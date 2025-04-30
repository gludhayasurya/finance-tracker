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
                        'icon' => 'fas fa-fw fa-landmark',
                        'icon_color' => 'info',
                        'submenu' => [
                            [
                                'text' => 'Transactions',
                                'url' => route('transactions.index', ['bank_id' => $bank->id]),
                            ],
                            [
                                'text' => 'Reports',
                                'url' => '#', // Placeholder for reports URL
                            ],
                        ],
                    ]);
                }
            });
    }
}
