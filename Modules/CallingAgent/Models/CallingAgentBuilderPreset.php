<?php
namespace Modules\CallingAgent\Models;

use Illuminate\Database\Eloquent\Model;

class CallingAgentBuilderPreset extends Model
{
    protected $table = 'calling_agent_builder_presets';
    protected $guarded = [];
    protected $casts = [
        'schema'           => 'array',
        'enabled_channels' => 'array',
        'routing_tree'     => 'array',
    ];
}
