<?php
namespace Modules\CallingAgent\Contracts;

interface TelephonyProvider
{
    public function placeCall(array $payload): array;
    public function sendSms(array $payload): array;
    public function transferCall(string $callSid, string $target): array;
    public function hangup(string $callSid): array;
    public function validateWebhook(array $headers, string $url, array $payload): bool;
    public function normalizeWebhook(array $payload): array;
}
