<?php
namespace Modules\CallingAgent\Models;

use Illuminate\Database\Eloquent\Model;

class CallingAgentCallOutcome extends Model
{
    protected $table = 'calling_agent_call_outcomes';
    protected $guarded = [];
    protected $casts = [
        'entities'         => 'array',
        'next_actions'     => 'array',
        'raw'              => 'array',
        'handoff_required' => 'boolean',
        'booking_requested'=> 'boolean',
    ];
}
