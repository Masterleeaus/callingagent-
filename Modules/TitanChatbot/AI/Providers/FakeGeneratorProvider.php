<?php

namespace Modules\TitanChatbot\AI\Providers;

class FakeGeneratorProvider
{
    private string $response;

    public function __construct(string $response = 'This is a fake AI response for testing.')
    {
        $this->response = $response;
    }

    public function generate(string $prompt, array $context = []): string
    {
        return $this->response;
    }

    public function withResponse(string $response): static
    {
        $clone = clone $this;
        $clone->response = $response;
        return $clone;
    }
}
