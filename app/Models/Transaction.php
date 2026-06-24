<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Transaction extends Model
{
    protected $fillable = [
        'customer_id',
        'amount',
        'description',
        'customer_balance',
        'transactionable_id',
        'transactionable_type',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'customer_balance' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function transactionable(): MorphTo
    {
        return $this->morphTo()->withTrashed();
    }
}
