<?php

namespace App\Extensions\ElevenLabsVoiceChat\System\Http\Controllers;

use App\Domains\Entity\Enums\EntityEnum;
use App\Domains\Entity\Facades\Entity as EntityFacade;
use App\Extensions\ElevenLabsVoiceChat\System\Http\Requests\VoiceChatbotUpdateRequest;
use App\Extensions\ElevenLabsVoiceChat\System\Services\ElevenLabsVoiceChatService;
use App\Helpers\Classes\Helper;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

class ElevenLabsVoiceChatController extends Controller
{
    public function __construct(public ElevenLabsVoiceChatService $service) {}

    /**
     * setting page for elevenlabs voice chat
     */
    public function index()
    {
        $item = $this->service->fetchVoiceChatbot();
        $data = empty($item) ? null : $item->trainData();
        $voices = $this->service->getVoices();

        return view('elevenlabs-voice-chat::index', compact('item', 'data', 'voices'));
    }

    /**
     * update the voice chatbot configure
     */
    public function update(VoiceChatbotUpdateRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $chatbot = $this->service->fetchVoiceChatbot();
            $chatbot->update($data);

            return response()->json(['status' => 'success']);
        } catch (Throwable $th) {
            return response()->json([
                'status' 		    => 'error',
                'message' 		   => 'Something went wrong!',
                'erroMessage' 	=> $th->getMessage(),
            ]);
        }
    }

    public function checkVoiceBalance(Request $request): ?JsonResponse
    {
        if (Helper::appIsDemo()) {
            $onStart = $request->input('onStart', false);
            $key = ($onStart ? 'onstart-voice-chat-attempt-:' : 'voice-chat-attempt-:') . (request()?->header('cf-connecting-ip') ?? request()?->ip());
            $tryCount = $onStart ? 1 : 4;
            if (! RateLimiter::tooManyAttempts($key, $tryCount)) {
                RateLimiter::hit($key, 60 * 60 * 24);

                return response()->json(['status' => 'success', 'message' => 'Demo mode'], 200);
            }

            return response()->json(['status' => 'error', 'message' => 'Exceeded messages limit on demo'], 200);
        }
        $chatbot = $this->service->fetchVoiceChatbot();
        if (! empty($chatbot)) {
            $user = $chatbot->user;
            $driver = EntityFacade::driver(EntityEnum::ELEVENLABS_VOICE_CHATBOT)->forUser($user);

            try {
                $driver->redirectIfNoCreditBalance();
            } catch (Exception $e) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'status'  => 'error',
                ], 200);
            }
        }

        return response()->json(['status' => 'success', 'message' => ''], 200);
    }
}
