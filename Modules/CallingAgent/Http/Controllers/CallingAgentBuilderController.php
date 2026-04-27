<?php

namespace Modules\CallingAgent\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\CallingAgent\AI\Agents\PersonaResolver;
use Modules\CallingAgent\Models\CallingAgent;
use Modules\CallingAgent\Models\CallingAgentPhoneNumber;
use Modules\CallingAgent\Services\TransferRoutingService;

final class CallingAgentBuilderController extends Controller
{
    public function index()
    {
        return view('calling-agent::builder.index');
    }

    /**
     * Load a calling agent's full builder settings.
     */
    public function load(Request $request, int $agentId): JsonResponse
    {
        $agent = CallingAgent::findOrFail($agentId);

        $settings = $agent->settings ?? [];
        $phoneNumbers = CallingAgentPhoneNumber::where('calling_agent_id', $agentId)
            ->get(['id', 'number', 'provider', 'capabilities']);

        return response()->json([
            'agent'         => $agent->only([
                'id', 'name', 'status', 'phone_number',
                'first_message', 'instructions', 'language',
                'elevenlabs_agent_id', 'elevenlabs_voice_id',
            ]),
            'settings'      => $settings,
            'phone_numbers' => $phoneNumbers,
            'webhook_urls'  => $this->buildWebhookUrls(),
        ]);
    }

    /**
     * Save builder settings for a calling agent.
     */
    public function save(Request $request, int $agentId): JsonResponse
    {
        $agent = CallingAgent::findOrFail($agentId);

        $validated = $request->validate([
            'name'                  => 'sometimes|string|max:255',
            'first_message'         => 'nullable|string|max:2000',
            'instructions'          => 'nullable|string',
            'language'              => 'nullable|string|max:12',
            'elevenlabs_agent_id'   => 'nullable|string|max:255',
            'elevenlabs_voice_id'   => 'nullable|string|max:255',
            'settings'              => 'nullable|array',
            'settings.greeting'     => 'nullable|string|max:2000',
            'settings.persona'      => 'nullable|string|max:100',
            'settings.business_hours' => 'nullable|array',
            'settings.realtime_bridge' => 'nullable|boolean',
            'settings.voicemail_enabled' => 'nullable|boolean',
            'settings.recording_enabled' => 'nullable|boolean',
            'settings.transfer_routing'  => 'nullable|array',
        ]);

        $settings = array_merge($agent->settings ?? [], $validated['settings'] ?? []);

        $agent->update(array_merge(
            array_filter(array_intersect_key($validated, array_flip([
                'name', 'first_message', 'instructions',
                'language', 'elevenlabs_agent_id', 'elevenlabs_voice_id',
            ])), fn ($v) => $v !== null),
            ['settings' => $settings]
        ));

        return response()->json(['success' => true, 'agent' => $agent->refresh()]);
    }

    /**
     * Assign a Twilio phone number to a calling agent.
     */
    public function assignNumber(Request $request, int $agentId): JsonResponse
    {
        $agent = CallingAgent::findOrFail($agentId);

        $validated = $request->validate([
            'number'       => 'required|string',
            'provider'     => 'nullable|string|in:twilio',
            'provider_sid' => 'nullable|string',
        ]);

        $phoneNumber = CallingAgentPhoneNumber::updateOrCreate(
            ['number' => $validated['number']],
            [
                'calling_agent_id' => $agent->id,
                'provider'         => $validated['provider'] ?? 'twilio',
                'provider_sid'     => $validated['provider_sid'] ?? null,
            ]
        );

        // Also update the agent's primary phone_number field
        if (!$agent->phone_number) {
            $agent->update(['phone_number' => $validated['number']]);
        }

        return response()->json([
            'success'      => true,
            'phone_number' => $phoneNumber,
            'webhook_urls' => $this->buildWebhookUrls(),
        ]);
    }

    /**
     * Preview persona and routing decisions without persisting.
     */
    public function preview(Request $request): array
    {
        $payload  = $request->all();
        $persona  = (new PersonaResolver())->resolve(
            $payload['agent'] ?? [],
            $payload['caller'] ?? [],
            $payload['context'] ?? []
        );
        $routing  = (new TransferRoutingService())->route(
            $payload['outcome'] ?? [],
            $payload['routing'] ?? [],
            $payload['caller'] ?? []
        );

        return ['persona' => $persona, 'routing' => $routing];
    }

    /**
     * Return the Twilio webhook URLs for the current app.
     */
    private function buildWebhookUrls(): array
    {
        return [
            'inbound_voice'  => url('/calling-agent/webhooks/twilio/voice/incoming'),
            'gather'         => url('/calling-agent/webhooks/twilio/voice/gather'),
            'status'         => url('/calling-agent/webhooks/twilio/voice/status'),
            'recording'      => url('/calling-agent/webhooks/twilio/voice/recording'),
            'voicemail'      => url('/calling-agent/webhooks/twilio/voice/voicemail'),
            'sms_whatsapp'   => url('/calling-agent/webhooks/twilio/message/incoming'),
        ];
    }
}
