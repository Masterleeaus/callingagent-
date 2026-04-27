<?php
return [
    'default' => env('CALLING_AGENT_CALENDAR_PROVIDER', 'google'),
    'providers' => ['google' => true, 'outlook' => true, 'caldav' => true],
    'slot_minutes' => 30,
    'timezone' => env('APP_TIMEZONE', 'UTC'),
];
