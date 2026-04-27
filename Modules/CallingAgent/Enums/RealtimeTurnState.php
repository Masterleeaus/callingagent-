<?php
namespace Modules\CallingAgent\Enums;

enum RealtimeTurnState: string
{
    case LISTENING = 'listening';
    case THINKING = 'thinking';
    case SPEAKING = 'speaking';
    case INTERRUPTED = 'interrupted';
    case ENDED = 'ended';
}
