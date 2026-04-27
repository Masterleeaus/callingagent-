<?php

namespace App\Services;

use Twilio\Rest\Client;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VoiceGrant;

class TwilioService
{
    protected $client;
    protected $accountSid;
    protected $authToken;
    protected $appSid;

    public function __construct()
    {
        $this->accountSid = config('services.twilio.account_sid');
        $this->authToken = config('services.twilio.auth_token');
        $this->appSid = config('services.twilio.app_sid');

        if ($this->accountSid && $this->authToken) {
            $this->client = new Client($this->accountSid, $this->authToken);
        }
    }

    public function purchaseNumber(string $areaCode)
    {
        $numbers = $this->client->availablePhoneNumbers('US')
            ->local
            ->read(['areaCode' => $areaCode], 1);

        if (empty($numbers)) {
            throw new \Exception("No numbers available for area code: $areaCode");
        }

        $number = $this->client->incomingPhoneNumbers
            ->create([
                'phoneNumber' => $numbers[0]->phoneNumber,
                'voiceUrl' => route('webhooks.twilio.voice'),
            ]);

        return $number;
    }

    public function generateToken(string $identity)
    {
        $token = new AccessToken(
            $this->accountSid,
            config('services.twilio.api_key'),
            config('services.twilio.api_secret'),
            3600,
            $identity
        );

        $voiceGrant = new VoiceGrant();
        $voiceGrant->setOutgoingApplicationSid($this->appSid);
        $voiceGrant->setPushCredentialSid(config('services.twilio.push_credential_sid'));

        $token->addGrant($voiceGrant);

        return $token->toJWT();
    }

    public function getRecording(string $recordingSid)
    {
        return $this->client->recordings($recordingSid)->fetch();
    }

    public function makeOutboundCall(string $to, string $from, string $flowId)
    {
        return $this->client->calls->create(
            $to,
            $from,
            [
                'url' => route('webhooks.twilio.voice', ['flow_id' => $flowId])
            ]
        );
    }

    public function sendSms(string $to, string $from, string $message)
    {
        return $this->client->messages->create(
            $to,
            [
                'from' => $from,
                'body' => $message
            ]
        );
    }

    public function hangupCall(string $callSid)
    {
        return $this->client->calls($callSid)->update([
            'status' => 'completed'
        ]);
    }
}
