<?php

declare(strict_types=1);

namespace Modules\CallingAgent\AI\Tools\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class Tool
{
    public function __construct(
        public string $name = '',
        public string $description = '',
    ) {}
}
