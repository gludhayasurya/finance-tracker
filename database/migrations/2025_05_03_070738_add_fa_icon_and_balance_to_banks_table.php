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
        Schema::table('banks', function (Blueprint $table) {
            $table->string('fa_icon')->nullable()->after('address'); // Font Awesome icon field
            $table->string('icon_color')->nullable()->after('fa_icon'); // Icon color field
            $table->decimal('current_balance', 15, 2)->default(0)->after('initial_balance'); // Current balance field
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->dropColumn(['fa_icon', 'icon_color', 'current_balance']);
        });
    }
};