<?php
namespace Modules\CallingAgent\Enums;

enum ProviderType: string
{
    case TWILIO = 'twilio';
    case ELEVENLABS = 'elevenlabs';
    case OPENAI = 'openai';
    case VAPI = 'vapi';
    case BLAND = 'bland';
}
