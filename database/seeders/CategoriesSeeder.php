<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            // Income Categories
            [
                'name' => 'Salary',
                'type' => 'income',
                'icon' => 'fas fa-money-bill-wave',
                'color' => '#11998e',
                'description' => 'Monthly salary and wages'
            ],
            [
                'name' => 'Freelancing',
                'type' => 'income',
                'icon' => 'fas fa-laptop-code',
                'color' => '#38ef7d',
                'description' => 'Freelance work income'
            ],
            [
                'name' => 'Investment Returns',
                'type' => 'income',
                'icon' => 'fas fa-chart-line',
                'color' => '#4facfe',
                'description' => 'Returns from investments'
            ],
            [
                'name' => 'Business Income',
                'type' => 'income',
                'icon' => 'fas fa-briefcase',
                'color' => '#667eea',
                'description' => 'Income from business activities'
            ],
            [
                'name' => 'Other Income',
                'type' => 'income',
                'icon' => 'fas fa-plus-circle',
                'color' => '#a8edea',
                'description' => 'Miscellaneous income sources'
            ],

            // Expense Categories
            [
                'name' => 'Food & Dining',
                'type' => 'expense',
                'icon' => 'fas fa-utensils',
                'color' => '#ff5f6d',
                'description' => 'Restaurant, groceries, and food expenses'
            ],
            [
                'name' => 'Transportation',
                'type' => 'expense',
                'icon' => 'fas fa-car',
                'color' => '#ffc371',
                'description' => 'Fuel, public transport, taxi expenses'
            ],
            [
                'name' => 'Shopping',
                'type' => 'expense',
                'icon' => 'fas fa-shopping-bag',
                'color' => '#764ba2',
                'description' => 'Clothing, electronics, and other purchases'
            ],
            [
                'name' => 'Healthcare',
                'type' => 'expense',
                'icon' => 'fas fa-heartbeat',
                'color' => '#f093fb',
                'description' => 'Medical expenses, medicines, insurance'
            ],
            [
                'name' => 'Utilities',
                'type' => 'expense',
                'icon' => 'fas fa-bolt',
                'color' => '#4facfe',
                'description' => 'Electricity, water, gas, internet bills'
            ],
            [
                'name' => 'Entertainment',
                'type' => 'expense',
                'icon' => 'fas fa-film',
                'color' => '#667eea',
                'description' => 'Movies, games, subscriptions'
            ],
            [
                'name' => 'Education',
                'type' => 'expense',
                'icon' => 'fas fa-graduation-cap',
                'color' => '#11998e',
                'description' => 'Courses, books, educational expenses'
            ],
            [
                'name' => 'Home & Garden',
                'type' => 'expense',
                'icon' => 'fas fa-home',
                'color' => '#38ef7d',
                'description' => 'Rent, maintenance, home improvements'
            ],
            [
                'name' => 'Travel',
                'type' => 'expense',
                'icon' => 'fas fa-plane',
                'color' => '#a8edea',
                'description' => 'Vacation, business travel expenses'
            ],
            [
                'name' => 'Personal Care',
                'type' => 'expense',
                'icon' => 'fas fa-spa',
                'color' => '#fed6e3',
                'description' => 'Salon, gym, personal care products'
            ],
            [
                'name' => 'Insurance',
                'type' => 'expense',
                'icon' => 'fas fa-shield-alt',
                'color' => '#ff9a8b',
                'description' => 'Life, health, vehicle insurance'
            ],
            [
                'name' => 'Taxes',
                'type' => 'expense',
                'icon' => 'fas fa-receipt',
                'color' => '#a18cd1',
                'description' => 'Income tax, property tax, other taxes'
            ],
            [
                'name' => 'Miscellaneous',
                'type' => 'expense',
                'icon' => 'fas fa-ellipsis-h',
                'color' => '#fbc2eb',
                'description' => 'Other uncategorized expenses'
            ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}

// Don't forget to add this seeder to DatabaseSeeder.php:
// $this->call(CategoriesSeeder::class);
