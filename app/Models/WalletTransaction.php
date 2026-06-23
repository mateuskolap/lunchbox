<?php

namespace App\Models;

use App\Observers\WalletTransactionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[ObservedBy(WalletTransactionObserver::class)]
class WalletTransaction extends Model
{
    protected $fillable = [
        'customer_id',
        'amount',
        'description',
        'transactionable_id',
        'transactionable_type',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }
}
