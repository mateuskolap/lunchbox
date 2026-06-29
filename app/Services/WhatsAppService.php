<?php

namespace App\Services;

use App\Data\WhatsApp\SendTextMessageData;
use App\Enums\MessageChannelEnum;
use App\Enums\MessageStatusEnum;
use App\Enums\MessageTypeEnum;
use App\Jobs\SendWhatsAppTextMessageJob;
use App\Models\Customer;
use App\Models\Message;
use App\Providers\WhatsAppProviderInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use InvalidArgumentException;
use Throwable;

readonly class WhatsAppService
{
    public function __construct(
        private WhatsAppProviderInterface $provider
    )
    {
    }

    /**
     * @throws RequestException
     * @throws Throwable
     * @throws ConnectionException
     */
    public function send(Message $message): void
    {
        $message->update([
            'status' => MessageStatusEnum::PROCESSING
        ]);

        $response = match ($message->type) {
            MessageTypeEnum::TEXT => $this->provider->sendText(
                new SendTextMessageData(
                    phone_number: $message->recipient,
                    content: $message->text
                )
            ),
            default => throw new InvalidArgumentException("Tipo de mensagem não suportado: {$message->type->value}"),
        };

        $message->update([
            'external_id' => $response->external_id,
            'status' => MessageStatusEnum::SENT,
            'sent_at' => $response->sent_at,
        ]);
    }

    public function queueText(SendTextMessageData $data): Message
    {
        $message = $this->createMessage(
            recipient: $data->phone_number,
            type: MessageTypeEnum::TEXT,
            text: $data->content,
        );

        SendWhatsAppTextMessageJob::dispatch($message);

        return $message;
    }

    private function createMessage(string $recipient, MessageTypeEnum $type, ?string $text = null): Message
    {
        $customer = Customer::where('phone', $recipient)->first();

        return Message::create([
            'customer_id' => $customer?->id,
            'recipient' => $recipient,
            'channel' => MessageChannelEnum::WHATSAPP,
            'type' => $type,
            'text' => $text,
            'status' => MessageStatusEnum::QUEUED,
            'sent_at' => now(),
        ]);
    }
}
