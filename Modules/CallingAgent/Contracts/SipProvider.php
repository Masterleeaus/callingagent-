<?php
namespace Modules\CallingAgent\Contracts;

interface SipProvider
{
    public function invite(string $destination, array $context = []): array;
    public function bridge(string $callSid, string $sipUri, array $options = []): array;
    public function hangup(string $callSid): bool;
}
