<?php
namespace Modules\CallingAgent\Contracts;

interface STTProvider
{
    public function transcribe(string $audioUrl, array $options = []): array;
    public function supportsStreaming(): bool;
}
