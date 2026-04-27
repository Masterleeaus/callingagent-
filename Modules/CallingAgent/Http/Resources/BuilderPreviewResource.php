<?php
namespace Modules\CallingAgent\Http\Resources;

final readonly class BuilderPreviewResource
{
    public function __construct(private array $payload) {}
    public function toArray(): array { return $this->payload; }
}
