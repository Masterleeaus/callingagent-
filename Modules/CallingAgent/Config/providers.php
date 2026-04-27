<?php
return [
    'telephony' => env('CALLING_AGENT_TELEPHONY_PROVIDER', 'twilio'),
    'tts' => env('CALLING_AGENT_TTS_PROVIDER', 'elevenlabs'),
    'stt' => env('CALLING_AGENT_STT_PROVIDER', 'openai'),
    'realtime' => env('CALLING_AGENT_REALTIME_PROVIDER', 'elevenlabs'),
    'fallback_order' => ['elevenlabs', 'openai', 'twilio'],
];
