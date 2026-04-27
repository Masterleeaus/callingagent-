# CallingAgent Next Extraction Report

This pass adds the remaining high-value core as first-class module code:

- Provider abstraction: telephony, STT, TTS, realtime voice, channel drivers.
- Unified Twilio provider and ElevenLabs/OpenAI realtime provider adapters.
- Twilio Media Streams relay, audio frame buffer, turn-taking state machine, barge-in state.
- Receptionist realtime pipeline with booking and human handoff intent handling.
- Call lifecycle manager, retry policy, missed-call/voicemail hooks.
- Booking core: availability resolver, timezone normalizer, booking intent resolver.
- Analytics core: sentiment, silence detection, talk-ratio, call metrics aggregation.
- Channel federation: capability matrix and unified router.
- Billing guards: realtime credit guard and minute rounding policy.

The module remains self-contained under `CallingAgent/` and includes an extraction inventory under `LegacySources/Pass11/`.
