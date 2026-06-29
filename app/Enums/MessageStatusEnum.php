<?php

namespace App\Enums;

enum MessageStatusEnum: string
{
    case QUEUED = 'queued';
    case PROCESSING = 'processing';
    case SENT = 'sent';
    case FAILED = 'failed';
}
