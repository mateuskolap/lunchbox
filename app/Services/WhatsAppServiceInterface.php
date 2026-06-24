<?php

namespace App\Services;

use App\Data\WhatsApp\SendTextMessageData;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

interface WhatsAppServiceInterface
{
    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function sendText(SendTextMessageData $data): void;
}
