<?php
namespace Modules\TitanChatbot\AI\DataModels;

abstract class BaseDataModel implements DataModelInterface
{
    public static function fromArray(array $data): static
    {
        $instance = new static();
        foreach ($data as $key => $value) {
            if (property_exists($instance, $key)) {
                $instance->$key = $value;
            }
        }
        return $instance;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function isValid(): bool
    {
        return true;
    }
}
