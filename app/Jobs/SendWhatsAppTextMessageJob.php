<?php

namespace App\Jobs;

use App\Enums\MessageStatusEnum;
use App\Models\Message;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Throwable;

class SendWhatsAppTextMessageJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Message $message
    )
    {
    }

    public function retries(): int
    {
        return 3;
    }

    public function backoff(): array
    {
        return [10, 30, 60];
    }

    /**
     * @throws RequestException
     * @throws Throwable
     * @throws ConnectionException
     */
    public function handle(WhatsAppService $whatsAppService): void
    {
        $whatsAppService->send($this->message);
    }

    public function failed(Throwable $exception): void
    {
        $this->message->update(['status' => MessageStatusEnum::FAILED->value]);
    }
}
