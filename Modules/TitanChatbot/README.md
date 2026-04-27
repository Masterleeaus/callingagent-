# TitanChatbot Module

Unified Laravel module for a multi-channel AI chatbot platform. Supports website chat, WhatsApp, Telegram, Messenger, voice calls, RAG training, live-agent escalation, billing meters, tenancy and a PWA-ready widget.

---

## Installation

1. **Register the provider** in `config/app.php` (or your module loader):
   ```php
   Modules\TitanChatbot\Providers\ModuleServiceProvider::class,
   ```

2. **Run migrations:**
   ```bash
   php artisan migrate
   ```

3. **Publish config** (optional):
   ```bash
   php artisan vendor:publish --tag=titan-chatbot-config
   ```

---

## Environment Variables

```dotenv
# AI provider
OPENAI_API_KEY=sk-...
TITAN_CHATBOT_AI_PROVIDER=openai        # openai | custom
TITAN_CHATBOT_MODEL=gpt-4o-mini
TITAN_CHATBOT_EMBEDDINGS_MODEL=text-embedding-3-small
TITAN_CHATBOT_MAX_TOKENS=1024
TITAN_CHATBOT_TEMPERATURE=0.7
TITAN_CHATBOT_RAG_CHUNKS=5
TITAN_CHATBOT_MEMORY_LIMIT=20
TITAN_CHATBOT_FALLBACK="I'm sorry, I can't answer that right now."

# Facebook Messenger webhook verification
MESSENGER_VERIFY_TOKEN=your_secret_token
```

---

## Route Reference

### API routes (`api/chatbots`)
| Method | URI | Name | Description |
|--------|-----|------|-------------|
| POST | `/api/chatbots/{id}/message` | `api.chatbots.message` | Send a text message |
| POST | `/api/chatbots/{id}/voice` | `api.chatbots.voice` | Send a voice message |
| GET  | `/api/chatbots/{id}/history` | `api.chatbots.history` | Fetch conversation history |
| POST | `/api/chatbots/{id}/train` | `api.chatbots.train` | Ingest training content |
| GET  | `/api/titan-chatbot/health` | `api.titan-chatbot.health` | Health check |

### Webhook routes (`webhooks/titan-chatbot`)
| Method | URI | Name | Description |
|--------|-----|------|-------------|
| POST | `/webhooks/titan-chatbot/whatsapp/{channelId}` | `titan-chatbot.webhooks.whatsapp` | Twilio WhatsApp inbound |
| POST | `/webhooks/titan-chatbot/telegram/{channelId}` | `titan-chatbot.webhooks.telegram` | Telegram bot update |
| GET  | `/webhooks/titan-chatbot/telegram/{channelId}/verify` | `titan-chatbot.webhooks.telegram.verify` | Telegram verify endpoint |
| GET  | `/webhooks/titan-chatbot/messenger/{channelId}` | `titan-chatbot.webhooks.messenger.verify` | Facebook hub challenge |
| POST | `/webhooks/titan-chatbot/messenger/{channelId}` | `titan-chatbot.webhooks.messenger` | Messenger message event |

> Webhook routes carry **no auth middleware** — they must be publicly accessible for external services to reach them.

---

## Channel Setup

### WhatsApp (Twilio)
1. Configure a Twilio WhatsApp sender and point the webhook URL to:
   `POST /webhooks/titan-chatbot/whatsapp/{channelId}`
2. Twilio posts `WaId` (sender number) and `Body` (message text). The controller normalises these into a `MessagePayload`.

### Telegram
1. Create a bot via [@BotFather](https://t.me/BotFather) and obtain a token.
2. Register the webhook with Telegram:
   ```
   https://api.telegram.org/bot<TOKEN>/setWebhook?url=https://yourdomain.com/webhooks/titan-chatbot/telegram/{channelId}
   ```
3. Telegram posts `message.text` and `message.from.id`.

### Facebook Messenger
1. Create a Meta App and add the Messenger product.
2. Set `MESSENGER_VERIFY_TOKEN` in your `.env`.
3. Register the callback URL in Meta's dashboard:
   `GET /webhooks/titan-chatbot/messenger/{channelId}` (verification)
   `POST /webhooks/titan-chatbot/messenger/{channelId}` (events)

---

## Voice Setup

Voice calls are handled by `VoiceAgent` and `VoiceConversationPipeline`. Integration with ElevenLabs or Twilio Voice is configured in `Config/legacy/chatbot/chatbot-voice.php`. Set up a Twilio Voice number to POST to your ConversationController's `/voice` endpoint.

---

## Training / RAG Flow

Use `TrainingPipeline` to ingest content into `ext_chatbot_embeddings`:

```php
app(\Modules\TitanChatbot\Services\TrainingPipeline::class)
    ->ingest($chatbotId, 'qa', $content, ['title' => 'FAQ', 'engine' => 'default']);
```

Supported `sourceType` values: `text`, `qa`, `pdf`, `url`.

`RagPipeline` retrieves the most relevant chunks (default: 5) and injects them into the AI prompt at inference time.

---

## Builder UI

The chatbot builder is served via `titan-chatbot` web routes. Views live in `Resources/views/` and include tabs for:

- **Configure** — name, persona, fallback message
- **Customize** — widget colours, avatar, position
- **Train** — upload PDF, paste text, enter Q&A pairs
- **Embed** — JS snippet and iframe code
- **Channels** — enable WhatsApp / Telegram / Messenger
- **Analytics** — conversation and session stats

---

## Filament Admin Integration

`Filament/` contains optional Filament resource pages and widgets. These are loaded automatically when Filament is present. They provide admin management for Chatbots, Conversations and Embeddings.

---

## Testing

No Laravel app bootstrap is required for unit and integration tests — they use plain PHPUnit with Mockery.

```bash
# From the repository root (requires a phpunit.xml that autoloads Modules/)
./vendor/bin/phpunit Modules/TitanChatbot/Tests/

# Run a specific suite
./vendor/bin/phpunit Modules/TitanChatbot/Tests/Unit/
./vendor/bin/phpunit Modules/TitanChatbot/Tests/Integration/
./vendor/bin/phpunit Modules/TitanChatbot/Tests/Feature/
```

Test files:
| File | Covers |
|------|--------|
| `Tests/Unit/BillingMeterTest.php` | `VoiceSecondsMeter`, `ConversationMeter` key formats and `record()` logic |
| `Tests/Unit/TrainingPipelineTest.php` | `chunkText()` and `chunkQa()` parsing |
| `Tests/Unit/MessagePayloadTest.php` | `MessagePayload` construction, `fromArray()`, `toArray()` |
| `Tests/Unit/ChannelRouterTest.php` | `ChannelRouter` map coverage, driver interface compliance, `register()` |
| `Tests/Unit/TitanChatbotStructureTest.php` | All required classes exist and implement correct interfaces |
| `Tests/Feature/TitanChatbotBuilderTest.php` | End-to-end payload round-trip |
| `Tests/Integration/TitanChatbotChannelTest.php` | `ChannelRouter` map covers all `ChannelType` enum values |

---

## Optional Dependencies

| Dependency | Purpose |
|------------|---------|
| `filament/filament` | Admin panel resource pages |
| `pgvector` PostgreSQL extension | Native vector similarity search for RAG |
| Legacy `GeneratorService` | Retained in `Support/Legacy/` for incremental migration |
| `twilio/sdk` | Twilio WhatsApp / Voice integration |
| ElevenLabs API | Voice synthesis in `Integrations/ElevenLabsVoiceChat/` |


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
