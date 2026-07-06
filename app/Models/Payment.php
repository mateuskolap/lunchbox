<?php

namespace App\Models;

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Payment extends Model
{
    use LogsActivity, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'method',
        'status',
        'value',
        'paid_at',
    ];

    protected $casts = [
        'method' => PaymentMethodEnum::class,
        'status' => PaymentStatusEnum::class,
        'value' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }

    public function confirm(): void
    {
        $this->update([
            'status' => PaymentStatusEnum::CONFIRMED,
        ]);
    }

    public function cancel(): void
    {
        $this->update([
            'status' => PaymentStatusEnum::CANCELED,
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
