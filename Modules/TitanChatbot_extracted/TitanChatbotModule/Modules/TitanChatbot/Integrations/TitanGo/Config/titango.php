<?php

return [
    // Allow enabling Titan Go during dev/testing without billing wiring.
    'force_enabled' => env('TITANGO_FORCE_ENABLED', false),

    // Titan Zero action dispatch endpoint (same app). Example:
    // https://ops.tradiesm.art/dashboard/user/titanzero/actions/dispatch
    'titanzero_action_url' => env('TITANGO_TITANZERO_ACTION_URL', null),

    // Default locale for browser speech recognition
    'speech_lang' => env('TITANGO_SPEECH_LANG', 'en-AU'),
];
