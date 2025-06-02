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
        Schema::table('imports', function (Blueprint $table) {
            $table->decimal('total_withdrawal', 15, 2)->nullable()->after('filepath');
            $table->decimal('total_deposit', 15, 2)->nullable()->after('total_withdrawal');
            $table->decimal('total_balance', 15, 2)->nullable()->after('total_deposit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imports', function (Blueprint $table) {
            $table->dropColumn('total_withdrawal');
            $table->dropColumn('total_deposit');
            $table->dropColumn('total_balance');
        });
    }
};
