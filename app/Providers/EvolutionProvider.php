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
            message_id: $response['data']['Info']['ID'],
            chat: $response['data']['Info']['Chat'] ?? $data->phone_number,
            sender: $response['data']['Info']['Sender'] ?? '',
            is_from_me: $response['data']['Info']['IsFromMe'] ?? true,
            type: $response['data']['Info']['Type'] ?? 'ExtendedTextMessage',
            text: $response['data']['Message']['extendedTextMessage']['text'] ?? $data->content,
            timestamp: Carbon::parse($response['data']['Info']['Timestamp'] ?? now())
        );
    }
}
