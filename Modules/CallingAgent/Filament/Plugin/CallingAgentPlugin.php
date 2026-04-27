<?php

namespace Modules\CallingAgent\Filament\Plugin;

class CallingAgentPlugin
{
    public static function make(): static
    {
        return new static();
    }

    public function getId(): string
    {
        return 'calling-agent';
    }
}
