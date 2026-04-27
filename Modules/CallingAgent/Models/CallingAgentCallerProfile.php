<?php
namespace Modules\CallingAgent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CallingAgentCallerProfile extends Model
{
    use SoftDeletes;

    protected $table = 'calling_agent_caller_profiles';
    protected $guarded = [];
    protected $casts = [
        'tags'         => 'array',
        'preferences'  => 'array',
        'last_outcome' => 'array',
        'last_seen_at' => 'datetime',
    ];
}
