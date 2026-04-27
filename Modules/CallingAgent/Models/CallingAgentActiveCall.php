<?php
namespace Modules\CallingAgent\Models;
use Illuminate\Database\Eloquent\Model;
class CallingAgentActiveCall extends Model { protected $table='calling_agent_active_calls'; protected $guarded=[]; protected $casts=['settings'=>'array','metadata'=>'array','context'=>'array','definition'=>'array','capabilities'=>'array','started_at'=>'datetime','ended_at'=>'datetime','last_seen_at'=>'datetime']; }
