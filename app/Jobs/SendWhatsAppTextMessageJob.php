<?php

namespace App\Jobs;

use App\Data\WhatsApp\SendTextMessageData;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendWhatsAppTextMessageJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public SendTextMessageData $data
    )
    {
    }

    public function retries(): int
    {
        return 3;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        app(WhatsAppService::class)->sendText($this->data);
    }
}
