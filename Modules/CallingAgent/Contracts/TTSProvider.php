<?php
namespace Modules\CallingAgent\Contracts;

interface TTSProvider
{
    public function synthesize(string $text, array $voice = [], array $options = []): array;
    public function streamUrl(string $text, array $voice = [], array $options = []): ?string;
}
