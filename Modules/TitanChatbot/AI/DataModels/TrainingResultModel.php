<?php
namespace Modules\TitanChatbot\AI\DataModels;

class TrainingResultModel extends BaseDataModel
{
    public bool   $success         = false;
    public int    $chunksCreated   = 0;
    public int    $tokensUsed      = 0;
    public string $sourceType      = '';
    public string $errorMessage    = '';
    public array  $citations       = [];

    public function isValid(): bool
    {
        return $this->success || !empty($this->errorMessage);
    }
}
