<?php

namespace App\Providers;

use App\Data\WhatsApp\SendTextMessageData;
use App\Data\WhatsApp\SendTextMessageResponseData;
use Carbon\Carbon;
use Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class EvolutionProvider implements WhatsAppProviderInterface
{
    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function sendText(SendTextMessageData $data): SendTextMessageResponseData
    {
        $response = Http::evolution()->post('/send/text', [
            'number' => $data->phone_number,
            'text' => $data->content,
        ])->throw()->json();

        return new SendTextMessageResponseData(
            external_id: $response['data']['Info']['ID'],
            text: $response['data']['Message']['extendedTextMessage']['text'] ?? $data->content,
            sent_at: Carbon::parse($response['data']['Info']['Timestamp'] ?? now())
        );
    }
}
