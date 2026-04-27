# TitanChatbot Module

Unified Laravel module scaffold for a multi-channel AI chatbot platform. It packages the uploaded chatbot codebases as preserved upstream sources and adds a clean module architecture for providers, manifests, routes, AI agents, workflows, automation, billing, tenancy, search, PWA support and upgrade hooks.

## Included capabilities
- External website chatbot builder
- RAG training: website, PDF, text, Q&A and spreadsheet-style ingestion
- WhatsApp, Telegram and Messenger channels
- Live agent escalation
- Voice chatbot and ElevenLabs voice layer
- PWA-ready client shell
- Billing limits, tenancy hooks, permissions and navigation manifests

## Source preservation
Original codebases are preserved in `Upgrade/Sources/`. Module-native adapters live in `Services`, `AI`, `Automation`, `Workflows`, `Billing`, `Search` and `Providers` so the existing extension code can be reused, migrated or refactored incrementally.


## Pass 03 merge notes

This pass uses `TitanChatbot_Module_Pass01.zip` as the base and merges the chatbot builder, runtime UI, channel extensions, voice integrations, agent handoff, and connector hub into one module.

### Builder UI preservation

The builder UI is intentionally retained under:

- `Resources/views/`
- `Resources/assets/`
- `Resources/views_combined_reference/`

No builder tabs were intentionally removed. The module keeps Configure, Customize, Train, Embed, Channel, frontend widget, frame, article/knowledge-base, conversation, analytics, and component views where present in the scanned sources.

### Merged code areas

- `Support/Legacy/Chatbot/System/`
- `Support/Legacy/Connectors/System/`
- `Channels/WhatsApp/`
- `Channels/Telegram/`
- `Channels/Messenger/`
- `Integrations/Agent/`
- `Integrations/ChatbotVoice/`
- `Integrations/ChatbotVoiceTitanGo/`
- `Integrations/ElevenLabsVoiceChat/`
- `Integrations/TitanGo/`

### Provenance

Source file hashes are recorded in `Upgrade/Sources/SOURCE_PROVENANCE.json`.
