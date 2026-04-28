# Source Map

## Original Merge Sources (Passes 1–12)

| Source | Concepts Extracted |
|--------|--------------------|
| `ChatbotWhatsapp` | Twilio credentials, WhatsApp send, Twilio webhook, chatbot conversation flow |
| `ExternalChatbot` | Conversation/history/channel models, RAG generator, human-agent conditions, voice-call fields |
| `ChatbotVoice` | Embeddable voice chatbot UI, train/history/conversation models |
| `ElevenLabsVoiceChat` | ElevenLabs agent creation, voice ID, language, knowledge-base sync |
| `ChatbotAgent` | Ably/live-agent handoff event pattern |
| `ChatbotMessenger` / `ChatbotTelegram` | Channel-card and channel-service pattern |
| `call-center-AI` | Twilio Voice webhooks, active calls, call logs, billing and health-check ideas |
| `twilio-elevenlabs-integration` | Minimal Twilio incoming/status webhook and TwiML pattern |
| `leadpilot_ai` | Lead scoring, intent classification, structured call outcome extraction |
| `TitanBot_merged_best_base_pass12` | Provider abstraction, booking workflows, realtime pipeline base |

---

## Fifth Pass: LarAgent-Inspired AI Core

**Source reference:** LarAgent by MaestroError (https://github.com/MaestroError/LarAgent)
**License:** MIT (patterns adapted, not copied wholesale)
**Note:** The `laragent/laragent` Composer package is NOT a dependency.

### Adapted Concepts

| LarAgent Concept | CallingAgent Equivalent | Location |
|-----------------|-------------------------|----------|
| `LarAgent\Core\LarAgent` (abstract agent) | `Modules\CallingAgent\AI\Core\BaseAgent` | `AI/Core/BaseAgent.php` |
| Agent configuration array | `AgentConfig` readonly class | `AI/Core/AgentConfig.php` |
| Engine interface | `AgentEngine` interface | `AI/Core/AgentEngine.php` |
| `#[Tool]` attribute + schema gen | `#[Tool]` attribute + `ToolDefinition::fromMethod()` | `AI/Tools/Attributes/Tool.php`, `AI/Tools/ToolDefinition.php` |
| Tool registry + execution | `ToolRegistry` + `ToolExecutor` | `AI/Tools/ToolRegistry.php`, `AI/Tools/ToolExecutor.php` |
| Message types (system/user/assistant/tool) | `SystemMessage`, `UserMessage`, `AssistantMessage`, `ToolResultMessage` | `AI/Messages/` |
| `MessageInterface` collection | `MessageCollection` | `AI/Messages/MessageCollection.php` |
| Chat history abstraction | `ChatHistoryInterface` + `ArrayChatHistory` + `DatabaseChatHistory` | `AI/Context/` |
| Session identity | `SessionIdentity` | `AI/Context/SessionIdentity.php` |
| Truncation strategies | `SlidingWindowTruncation`, `SummarizationTruncation` | `AI/Context/` |
| `DataModel` structured outputs | `DataModel` abstract + `CallOutcomeModel` | `AI/StructuredOutput/` |
| OpenAI-compatible driver | `OpenAICompatibleDriver` | `AI/Drivers/OpenAICompatibleDriver.php` |
| Null/test driver | `NullDriver` | `AI/Drivers/NullDriver.php` |
| `DriverInterface` | `DriverInterface` | `AI/Drivers/DriverInterface.php` |
| Usage record DTO | `UsageRecord` | `AI/Usage/UsageRecord.php` |
| Usage storage | `UsageStorageInterface` + `DatabaseUsageStorage` | `AI/Usage/` |
| Lifecycle events | `BeforeAgentSend`, `AfterAgentSend`, `BeforeToolExecution`, `AfterToolExecution`, `EngineError`, `ConversationStarted`, `ConversationEnded` | `Events/AI/` |

### Intentional Deviations from LarAgent

1. **No Composer package dependency** — all patterns reimplemented natively in the module namespace.
2. **No Laravel service provider assumptions** — all Laravel facade usage guarded by `class_exists()`.
3. **Module-native namespaces** — `Modules\CallingAgent\AI\*` instead of `LarAgent\*`.
4. **Simpler driver interface** — `generate()` used instead of `send()` on `AgentEngine` to avoid return-type conflicts with `DriverInterface::send()`.
5. **Integrated with existing CallingAgent patterns** — `CallOutcomeModel` converts to/from existing `StructuredCallOutcome` VO; `SummarizationTruncation` uses existing `CallSummarizerTool`; `DatabaseChatHistory` uses `calling_agent_transcripts` table.
6. **OpenAI-compatible driver is generic** — works with OpenAI, Groq, OpenRouter, and any API that implements the OpenAI chat completions format.
