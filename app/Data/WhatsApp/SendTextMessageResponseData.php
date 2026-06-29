<?php

namespace App\Data\WhatsApp;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class SendTextMessageResponseData extends Data
{
    public function __construct(
        public string $external_id,
        public string $text,
        public Carbon $sent_at
    )
    {
    }
}
