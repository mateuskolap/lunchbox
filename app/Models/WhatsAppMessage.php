<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppMessage extends Model
{
    protected $table = 'whatsapp_messages';

    protected $fillable = [
        'message_id',
        'customer_id',
        'chat',
        'sender',
        'is_from_me',
        'type',
        'text',
        'message_timestamp'
    ];

    protected $casts = [
        'is_from_me' => 'boolean',
        'message_timestamp' => 'datetime'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
