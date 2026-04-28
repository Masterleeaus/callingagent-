<?php

namespace Modules\TitanChatbot\AI\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Tool
{
    public function __construct(
        public readonly string $name = '',
        public readonly string $description = '',
    ) {}
}
