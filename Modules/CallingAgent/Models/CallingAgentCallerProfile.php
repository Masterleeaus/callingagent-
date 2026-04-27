<?php
namespace Modules\CallingAgent\Models;

class CallingAgentCallerProfile
{
    protected $table = 'calling_agent_caller_profiles';
    protected $guarded = [];
    protected $casts = ['tags' => 'array', 'preferences' => 'array', 'last_outcome' => 'array'];
}
