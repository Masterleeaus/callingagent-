<?php
namespace Modules\CallingAgent\Enums;
enum ReceptionAction: string {
    case ANSWER = 'answer';
    case TRANSFER = 'transfer';
    case VOICEMAIL = 'voicemail';
    case HUMAN_AGENT = 'human_agent';
    case REPEAT = 'repeat';
}
