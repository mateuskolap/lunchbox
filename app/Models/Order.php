<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use LogicException;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Order extends Model
{
    use CascadeSoftDeletes, LogsActivity, SoftDeletes;

    protected array $cascadeDeletes = ['items'];

    protected $fillable = [
        'customer_id',
        'total_amount',
        'paid_amount',
        'status',
        'observation',
        'date',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'status' => OrderStatusEnum::class,
        'date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }

    #[Scope]
    protected function unpaid(Builder $query): void
    {
        $query->whereColumn('paid_amount', '<', 'total_amount')
            ->where('status', '!=', OrderStatusEnum::CANCELED);
    }

    public function isClosed(): bool
    {
        return in_array($this->status, [OrderStatusEnum::CONCLUDED, OrderStatusEnum::CANCELED]);
    }

    public function recalculateTotals(): void
    {
        $total = (float)$this->items()->sum('total_amount');

        $this->update([
            'total_amount' => $total,
        ]);
    }

    /**
     * @throws LogicException
     */
    public function conclude(): void
    {
        if ($this->status === OrderStatusEnum::CANCELED) {
            throw new LogicException('Pedidos cancelados não podem ser concluídos.');
        }

        $this->update([
            'status' => OrderStatusEnum::CONCLUDED,
        ]);
    }

    public function cancel(): void
    {
        $this->update([
            'status' => OrderStatusEnum::CANCELED,
        ]);
    }

    /**
     * @throws LogicException
     */
    public function reopen(): void
    {
        if (!$this->isClosed()) {
            throw new LogicException('Apenas pedidos concluídos ou cancelados podem ser reabertos.');
        }

        $this->update([
            'status' => OrderStatusEnum::PENDING,
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
