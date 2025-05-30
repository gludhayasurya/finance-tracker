<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('daily_manual_transactions', function (Blueprint $table) {
            $table->string('type', 20)->change(); // extend length or convert to string
        });
    }

    public function down(): void
    {
        Schema::table('daily_manual_transactions', function (Blueprint $table) {
            $table->enum('type', ['income', 'expense'])->default('expense')->change(); // revert to original enum type
        });
    }
};
