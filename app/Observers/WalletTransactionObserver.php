<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\WalletTransaction;

class WalletTransactionObserver
{
    /**
     * Handle the WalletTransaction "created" event.
     */
    public function created(WalletTransaction $walletTransaction): void
    {
        $walletTransaction->customer()->increment('wallet_balance', $walletTransaction->amount);

        if ($walletTransaction->transactionable_type === Order::class) {
            $walletTransaction->transactionable()->increment('paid_amount', -$walletTransaction->amount);
        }
    }
}
