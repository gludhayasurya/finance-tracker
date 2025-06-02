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
            $table->string('mode')->nullable()->after('type');
            $table->text('particulars')->nullable()->after('mode');
            $table->decimal('deposit', 15, 2)->nullable()->after('amount');
            $table->decimal('withdrawal', 15, 2)->nullable()->after('deposit');
            $table->decimal('balance', 15, 2)->nullable()->after('withdrawal');
            $table->unsignedBigInteger('imported_id')->nullable()->after('bank_id');
            $table->timestamp('imported_at')->nullable()->after('imported_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_manual_transactions', function (Blueprint $table) {
            $table->dropColumn(['mode', 'particulars', 'deposit', 'withdrawal', 'balance', 'imported_id', 'imported_at']);
        });
    }
};
