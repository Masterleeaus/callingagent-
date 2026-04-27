<?php
namespace Modules\CallingAgent\Http\Controllers; use Illuminate\Routing\Controller; use Modules\CallingAgent\Models\CallingAgentCall; class CallingAgentDashboardController extends Controller { public function index(){ return view('calling-agent::dashboard.index',['calls'=>CallingAgentCall::latest()->limit(25)->get()]); } }
