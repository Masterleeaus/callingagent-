<?php
namespace Modules\CallingAgent\Enums;
enum CallStatus: string {
    case QUEUED = 'queued';
    case RINGING = 'ringing';
    case IN_PROGRESS = 'in-progress';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case BUSY = 'busy';
    case NO_ANSWER = 'no-answer';
}
