<?php

namespace Extensions\Connectors\System\Models;

use Illuminate\Database\Eloquent\Model;

class ConnectorRun extends Model
{
    protected $table = 'connector_runs';

    protected $fillable = ['tenant_id','user_id','flow','status','results_json'];
}
