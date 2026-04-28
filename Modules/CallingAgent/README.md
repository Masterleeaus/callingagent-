# CallingAgent Module

A production-ready, module-only AI receptionist and voice calling system built on Twilio, ElevenLabs, and an LarAgent-inspired AI core. Designed to drop into any Laravel 10+ application without assuming a specific host app structure.

---

## Capabilities

- Twilio inbound/outbound voice with TwiML `Gather`, `Say`, `Dial`, `Record`, status callbacks
- Twilio SMS and WhatsApp messaging
- Realtime Media Streams bridge (Twilio ↔ ElevenLabs)
- AI receptionist with rule-based + LLM response modes
- **LarAgent-inspired AI core** — tool system, structured outputs, message layer, context/history, provider drivers, usage tracking, AI lifecycle events
- Live agent transfer and call hangup (Twilio REST API)
- Idempotent billing via `VoiceSecondsMeter` (one record per call)
- Call logs, active calls, transcripts, recordings, caller profiles, outcome intelligence
- Builder UI for configuring agents (no framework build step required)
- Filament v3 admin panel (calls, profiles, outcomes, usage)
- Webhook signature validation via `ValidateTwilioSignature` middleware
- Module validator and manifest validator scripts

---

## Module-Only Installation

### 1. Copy the module

```bash
cp -r Modules/CallingAgent your-laravel-app/Modules/CallingAgent
```

### 2. Register the service provider

If you use `nWidart/laravel-modules`, add to your `modules_statuses.json`:
```json
{ "CallingAgent": true }
```

Otherwise, register manually in `bootstrap/providers.php` (Laravel 11) or `config/app.php` (Laravel 10):
```php
\Modules\CallingAgent\Providers\ModuleServiceProvider::class,
```

### 3. Run migrations

```bash
php artisan migrate
```

### 4. Publish assets (optional — for Builder UI)

```bash
php artisan vendor:publish --tag=calling-agent-assets
```

### 5. Register Filament plugin (optional)

In your Filament panel provider:
```php
$panel->plugins([
    \Modules\CallingAgent\Filament\Plugin\CallingAgentPlugin::make(),
]);
```

---

## Environment Variables

See `.env.example` for the full set. Minimum required:

```env
# Twilio (optional — safe mock mode when absent)
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_FROM_NUMBER=+15005550001

# ElevenLabs (optional)
ELEVENLABS_API_KEY=sk_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

# OpenAI (optional — for LLM responses)
OPENAI_API_KEY=sk-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

# Module
CALLING_AGENT_REALTIME_ENABLED=false
CALLING_AGENT_SKIP_TWILIO_VALIDATION=false
```

---

## MVP Reception Flow

1. Caller rings a Twilio number.
2. `TwilioVoiceWebhookController@incoming` resolves the calling agent by phone number.
3. The module creates/updates an active call record and returns TwiML.
4. Twilio gathers speech (`<Gather>`) and posts it to `@gather`.
5. `ReceptionistAgent` uses rule-based fallback (or `OpenAICompatibleDriver` when configured).
6. TwiML says the answer, offers transfer/voicemail/repeat, and logs the turn.
7. `@status` finalizes duration, billing (`CallUsageRecorder`), and outcome.

---

## Twilio Webhook Setup

| Webhook | URL |
|---------|-----|
| Inbound Voice | `https://your-domain.com/calling-agent/webhooks/twilio/voice/incoming` |
| Gather/Response | `https://your-domain.com/calling-agent/webhooks/twilio/voice/gather` |
| Status Callback | `https://your-domain.com/calling-agent/webhooks/twilio/voice/status` |
| Recording Callback | `https://your-domain.com/calling-agent/webhooks/twilio/voice/recording` |
| Voicemail | `https://your-domain.com/calling-agent/webhooks/twilio/voice/voicemail` |
| SMS/WhatsApp | `https://your-domain.com/calling-agent/webhooks/twilio/message/incoming` |
| Media Streams | `https://your-domain.com/calling-agent/realtime/twilio` |

All webhook routes are protected with Twilio signature validation. For local dev, set `CALLING_AGENT_SKIP_TWILIO_VALIDATION=true` and use [ngrok](https://ngrok.com).

---

## ElevenLabs Setup

1. Create an agent at https://elevenlabs.io/app/conversational-ai
2. Copy the Agent ID into your agent record: `calling_agents.settings.elevenlabs_agent_id`
3. Set `ELEVENLABS_API_KEY` in `.env`
4. Enable realtime: `CALLING_AGENT_REALTIME_ENABLED=true`

---

## Realtime Bridge Setup

Full-duplex audio via Twilio Media Streams + ElevenLabs:

1. Install and configure the Twilio SDK: `composer require twilio/sdk`
2. Set `CALLING_AGENT_REALTIME_ENABLED=true`
3. Configure your Twilio phone number's Media Streams URL:
   `https://your-domain.com/calling-agent/realtime/twilio`
4. The bridge auto-falls back to Gather/Say if ElevenLabs is unavailable.

Session tokens for WebSocket authentication:
- Issue: `POST /calling-agent/realtime/token`
- Validate: `POST /calling-agent/realtime/validate-token`

---

## Builder UI

The Builder UI requires no npm build step. Assets are plain CSS/JS under `Resources/assets/`.

Access at: `https://your-domain.com/calling-agent/builder`

Features:
- Per-agent settings (persona, voice, greeting, capabilities)
- Phone number assignment
- Webhook URL display
- ☎ Test call button
- Save/load with visible error feedback
- CSRF-protected via `<meta name="csrf-token">`

---

## LarAgent-Inspired AI Core

The module includes an AI core adapted from [LarAgent](https://github.com/MaestroError/LarAgent) design patterns. The `laragent/laragent` Composer package is **not required**.

### Using the AI Core

```php
use Modules\CallingAgent\AI\Core\AgentConfig;
use Modules\CallingAgent\AI\Core\BaseAgent;
use Modules\CallingAgent\AI\Drivers\OpenAICompatibleDriver;

// Build a config
$config = AgentConfig::fromArray([
    'driver'      => 'openai',
    'model'       => 'gpt-4o',
    'maxTokens'   => 512,
    'temperature' => 0.6,
]);

// Configure a driver
$driver = new OpenAICompatibleDriver(apiKey: env('OPENAI_API_KEY'));

// Use in an agent
class MyReceptionist extends BaseAgent {
    protected function systemPrompt(): string {
        return 'You are a helpful dental clinic receptionist.';
    }
}

$agent = (new MyReceptionist())->withConfig($config)->withEngine($driver);
$reply = $agent->chat('I need to book an appointment');
```

### Declare tools with the `#[Tool]` attribute

```php
use Modules\CallingAgent\AI\Tools\Attributes\Tool;
use Modules\CallingAgent\AI\Tools\ToolRegistry;
use Modules\CallingAgent\AI\Tools\ToolDefinition;

class BookingTools {
    #[Tool(name: 'check_availability', description: 'Check available appointment slots')]
    public function checkAvailability(string $date): array {
        return ['slots' => ['09:00', '11:00', '14:00']];
    }
}

$registry = new ToolRegistry();
$registry->registerFromObject(new BookingTools());
```

### Structured AI outputs with schema validation

```php
use Modules\CallingAgent\AI\StructuredOutput\CallOutcomeModel;

$outcome = CallOutcomeModel::fromArray([
    'intent'           => 'booking',
    'urgency'          => 'high',
    'handoff_required' => false,
    'summary'          => 'Caller wants to book next Tuesday',
]);

$valid = CallOutcomeModel::validate($outcome->toArray()); // true/false
$vo    = $outcome->toStructuredCallOutcome(); // → StructuredCallOutcome
```

---

## Queue Worker

Dispatches jobs to the `calling-agent` queue for summarisation and missed-call recovery.

**Supervisor:**
```ini
[program:calling-agent-worker]
command=php /var/www/artisan queue:work redis --queue=calling-agent --sleep=3 --tries=3
autostart=true
autorestart=true
numprocs=2
user=www-data
```

**Systemd:**
```ini
[Unit]
Description=CallingAgent Queue Worker
After=network.target

[Service]
User=www-data
WorkingDirectory=/var/www
ExecStart=/usr/bin/php artisan queue:work redis --queue=calling-agent --sleep=3
Restart=on-failure
```

---

## Module Validation

Run the static validators to check file integrity:

```bash
php Modules/CallingAgent/Support/Validation/ModuleValidator.php
php Modules/CallingAgent/Support/Validation/ManifestValidator.php
```

---

## Compatibility

| Dependency | Status | Notes |
|-----------|--------|-------|
| Laravel 10+ | Required | Uses PHP 8.1+ readonly, named args, match |
| Filament v3 | Optional | Plugin for admin panel; graceful skip if absent |
| `twilio/sdk` | Optional | Safe mock mode when absent |
| ElevenLabs | Optional | Fallback to Gather/Say TwiML |
| OpenAI / Groq / OpenRouter | Optional | LarAgent-inspired driver; NullDriver fallback |
| `nWidart/laravel-modules` | Optional | Module auto-discovery via module.json |

---

## Tests

```bash
# From the host app root
php artisan test --filter=ProductionReadinessTest
php artisan test --filter=FourthPassTest
```

---

## Readiness Report

See `Support/MODULE_READINESS_REPORT.md` for the full fifth-pass status report.

## Source Map

See `Support/SOURCE_MAP.md` for the full list of merged sources and LarAgent extraction notes.
