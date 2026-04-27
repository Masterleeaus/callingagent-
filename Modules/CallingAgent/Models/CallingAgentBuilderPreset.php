<?php
namespace Modules\CallingAgent\Models;

class CallingAgentBuilderPreset
{
    protected $table = 'calling_agent_builder_presets';
    protected $guarded = [];
    protected $casts = ['schema' => 'array', 'enabled_channels' => 'array', 'routing_tree' => 'array'];
}
