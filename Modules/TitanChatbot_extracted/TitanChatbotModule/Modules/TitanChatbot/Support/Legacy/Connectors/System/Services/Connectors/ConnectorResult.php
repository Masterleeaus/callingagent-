<?php

namespace Extensions\Connectors\System\Services\Connectors;

class ConnectorResult
{
    public function __construct(
        public string $status,
        public string $message = '',
        public array $data = []
    ) {}

    public static function success(string $message = 'OK', array $data = []): self
    {
        return new self('success', $message, $data);
    }

    public static function error(string $message, array $data = []): self
    {
        return new self('error', $message, $data);
    }
}
