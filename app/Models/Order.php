<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use LogicException;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property int $id
 * @property string $customer_id
 * @property string $total_amount
 * @property string $paid_amount
 * @property OrderStatusEnum $status
 * @property Carbon $date
 * @property string|null $observation
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
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

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }

    #[Scope]
    protected function paid(Builder $query): void
    {
        $query->whereColumn('paid_amount', 'total_amount')
            ->where('status', '!=', OrderStatusEnum::CANCELED);
    }

    #[Scope]
    protected function unpaid(Builder $query): void
    {
        $query->whereColumn('paid_amount', '<', 'total_amount')
            ->where('status', '!=', OrderStatusEnum::CANCELED);
    }

    #[Scope]
    protected function partiallyPaid(Builder $query): void
    {
        $query->where('paid_amount', '>', 0)
            ->whereColumn('paid_amount', '<', 'total_amount')
            ->where('status', '!=', OrderStatusEnum::CANCELED);
    }

    public function isClosed(): bool
    {
        return in_array($this->status, [OrderStatusEnum::CONCLUDED, OrderStatusEnum::CANCELED]);
    }

    public function recalculateTotals(): void
    {
        $total = $this->items()->sum('total_amount');

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
        if (! $this->isClosed()) {
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
