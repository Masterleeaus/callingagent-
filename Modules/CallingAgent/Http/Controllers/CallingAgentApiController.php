<?php

namespace Modules\CallingAgent\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\CallingAgent\Models\CallingAgentCall;
use Modules\CallingAgent\Services\ReceptionistOrchestrator;
use Modules\CallingAgent\Services\TwilioChannelService;

class CallingAgentApiController extends Controller
{
    public function showCall(CallingAgentCall $call): CallingAgentCall
    {
        return $call->load([]);
    }

    public function sendSms(Request $request, TwilioChannelService $twilio): array
    {
        return $twilio->sendSms(
            $request->string('to'),
            $request->string('body'),
            $request->input('from')
        );
    }

    public function sendWhatsapp(Request $request, TwilioChannelService $twilio): array
    {
        return $twilio->sendWhatsapp(
            $request->string('to'),
            $request->string('body'),
            $request->input('from')
        );
    }

    /**
     * Initiate an outbound call via Twilio and persist a call log.
     */
    public function placeCall(Request $request, TwilioChannelService $twilio): JsonResponse
    {
        $validated = $request->validate([
            'to'               => 'required|string',
            'from'             => 'nullable|string',
            'twiml_url'        => 'required|url',
            'status_callback'  => 'nullable|url',
        ]);

        $result = $twilio->placeCall(
            $validated['to'],
            $validated['twiml_url'],
            $validated['from'] ?? null,
            $validated['status_callback'] ?? null
        );

        // Persist the outbound call log
        $call = CallingAgentCall::create([
            'provider'   => 'twilio',
            'call_sid'   => $result['sid'],
            'direction'  => 'outbound',
            'from'       => $result['from'],
            'to'         => $result['to'],
            'status'     => $result['status'] ?? 'queued',
            'started_at' => now(),
            'metadata'   => $result,
        ]);

        return response()->json(['call_id' => $call->id, 'call_sid' => $call->call_sid, 'status' => $call->status]);
    }

    /**
     * Transfer a live call to a target number / SIP URI.
     */
    public function transfer(Request $request, TwilioChannelService $twilio, string $callSid): JsonResponse
    {
        $validated = $request->validate([
            'target' => 'required|string',
        ]);

        // Generate a Dial TwiML and update the call via Twilio REST
        $twiml = '<Response><Dial>' . e($validated['target']) . '</Dial></Response>';

        try {
            $twilio->client()->calls($callSid)->update([
                'twiml' => $twiml,
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }

        // Persist transfer attempt
        try {
            \DB::table('calling_agent_transfer_attempts')->insert([
                'call_sid'       => $callSid,
                'target_number'  => $validated['target'],
                'status'         => 'initiated',
                'attempted_at'   => now(),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json(['success' => true, 'call_sid' => $callSid, 'target' => $validated['target']]);
    }

    /**
     * Hang up a live call.
     */
    public function hangup(Request $request, TwilioChannelService $twilio, ReceptionistOrchestrator $orchestrator, string $callSid): JsonResponse
    {
        try {
            $twilio->client()->calls($callSid)->update(['status' => 'completed']);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }

        $orchestrator->complete($callSid, ['CallStatus' => 'completed', 'CallSid' => $callSid]);

        return response()->json(['success' => true, 'call_sid' => $callSid]);
    }
}
