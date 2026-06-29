<?php

namespace App\Data\WhatsApp;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class SendTextMessageResponseData extends Data
{
    public function __construct(
        public string $message_id,
        public string $chat,
        public string $sender,
        public bool   $is_from_me,
        public string $type,
        public string $text,
        public Carbon $timestamp
    )
    {
    }
}
