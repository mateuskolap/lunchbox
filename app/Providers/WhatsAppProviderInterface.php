<?php

namespace App\Providers;

use App\Data\WhatsApp\SendTextMessageData;
use App\Data\WhatsApp\SendTextMessageResponseData;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

interface WhatsAppProviderInterface
{
    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function sendText(SendTextMessageData $data): SendTextMessageResponseData;
}
