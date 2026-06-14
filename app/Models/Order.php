<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Order extends Model
{
    use LogsActivity, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'total_amount',
        'total_items_amount',
        'status',
        'date',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'total_items_amount' => 'decimal:2',
        'status' => OrderStatusEnum::class,
        'date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isClosed(): bool
    {
        return in_array($this->status, [OrderStatusEnum::CONCLUDED, OrderStatusEnum::CANCELED]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function recalculateTotals(): void
    {
        $total = (float)$this->items()->sum('total_amount');

        $this->update([
            'total_amount' => $total,
            'total_items_amount' => $total,
        ]);
    }
}
