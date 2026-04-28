# AI-Native Module Blueprint — Design Notes

This document captures the design decisions and rationale behind the **AI-native module pattern** as implemented in TitanChatbot.

---

## What Makes a Module "AI-Native"?

A standard Laravel module manages business logic, HTTP controllers, database models, and jobs. An **AI-native module** does all of that *plus*:

1. **Ships its own knowledge pack** — structured Markdown files that the RAG pipeline indexes at deployment time, giving the AI domain-specific ground truth.
2. **Declares agent manifests** — JSON files describing agent identity, capabilities, tools, prompts, and memory configuration.
3. **Defines tool schemas explicitly** — each callable tool has a `.tool.json` file describing its input schema, enabling validation, documentation, and fine-tuning data generation.
4. **Includes prompt files** — versioned Markdown prompts for system, answering, and tool-use contexts, separate from PHP code.
5. **Carries AI configuration manifests** — retrieval policy, guardrails, indexing strategy, telemetry, and action maps live *inside* the module, not in a central config file.
6. **Provides evaluations** — an `eval-suite.json` with test cases that can be run offline to validate agent behaviour before deployment.
7. **Includes training data manifests** — references to datasets used to fine-tune or validate model behaviour.

The result: the module is **self-contained and portable**. You can drop it into any Laravel application and the AI runtime has everything it needs.

---

## TitanChatbot as Reference Implementation

TitanChatbot is the canonical reference for this pattern. Its structure covers every element:

```
TitanChatbot/
├── AI/                          ← AI runtime (PHP + config manifests)
│   ├── Actions/action-map.json
│   ├── Citations/citation.schema.json
│   ├── Guardrails/guardrails.json
│   ├── Indexing/indexing.manifest.json
│   ├── Retrieval/retrieval.policy.json
│   ├── Telemetry/telemetry.manifest.json
│   └── README.md
├── Agents/
│   └── BookingAgent/            ← Fully-specified agent
│       ├── agent.manifest.json
│       ├── evaluations/eval-suite.json
│       ├── prompts/{system,answering,tool-use}.md
│       ├── tools/*.tool.json
│       └── training/dataset-manifest.json
└── Knowledge/                   ← Domain knowledge pack
    ├── README.md
    ├── guides/
    ├── faqs/
    ├── pricing/
    ├── policies/
    ├── sops/
    ├── checklists/
    ├── compliance/
    ├── contracts/
    └── examples/
```

---

## Directory Structure Decisions

### Why `Knowledge/` at module root?

The knowledge pack is a first-class concern of the module, not an implementation detail. Placing it at the root makes it visible to non-engineers (content teams, operators) and keeps it separate from PHP source files.

### Why `Agents/` separate from `AI/Agents/`?

`AI/Agents/` contains **PHP runtime classes**. `Agents/` contains **agent manifests** — JSON + Markdown configuration files. The split mirrors the distinction between code and configuration.

### Why `.tool.json` files?

Tool schemas serve three purposes:
1. **Documentation** — clear, human-readable spec for each tool
2. **Validation** — incoming LLM tool calls can be validated against the schema before dispatch
3. **Fine-tuning** — tool schemas are required input for generating fine-tuning datasets

### Why prompt `.md` files instead of PHP strings?

Prompts change frequently and are reviewed by non-engineers. Markdown files can be edited without touching PHP, reviewed in PRs with clean diffs, and versioned independently.

---

## Knowledge Pack Structure

Each subdirectory targets a specific query category:

| Directory | Content | RAG Priority |
|-----------|---------|-------------|
| `guides/` | How-to estimating and service guides | High |
| `faqs/` | Common customer questions and answers | High |
| `pricing/` | Rate cards, surcharges, discount rules | High |
| `policies/` | Service terms, cancellation, refund policies | Medium |
| `sops/` | Operator standard operating procedures | Medium |
| `checklists/` | Quality inspection and task checklists | Medium |
| `compliance/` | Legal compliance notes | Low |
| `contracts/` | Sample service agreements | Reference |
| `examples/` | Sample conversations for few-shot context | Reference |

---

## Agent Manifest Structure

`agent.manifest.json` is the single source of truth for an agent's capabilities:

```json
{
  "agent_id": "booking-agent",
  "version": "1.0",
  "tools": [{ "name": "...", "schema": "Agents/BookingAgent/tools/..." }],
  "prompts": { "system": "...", "answering": "...", "tool_use": "..." },
  "memory": { "driver": "cache", "max_messages": 20 },
  "provider": { "primary": "openai", "model": "gpt-4o" }
}
```

The manifest is read by the agent factory at runtime to configure the agent instance.

---

## Tool Schema Definition

Each `.tool.json` follows the OpenAI function-calling schema:

```json
{
  "name": "calculate_quote",
  "description": "Calculate a cleaning service quote",
  "parameters": {
    "type": "object",
    "properties": {
      "bedrooms": { "type": "integer", "description": "Number of bedrooms" }
    },
    "required": ["bedrooms"]
  }
}
```

These files are the authoritative source. `SchemaGenerator.php` also auto-generates schemas from PHP `#[Tool]` attributes at runtime — both approaches are supported.

---

## Evaluations

`eval-suite.json` contains offline test cases:

```json
{
  "suite": "booking-agent-evals",
  "cases": [
    {
      "id": "quote-1",
      "category": "quoting",
      "input": "How much for a 3-bedroom house clean?",
      "pass_criteria": { "tool_called": "calculate_quote" }
    }
  ]
}
```

Run evals via the audit command or a dedicated eval runner to verify agent behaviour without live API calls.

---

## Migration Path from Non-AI-Native Module

To upgrade an existing module to AI-native:

1. **Create `Knowledge/`** — gather domain content into structured Markdown files
2. **Create `Agents/<AgentName>/`** — write `agent.manifest.json`, prompts, and tool schemas
3. **Create `AI/` config manifests** — add indexing, retrieval, guardrails, actions, telemetry
4. **Write eval cases** — document expected agent behaviour in `evaluations/eval-suite.json`
5. **Update the audit command** — add AI-native checks
6. **Add `AiNativeStructureTest`** — PHPUnit tests that verify all files exist and are valid JSON
7. **Update `ACCEPTANCE.md`** — checklist for AI-native readiness

The migration can be done incrementally — start with the knowledge pack and a single agent, then add the configuration manifests.
