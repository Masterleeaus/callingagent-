<?php
namespace Modules\CallingAgent\Contracts;

interface ChannelDriver
{
    public function key(): string;
    public function capabilities(): array;
    public function inbound(array $payload): array;
    public function outbound(array $message): array;
}
