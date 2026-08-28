<?php

namespace App\Data\WhatsApp;

use Spatie\LaravelData\Data;

class SendTextMessageData extends Data
{
    public function __construct(
        public string $phone_number,
        public string $content
    ) {}
}
