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
