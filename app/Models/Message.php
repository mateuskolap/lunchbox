<?php

namespace App\Models;

use App\Enums\MessageChannelEnum;
use App\Enums\MessageStatusEnum;
use App\Enums\MessageTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $external_id
 * @property string|null $customer_id
 * @property string $recipient
 * @property MessageChannelEnum $channel
 * @property MessageTypeEnum $type
 * @property string|null $text
 * @property MessageStatusEnum $status
 * @property Carbon $sent_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
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
        'sent_at',
    ];

    protected $casts = [
        'channel' => MessageChannelEnum::class,
        'type' => MessageTypeEnum::class,
        'status' => MessageStatusEnum::class,
        'sent_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
