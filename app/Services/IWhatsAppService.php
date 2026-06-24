<?php

namespace App\Services;

use App\Data\WhatsApp\SendTextMessageData;

interface IWhatsAppService
{
    public function sendText(SendTextMessageData $data): void;
}