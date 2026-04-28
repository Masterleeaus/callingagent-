<?php
namespace Modules\TitanChatbot\AI\DataModels;

interface DataModelInterface
{
    public static function fromArray(array $data): static;
    public function toArray(): array;
    public function isValid(): bool;
}
