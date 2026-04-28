# TitanChatbot — AI-Native Acceptance Checklist

Use this checklist to verify that the TitanChatbot module meets all AI-native readiness requirements before a release or deployment.

---

## Knowledge Pack

- [ ] `Knowledge/` directory exists at module root
- [ ] `Knowledge/README.md` exists and is non-empty (>100 bytes)
- [ ] `Knowledge/guides/` directory exists with at least one `.md` file
- [ ] `Knowledge/faqs/` directory exists with at least one `.md` file
- [ ] `Knowledge/pricing/` directory exists with at least one `.md` file
- [ ] `Knowledge/policies/` directory exists with at least one `.md` file
- [ ] `Knowledge/sops/` directory exists with at least one `.md` file
- [ ] `Knowledge/checklists/` directory exists with at least one `.md` file
- [ ] `Knowledge/compliance/` directory exists with at least one `.md` file
- [ ] `Knowledge/examples/` directory exists with at least one `.md` file
- [ ] All knowledge files are substantial (>200 bytes each)

---

## BookingAgent

- [ ] `Agents/BookingAgent/` directory exists
- [ ] `Agents/BookingAgent/agent.manifest.json` exists and is valid JSON
- [ ] `agent.manifest.json` contains `agent_id`, `tools`, and `prompts` keys
- [ ] `Agents/BookingAgent/prompts/system.md` exists and is non-trivial (>50 bytes)
- [ ] `Agents/BookingAgent/prompts/answering.md` exists and is non-trivial (>50 bytes)
- [ ] `Agents/BookingAgent/prompts/tool-use.md` exists and is non-trivial (>50 bytes)
- [ ] All tool `.tool.json` files exist and parse as valid JSON
- [ ] Each tool schema contains `name`, `description`, and `parameters` fields
- [ ] `parameters.type` is `"object"` in every tool schema
- [ ] `Agents/BookingAgent/evaluations/eval-suite.json` exists and is valid JSON
- [ ] `eval-suite.json` contains at least one entry in `cases`
- [ ] Each eval case has `id`, `category`, `input`, and `pass_criteria`
- [ ] `Agents/BookingAgent/training/dataset-manifest.json` exists and is valid JSON
- [ ] All tool schema files referenced in `agent.manifest.json` exist on disk

### Required Tool Schemas

- [ ] `calculate-quote.tool.json`
- [ ] `check-service-area.tool.json`
- [ ] `lookup-available-slots.tool.json`
- [ ] `create-booking-request.tool.json`
- [ ] `lookup-customer.tool.json`
- [ ] `create-escalation.tool.json`
- [ ] `search-knowledge.tool.json`

---

## AI Config Manifests

- [ ] `AI/Indexing/indexing.manifest.json` exists and is valid JSON
- [ ] `indexing.manifest.json` contains `indexing.index_targets` array
- [ ] `AI/Citations/citation.schema.json` exists and is valid JSON
- [ ] `citation.schema.json` contains `citation` key
- [ ] `AI/Retrieval/retrieval.policy.json` exists and is valid JSON
- [ ] `retrieval.policy.json` contains `retrieval` key
- [ ] `AI/Guardrails/guardrails.json` exists and is valid JSON
- [ ] `guardrails.json` contains `input_guardrails`, `output_guardrails`, and `escalation_triggers`
- [ ] `AI/Actions/action-map.json` exists and is valid JSON
- [ ] `action-map.json` contains non-empty `intents` array
- [ ] `AI/Telemetry/telemetry.manifest.json` exists and is valid JSON
- [ ] `telemetry.manifest.json` contains non-empty `metrics` array
- [ ] `AI/README.md` exists and is substantial (>200 bytes)

---

## Documentation

- [ ] `Docs/` directory exists
- [ ] `Docs/BLUEPRINT_NOTES.md` exists and is non-empty (>100 bytes)
- [ ] `Docs/AI_NATIVE_MODULE_GUIDE.md` exists and is non-empty (>100 bytes)
- [ ] `ACCEPTANCE.md` exists (this file)
- [ ] `TREE.md` reflects current module structure

---

## Audit Command

- [ ] `php artisan titan-chatbot:audit` exits with code `0`
- [ ] All AI-native checks pass (Knowledge Pack, BookingAgent Manifest, Tool Schemas, Retrieval, Indexing, Guardrails, Telemetry, Action Map, Dataset Manifest, Eval Suite)

---

## Tests

- [ ] All PHPUnit tests pass: `phpunit --configuration phpunit.xml`
- [ ] `AiNativeStructureTest` passes specifically
- [ ] No PHP syntax errors: `php -l` passes on all module PHP files
- [ ] Test count is ≥ 121 (original) + new AI-native tests

---

## PHP Code Quality

- [ ] All PHP files pass `php -l` syntax check
- [ ] No undefined class references in new PHP files
- [ ] Namespace consistency: `Modules\TitanChatbot\*` throughout
