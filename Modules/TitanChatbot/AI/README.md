# TitanChatbot — AI/Core Directory

This directory contains the runtime AI infrastructure for TitanChatbot: agents, tools, memory, pipelines, and all supporting AI-native configuration files.

---

## Directory Layout

```
AI/
├── Actions/              # Intent-to-action mapping (action-map.json)
├── Agents/               # Concrete agent implementations (PHP classes)
├── Attributes/           # PHP attributes: #[Tool], #[Desc]
├── Citations/            # RAG citation schema (citation.schema.json)
├── Core/                 # TitanAgent base class
├── DataModels/           # Structured output models (BookingIntentModel, etc.)
├── Guardrails/           # Input/output content guardrails (guardrails.json)
├── Indexing/             # Knowledge indexing manifest (indexing.manifest.json)
├── Memory/               # ConversationMemory, StorageManager, drivers, truncation
├── Pipelines/            # RagPipeline, RagAnswerPipeline, VoiceConversationPipeline
├── Prompts/              # PHP prompt templates
├── Providers/            # AI provider adapters (FakeGeneratorProvider, etc.)
├── Retrieval/            # RAG retrieval policy (retrieval.policy.json)
├── Telemetry/            # Observability manifest (telemetry.manifest.json)
└── Tools/                # ToolRegistry, SchemaGenerator, tool implementations
```

---

## Core Components

### `TitanAgent` (`Core/TitanAgent.php`)

The abstract base class for all agents. Subclass it and annotate PHP methods with `#[Tool]` and `#[Desc]` to expose them as callable tools.

**Responsibilities:**
- Loads conversation memory via `StorageManager`
- Builds the message array (system prompt + history + user message)
- Calls the configured LLM provider
- Dispatches tool calls discovered in the response
- Returns the final assistant reply

### `ToolRegistry` (`Tools/ToolRegistry.php`)

Discovers and registers all `#[Tool]`-annotated methods on an agent instance at runtime. Produces the `tools` array passed to the LLM.

### `SchemaGenerator` (`Tools/SchemaGenerator.php`)

Uses reflection and `#[Desc]` attributes to auto-generate OpenAI-compatible JSON Schema for each tool parameter. No manual schema writing required.

### `StorageManager` (`Memory/StorageManager.php`)

Abstracts conversation persistence. Selects the appropriate memory driver based on configuration and manages read/write of conversation history.

### Data Models (`DataModels/`)

Typed output models that represent structured data extracted from LLM responses: `BookingIntentModel`, `QuoteRequestModel`, `EscalationDecisionModel`, etc. All extend `BaseDataModel` and implement `DataModelInterface`.

---

## Agent Pipeline

```
User Message
    │
    ▼
[ConversationMemory.load]   ← hydrates history from driver
    │
    ▼
[TitanAgent.buildMessages]  ← system prompt + history + user turn
    │
    ▼
[LLM Provider.generate]     ← sends to OpenAI / Anthropic / etc.
    │
    ├── tool_call detected?
    │       │
    │       ▼
    │   [ToolRegistry.dispatch]  ← calls matching #[Tool] method
    │       │
    │       ▼
    │   [LLM Provider.generate]  ← sends tool result, gets final reply
    │
    ▼
[ConversationMemory.append]  ← saves assistant reply
    │
    ▼
Response returned to caller
```

---

## Creating a New Agent

1. Create a PHP class in `AI/Agents/` extending `TitanAgent`:

```php
namespace Modules\TitanChatbot\AI\Agents;

use Modules\TitanChatbot\AI\Core\TitanAgent;
use Modules\TitanChatbot\AI\Attributes\Tool;
use Modules\TitanChatbot\AI\Attributes\Desc;

class MyAgent extends TitanAgent
{
    protected string $systemPrompt = 'You are a helpful assistant for...';

    #[Tool]
    public function lookupOrder(
        #[Desc('The order ID to look up')] string $orderId
    ): array {
        // fetch from database or API
        return ['order_id' => $orderId, 'status' => 'pending'];
    }
}
```

2. Register the agent in your service provider or factory.
3. Call `$agent->chat($sessionId, $userMessage)` to run a conversation turn.

---

## Using Tool Attributes

| Attribute | Target | Purpose |
|-----------|--------|---------|
| `#[Tool]` | Method | Marks method as an LLM-callable tool |
| `#[Desc('...')` | Parameter | Adds description to the JSON schema for that parameter |

`SchemaGenerator` reflects over every `#[Tool]` method and builds the OpenAI `tools` array automatically. Parameter types (`string`, `int`, `bool`, `array`) are mapped to JSON Schema types.

---

## Memory Driver Selection

Configure the driver in `config/titan-chatbot.php`:

```php
'memory' => [
    'driver' => env('TITAN_MEMORY_DRIVER', 'in_memory'),
    // 'driver' => 'cache'      // uses Laravel Cache
    // 'driver' => 'file'       // persists to storage/app/titan-memory/
    // 'driver' => 'database'   // persists to DB table
],
```

| Driver | Use Case |
|--------|----------|
| `in_memory` | Testing, ephemeral single-request conversations |
| `cache` | Short-lived sessions backed by Redis/Memcached |
| `file` | Development / small deployments without a DB |
| `database` | Production multi-turn conversations |

---

## Truncation Strategies

When conversation history exceeds the token budget, a truncation strategy is applied:

- **`SimpleTruncationStrategy`** — drops oldest messages until within budget
- **`SummarizationStrategy`** — summarizes older messages using the LLM before dropping

Configure in `config/titan-chatbot.php`:

```php
'memory' => [
    'truncation' => 'simple',   // or 'summarization'
    'max_messages' => 20,
],
```

---

## Provider Fallback Configuration

```php
'ai' => [
    'provider'  => env('TITAN_AI_PROVIDER', 'openai'),
    'fallback'  => env('TITAN_AI_FALLBACK', 'anthropic'),
    'model'     => env('TITAN_AI_MODEL', 'gpt-4o'),
    'api_key'   => env('OPENAI_API_KEY'),
],
```

The `GeneratorBridge` service handles provider selection and falls back to the configured secondary provider on failure.

---

## Usage Tracking

Token usage is recorded via `ChatbotUsageRecorder` and metered through:

- `ConversationMeter` — counts billable conversations
- `EmbeddingMeter` — counts embedding API calls
- `VoiceSecondsMeter` — counts voice channel seconds

Access usage via `UsageTracker::forTenant($tenantId)`.

---

## AI-Native Support Files

These files live alongside the PHP code and are used by the AI runtime, indexing pipeline, and monitoring systems:

| File | Purpose |
|------|---------|
| `AI/Indexing/indexing.manifest.json` | Controls how Knowledge/ files are chunked and embedded |
| `AI/Citations/citation.schema.json` | Schema for RAG citations attached to responses |
| `AI/Retrieval/retrieval.policy.json` | Similarity thresholds, reranking, category boosts |
| `AI/Guardrails/guardrails.json` | Input/output content rules and escalation triggers |
| `AI/Actions/action-map.json` | Maps detected intents to tool calls |
| `AI/Telemetry/telemetry.manifest.json` | Metrics, traces, and log configuration |

Alongside:

| Directory | Purpose |
|-----------|---------|
| `Agents/BookingAgent/` | BookingAgent manifest, prompts, tool schemas, evals, training |
| `Knowledge/` | Markdown knowledge pack (guides, FAQs, pricing, policies, SOPs, etc.) |

---

## Related Documentation

- [`Docs/AI_NATIVE_MODULE_GUIDE.md`](../Docs/AI_NATIVE_MODULE_GUIDE.md) — step-by-step guide for adding knowledge, agents, and tools
- [`Docs/BLUEPRINT_NOTES.md`](../Docs/BLUEPRINT_NOTES.md) — design decisions behind the AI-native module pattern
- [`ACCEPTANCE.md`](../ACCEPTANCE.md) — acceptance checklist for AI-native readiness
