<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Enhance banks table
        Schema::table('banks', function (Blueprint $table) {
            $table->string('bank_type')->default('savings')->after('name'); // savings, current, credit_card
            $table->string('account_number')->nullable()->after('bank_type');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('icon_color');
        });

        // Enhance statement_transactions table
        Schema::table('statement_transactions', function (Blueprint $table) {
            $table->string('category')->nullable()->after('particulars'); // income, expense, transfer
            $table->string('subcategory')->nullable()->after('category'); // food, transport, salary, etc.
            $table->text('description')->nullable()->after('subcategory'); // processed description
            $table->enum('transaction_type', ['debit', 'credit'])->nullable()->after('description');
            $table->index(['date', 'category']);
            $table->index(['bank_id', 'date']);
        });

        // Create categories table
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // income, expense
            $table->string('icon')->nullable();
            $table->string('color')->default('#667eea');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Create budgets table
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('amount', 15, 2);
            $table->decimal('spent', 15, 2)->default(0);
            $table->string('period'); // monthly, yearly
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Create financial_goals table
        Schema::create('financial_goals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('target_amount', 15, 2);
            $table->decimal('current_amount', 15, 2)->default(0);
            $table->date('target_date');
            $table->string('priority')->default('medium'); // low, medium, high
            $table->boolean('is_achieved')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('financial_goals');
        Schema::dropIfExists('budgets');
        Schema::dropIfExists('categories');

        Schema::table('statement_transactions', function (Blueprint $table) {
            $table->dropIndex(['date', 'category']);
            $table->dropIndex(['bank_id', 'date']);
            $table->dropColumn(['category', 'subcategory', 'description', 'transaction_type']);
        });

        Schema::table('banks', function (Blueprint $table) {
            $table->dropColumn(['bank_type', 'account_number', 'status']);
        });
    }
};
