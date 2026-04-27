<?php

namespace Extensions\Connectors\System\Models;

use Illuminate\Database\Eloquent\Model;

class ClientPackTemplate extends Model
{
    protected $table = 'connector_client_pack_templates';

    protected $fillable = ['tenant_id', 'name', 'is_default', 'template_json'];

    protected $casts = ['is_default' => 'boolean'];

    public function getTemplate(): array
    {
        return json_decode($this->template_json ?? '{}', true) ?: [];
    }

    public function setTemplate(array $template): void
    {
        $this->template_json = json_encode($template);
    }
}
