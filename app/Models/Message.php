<?php

namespace App\Models;

use App\Enums\MessageChannelEnum;
use App\Enums\MessageStatusEnum;
use App\Enums\MessageTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'external_id',
        'customer_id',
        'recipient',
        'channel',
        'type',
        'text',
        'status',
        'sent_at'
    ];

    protected $casts = [
        'channel' => MessageChannelEnum::class,
        'type' => MessageTypeEnum::class,
        'status' => MessageStatusEnum::class,
        'sent_at' => 'datetime'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
