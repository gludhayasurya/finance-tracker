<?php

namespace App\Providers;

use App\Models\Bank;
use Illuminate\Support\ServiceProvider;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;

class DynamicBankMenuProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        View::composer('*', function ($view) {
            $view->with('sidebarBanks', Bank::all());
        });

        Event::listen(BuildingMenu::class, function (BuildingMenu $event) {
            $banks = Bank::all();


            foreach ($banks as $bank) {
                $event->menu->add([
                    'text' => strtoupper($bank->name) . ' Bank',
                    'icon' => $bank->fa_icon ?? 'fas fa-fw fa-landmark', // Use dynamic fa_icon or fallback
                    'icon_color' => $bank->icon_color ?? 'info', // Use dynamic icon_color or fallback
                    'submenu' => [
                        [
                            'text' => ' Manual Entry Transactions',
                            'url' => route('transactions.index', ['bank_id' => $bank->id]),
                        ],
                        [
                            'text' => 'Upload Statement',
                            'url' => route('bank.upload.form', ['bank_id' => $bank->id]),
                        ],
                        [
                            'text' => 'Import Transactions',
                            'url' => route('bank.parse.store', ['bank_id' => $bank->id]),
                        ],
                        [
                            'text' => 'View Statement',
                            'url' => route('statements.index', ['bank_id' => $bank->id]),
                        ],
                    ],
                ]);
            }
        });
    }
}
