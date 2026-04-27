<?php
namespace Modules\CallingAgent\Http\Controllers;

use Modules\CallingAgent\AI\Agents\PersonaResolver;
use Modules\CallingAgent\Services\TransferRoutingService;

final class CallingAgentBuilderController
{
    public function index()
    {
        return view('calling-agent::builder.index');
    }

    public function preview(array $payload): array
    {
        $persona = (new PersonaResolver())->resolve($payload['agent'] ?? [], $payload['caller'] ?? [], $payload['context'] ?? []);
        $routing = (new TransferRoutingService())->route($payload['outcome'] ?? [], $payload['routing'] ?? [], $payload['caller'] ?? []);
        return ['persona' => $persona, 'routing' => $routing];
    }
}
