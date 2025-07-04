<?php

namespace App\Providers;

use App\Models\Bank;
use Illuminate\Support\ServiceProvider;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {


        if (app()->environment('production')) {
                URL::forceScheme('https');
            }

        // View::composer('*', function ($view) {
        //     $view->with('sidebarBanks', Bank::all());
        // });

        // Event::listen(BuildingMenu::class, function (BuildingMenu $event) {
        //     $banks = Bank::all();


        //     foreach ($banks as $bank) {
        //         $event->menu->add([
        //             'text' => $bank->name,
        //             'icon' => 'fas fa-fw fa-landmark',
        //             'icon_color' => 'info',
        //             'submenu' => [
        //                 [
        //                     'text' => 'Transactions',
        //                     'url' => route('transactions.index', ['bank_id' => $bank->id]),
        //                 ],
        //                 [
        //                     'text' => 'Reportsmmmm',
        //                     // 'url' => route('banks.reports', ['bank' => $bank->id]),
        //                 ],
        //             ],
        //         ]);
        //     }
        // });
    }
}
