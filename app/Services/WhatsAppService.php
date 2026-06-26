<?php

namespace App\Services;

use App\Data\WhatsApp\SendTextMessageData;
use App\Models\Customer;
use App\Models\WhatsAppMessage;
use App\Providers\WhatsAppProviderInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

readonly class WhatsAppService
{

    public function __construct(
        private WhatsAppProviderInterface $provider
    )
    {
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function sendText(SendTextMessageData $data): WhatsAppMessage
    {
        $response = $this->provider->sendText($data);

        $customer = Customer::where('phone', $data->phone_number)->first();

        return WhatsAppMessage::create([
            ...$response->toArray(),
            'customer_id' => $customer?->id,
            'message_timestamp' => $response->timestamp,
        ]);
    }
}
