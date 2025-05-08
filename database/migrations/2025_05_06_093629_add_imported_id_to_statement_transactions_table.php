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
            $table->string('imported_id')->nullable()->after('id');
            $table->foreignId('bank_id')->nullable()->constrained('banks')->after('imported_at');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('statement_transactions', function (Blueprint $table) {
            $table->dropForeign(['bank_id']);
            $table->dropColumn(['imported_id', 'bank_id']);
        });
    }
};
