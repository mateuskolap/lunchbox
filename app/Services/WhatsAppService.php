<?php

namespace App\Services;

use App\Data\WhatsApp\SendTextMessageData;
use Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class WhatsAppService implements IWhatsAppService
{
    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function sendText(SendTextMessageData $data): void
    {
        Http::evolution()->post('/send/text', [
            'number' => $data->phone_number,
            'text' => $data->content,
        ])
        ->throw();
    }
}
