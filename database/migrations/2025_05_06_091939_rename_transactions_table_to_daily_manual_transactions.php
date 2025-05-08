<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('daily_manual_transactions', function (Blueprint $table) {
           Schema::rename('transactions', 'daily_manual_transactions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_manual_transactions', function (Blueprint $table) {
           Schema::rename('daily_manual_transactions', 'transactions');
        });
    }
};
