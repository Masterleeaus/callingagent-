# AI-Native Module Guide

This guide walks through the practical steps for working with TitanChatbot's AI-native features: knowledge, agents, tools, evaluations, and the audit command.

---

## Architecture Overview

TitanChatbot is built on three pillars:

1. **Knowledge Pack** (`Knowledge/`) — domain-specific Markdown files indexed into a vector store for RAG
2. **Agent Layer** (`Agents/`, `AI/Agents/`, `AI/Core/`) — PHP agents with declarative tool schemas and versioned prompts
3. **AI Config Manifests** (`AI/Indexing/`, `AI/Retrieval/`, `AI/Guardrails/`, `AI/Actions/`, `AI/Telemetry/`, `AI/Citations/`) — runtime configuration for the AI pipeline

These three pillars work together: a user message triggers the BookingAgent, which retrieves context from the knowledge pack via the RAG pipeline (governed by retrieval policy and guardrails), calls tools as needed, and returns a response — all tracked via telemetry.

---

## Step-by-Step: Adding a New Knowledge File

### 1. Choose the right category directory

| Category | Path | Use for |
|----------|------|---------|
| Guides | `Knowledge/guides/` | How-to content, estimating guides |
| FAQs | `Knowledge/faqs/` | Frequently asked questions |
| Pricing | `Knowledge/pricing/` | Rate cards, surcharges |
| Policies | `Knowledge/policies/` | Service terms, cancellation |
| SOPs | `Knowledge/sops/` | Operator procedures |
| Checklists | `Knowledge/checklists/` | Quality and task checklists |
| Compliance | `Knowledge/compliance/` | Legal and regulatory notes |
| Examples | `Knowledge/examples/` | Sample conversations |

### 2. Write the Markdown file

```markdown
# Move-Out Cleaning FAQ

## What is included in a move-out clean?
A move-out clean includes all rooms, appliances (oven, fridge), windows (interior),
cabinets (interior), and bathrooms to bond/deposit standard.

## How much does a move-out clean cost?
Pricing starts at $X for a studio and increases by $Y per bedroom.
See the rate card for full details.
```

Keep files focused on a single topic. Aim for 300–1500 words per file for optimal chunking.

### 3. Verify the indexing manifest covers your category

Open `AI/Indexing/indexing.manifest.json` and confirm your category appears in `index_targets`. If adding a new category, add an entry:

```json
{
  "path": "Knowledge/move-out/",
  "recursive": true,
  "file_types": ["md"],
  "category": "move-out",
  "priority": "high"
}
```

### 4. Re-run the indexing pipeline

```bash
php artisan titan-chatbot:reindex
```

---

## Step-by-Step: Creating a New Agent

### 1. Create the agent manifest

Create `Agents/MyAgent/agent.manifest.json`:

```json
{
  "agent_id": "my-agent",
  "version": "1.0",
  "description": "Handles X and Y",
  "tools": [
    { "name": "do_thing", "schema": "Agents/MyAgent/tools/do-thing.tool.json" }
  ],
  "prompts": {
    "system": "Agents/MyAgent/prompts/system.md",
    "answering": "Agents/MyAgent/prompts/answering.md",
    "tool_use": "Agents/MyAgent/prompts/tool-use.md"
  },
  "memory": { "driver": "cache", "max_messages": 20 },
  "provider": { "primary": "openai", "model": "gpt-4o", "temperature": 0.3 }
}
```

### 2. Create the system prompt

Create `Agents/MyAgent/prompts/system.md`:

```markdown
You are a specialist assistant for [purpose].

Your goals:
- Help customers with [task A]
- Use available tools to look up real data before answering
- Escalate to a human when [condition]

Always be concise, professional, and factual.
```

### 3. Create the PHP class

```php
namespace Modules\TitanChatbot\AI\Agents;

use Modules\TitanChatbot\AI\Core\TitanAgent;
use Modules\TitanChatbot\AI\Attributes\Tool;
use Modules\TitanChatbot\AI\Attributes\Desc;

class MyAgent extends TitanAgent
{
    protected string $agentId = 'my-agent';

    #[Tool]
    public function doThing(
        #[Desc('The ID of the thing to do')] string $thingId
    ): array {
        return ['result' => 'done', 'thing_id' => $thingId];
    }
}
```

### 4. Register the agent in the service provider

```php
$this->app->bind('titan.agent.my-agent', MyAgent::class);
```

---

## Step-by-Step: Adding a New Tool

### 1. Write the tool schema

Create `Agents/BookingAgent/tools/send-confirmation.tool.json`:

```json
{
  "name": "send_confirmation",
  "description": "Send a booking confirmation email to the customer",
  "parameters": {
    "type": "object",
    "properties": {
      "booking_id": {
        "type": "string",
        "description": "The booking ID to confirm"
      },
      "email": {
        "type": "string",
        "description": "Customer email address"
      }
    },
    "required": ["booking_id", "email"]
  }
}
```

### 2. Implement the tool method

Add a `#[Tool]` method to the agent class:

```php
#[Tool]
public function sendConfirmation(
    #[Desc('The booking ID to confirm')] string $bookingId,
    #[Desc('Customer email address')] string $email
): array {
    // send the email
    return ['sent' => true, 'booking_id' => $bookingId];
}
```

### 3. Register in the agent manifest

Add the tool to the `tools` array in `agent.manifest.json`.

### 4. Add the tool to the action map

In `AI/Actions/action-map.json`, add or update an intent entry to reference the new tool.

---

## Step-by-Step: Writing an Eval Case

Open `Agents/BookingAgent/evaluations/eval-suite.json` and add a case to the `cases` array:

```json
{
  "id": "confirmation-1",
  "category": "confirmations",
  "input": "Can you send me a confirmation for booking B-1234?",
  "context": { "booking_id": "B-1234", "email": "user@example.com" },
  "pass_criteria": {
    "tool_called": "send_confirmation",
    "tool_args_contain": { "booking_id": "B-1234" }
  },
  "fail_criteria": {
    "response_contains_any": ["I can't", "unable to send"]
  }
}
```

**Required fields for every eval case:**

| Field | Description |
|-------|-------------|
| `id` | Unique case identifier |
| `category` | Logical grouping |
| `input` | The user message |
| `pass_criteria` | What a passing response looks like |

---

## Step-by-Step: Running the Audit Command

```bash
php artisan titan-chatbot:audit
```

The audit command checks:
- Core PHP classes exist and are loadable
- AI provider config is set
- Knowledge pack exists
- BookingAgent manifest is valid JSON
- All prompt files exist and are non-empty
- All tool schemas are valid JSON
- Retrieval, Indexing, Guardrails, Telemetry, and Action Map files are valid JSON
- Dataset manifest and eval suite are valid JSON

Exit code `0` = all checks pass. Exit code `1` = one or more checks failed.

---

## Manifest Files Explained

| File | Purpose |
|------|---------|
| `AI/Indexing/indexing.manifest.json` | Tells the indexer which directories to crawl, chunk size, embedding model, metadata fields |
| `AI/Citations/citation.schema.json` | Defines the shape of citations attached to RAG responses; controls display rules |
| `AI/Retrieval/retrieval.policy.json` | Top-K, similarity thresholds, reranking config, category score boosts, fallback behaviour |
| `AI/Guardrails/guardrails.json` | Input sanitisation (PII redaction, prompt injection blocking), output rules (no price guarantees), escalation triggers |
| `AI/Actions/action-map.json` | Maps intent labels to tool calls with confidence thresholds |
| `AI/Telemetry/telemetry.manifest.json` | Prometheus-style metric definitions, trace span names, log masking rules |

---

## RAG Pipeline Explained

```
User query
    │
    ▼
[Embed query]          ← text-embedding-3-small
    │
    ▼
[Vector search]        ← top_k=5, min_similarity=0.60
    │
    ▼
[Reranking]            ← cross-encoder, top_k_after_rerank=3
    │
    ▼
[Build context]        ← chunks joined with separator, max 2048 tokens
    │
    ▼
[LLM generation]       ← system prompt + context + conversation history + query
    │
    ▼
[Attach citations]     ← citation.schema.json defines the citation shape
    │
    ▼
Response + citations
```

Category boosts in `retrieval.policy.json` give higher scores to FAQs and pricing results over compliance docs, because FAQ answers are usually more directly useful to customers.

---

## Provider Configuration

```php
// config/titan-chatbot.php
'ai' => [
    'provider' => env('TITAN_AI_PROVIDER', 'openai'),
    'fallback'  => env('TITAN_AI_FALLBACK', null),
    'model'     => env('TITAN_AI_MODEL', 'gpt-4o'),
    'api_key'   => env('OPENAI_API_KEY'),
],
```

The `GeneratorBridge` service wraps provider selection. If `fallback` is set and the primary provider throws, the bridge retries with the fallback provider automatically.

---

## Testing AI-Native Features

The `AiNativeStructureTest` PHPUnit suite verifies all AI-native files exist and are structurally valid without making any live API calls:

```bash
cd Modules/TitanChatbot
phpunit --configuration phpunit.xml --filter AiNativeStructureTest
```

For agent behaviour, run the eval suite:

```bash
php artisan titan-chatbot:eval --agent=booking-agent
```

For the full audit:

```bash
php artisan titan-chatbot:audit
```
