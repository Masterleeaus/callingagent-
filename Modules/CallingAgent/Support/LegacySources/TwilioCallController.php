<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Twilio\TwiML\VoiceResponse;

class TwilioCallController extends Controller
{
    /**
     * Handle an incoming Twilio call.
     * Twilio will POST to this endpoint when a call is received.
     */
    public function incoming(Request $request): Response
    {
        $response = new VoiceResponse();

        $response->say('Hello! You have reached the AI call agent. Please wait while we connect you.');

        return response($response->asXML(), 200)
            ->header('Content-Type', 'text/xml');
    }

    /**
     * Handle the call status callback from Twilio.
     */
    public function status(Request $request): Response
    {
        // Log or handle call status updates (e.g., completed, failed)
        return response('', 204);
    }
}
