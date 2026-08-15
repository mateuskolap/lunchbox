<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $customer_id
 * @property string $amount
 * @property string $description
 * @property string $transactionable_type
 * @property string $transactionable_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $customer_balance
 */
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

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function transactionable()
    {
        return $this->morphTo();
    }
}
