<?php
namespace Modules\CallingAgent\Models;

class CallingAgentCallOutcome
{
    protected $table = 'calling_agent_call_outcomes';
    protected $guarded = [];
    protected $casts = ['entities' => 'array', 'next_actions' => 'array', 'raw' => 'array'];
}
