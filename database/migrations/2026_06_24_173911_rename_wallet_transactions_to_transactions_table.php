<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('wallet_transactions', 'transactions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('transactions', 'wallet_transactions');
    }
};
