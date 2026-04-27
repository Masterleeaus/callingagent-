# CallingAgent Module

Merged calling/reception module built from the uploaded chatbot, WhatsApp/Twilio, ElevenLabs voice, live-agent, and call-center sources.

## Capabilities

- Twilio WhatsApp messaging
- Twilio SMS messaging
- Twilio inbound voice webhooks
- Twilio outbound calls
- TwiML receptionist flows using `Gather`, `Say`, `Dial`, `Record`, and status callbacks
- AI response adapter for existing chatbot generator services
- ElevenLabs conversational agent metadata and knowledge sync hooks
- Live agent handoff and transfer rules
- Call logs, active calls, transcripts, recordings, and usage metering
- IVR / front-desk workflow definition skeleton
- Tenant, billing, permissions, automation, workflow, AI, API, UI, and upgrade manifests

## MVP Reception Flow

1. Caller rings a Twilio number.
2. `TwilioVoiceWebhookController@incoming` resolves the calling agent by phone number.
3. The module creates/updates an active call and returns TwiML.
4. Twilio gathers speech and posts it to `@gather`.
5. `ReceptionistAgent` asks the existing chatbot generator for an answer.
6. TwiML says the answer, offers transfer/voicemail/repeat, and logs the turn.
7. `@status` finalizes duration, cost, transcript, and billing meters.

## Required env/settings

```env
TWILIO_ACCOUNT_SID=
TWILIO_AUTH_TOKEN=
TWILIO_FROM_NUMBER=
ELEVENLABS_API_KEY=
CALLING_AGENT_DEFAULT_TRANSFER_NUMBER=
```

## Install notes

Copy `Modules/CallingAgent` into your application, register the provider if your module loader does not auto-discover `module.json`, then run migrations.


## Pass 11 extraction

Adds provider abstraction, realtime Twilio/ElevenLabs/OpenAI voice pipeline, receptionist booking workflow, call lifecycle orchestration, analytics scoring, channel federation, and realtime billing guards.

## Environment Variables

```env
# Twilio
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=your_auth_token_here
TWILIO_FROM_NUMBER=+15005550006
TWILIO_WHATSAPP_FROM=whatsapp:+14155238886

# ElevenLabs
ELEVENLABS_API_KEY=sk_...
ELEVENLABS_AGENT_ID=agent_...

# Module
CALLING_AGENT_ENABLED=true
CALLING_AGENT_DEFAULT_TRANSFER_NUMBER=+15005550001
CALLING_AGENT_SKIP_TWILIO_VALIDATION=false
```

## Webhook Setup (Twilio Console)

| Webhook | URL |
|---------|-----|
| Inbound Voice | `https://your-domain.com/calling-agent/webhooks/twilio/voice/incoming` |
| Gather/Response | `https://your-domain.com/calling-agent/webhooks/twilio/voice/gather` |
| Status Callback | `https://your-domain.com/calling-agent/webhooks/twilio/voice/status` |
| Recording Callback | `https://your-domain.com/calling-agent/webhooks/twilio/voice/recording` |
| Voicemail | `https://your-domain.com/calling-agent/webhooks/twilio/voice/voicemail` |
| SMS/WhatsApp Inbound | `https://your-domain.com/calling-agent/webhooks/twilio/message/incoming` |

All webhook routes are protected with Twilio signature validation via `ValidateTwilioSignature` middleware.

### Basic TwiML Gather/Say Mode

This mode works without a realtime bridge. Twilio collects speech, sends it to `/gather`, and the AI responds via TwiML `<Say>`.

1. Set `TWILIO_ACCOUNT_SID`, `TWILIO_AUTH_TOKEN`, `TWILIO_FROM_NUMBER` in `.env`
2. Point your Twilio number's Voice webhook to the Inbound Voice URL above
3. The AI will respond using the rule-based receptionist or the TitanZero chatbot if available

### Realtime Media Streams Mode

For low-latency full-duplex audio via ElevenLabs:

1. Set `ELEVENLABS_API_KEY` and `ELEVENLABS_AGENT_ID`
2. Deploy the WebSocket bridge (see `bridge/README.md`)
3. Enable `realtime_streaming` feature flag in config
4. Configure your Twilio number to use the Stream TwiML endpoint
