<?php
namespace Modules\CallingAgent\Models;
use Illuminate\Database\Eloquent\Model;
class CallingAgent extends Model { protected $table='calling_agents'; protected $guarded=[]; protected $casts=['settings'=>'array','metadata'=>'array','context'=>'array','definition'=>'array','capabilities'=>'array','started_at'=>'datetime','ended_at'=>'datetime','last_seen_at'=>'datetime']; }
