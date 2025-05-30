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
        Schema::table('statement_transactions', function (Blueprint $table) {
           Schema::rename('bank_transactions', 'statement_transactions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('statement_transactions', function (Blueprint $table) {
           Schema::rename('statement_transactions', 'bank_transactions');
        });
    }
};
