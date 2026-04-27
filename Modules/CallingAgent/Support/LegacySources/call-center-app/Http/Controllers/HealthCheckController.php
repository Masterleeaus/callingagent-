<?php

namespace App\Http\Controllers;

use App\Services\HealthCheckService;
use Illuminate\Http\JsonResponse;

class HealthCheckController extends Controller
{
    protected $healthService;

    public function __construct(HealthCheckService $healthService)
    {
        $this->healthService = $healthService;
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'twilio' => $this->healthService->checkTwilio(),
            'openai' => $this->healthService->checkOpenAI(),
            'elevenlabs' => $this->healthService->checkElevenLabs(),
        ]);
    }
}
