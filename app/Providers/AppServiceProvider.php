<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\SerializedProduct;
use App\Models\SupplierProduct;
use Carbon\Carbon;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if (env('APP_ENV') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            if (auth()->check()) {
                $today = \Carbon\Carbon::today()->toDateString();

                $lowStockCount = \App\Models\SupplierProduct::where('stock', '<=', 5)->count();
                $newArrivals = \App\Models\SerializedProduct::whereDate('created_at', $today)->count();

                $view->with([
                    'lowStockCount' => $lowStockCount,
                    'newArrivals'   => $newArrivals,
                ]);
            }
        });

        Event::listen(BuildingMenu::class, function (BuildingMenu $event) {
            $user = auth()->user();

            $warehouseName = $user?->adminlte_warehouse() ?? 'No Warehouse';
            $roleName = $user?->employee?->role?->role_name ?? 'No Role';

            $event->menu->addBefore('inventory', [
                'key' => 'account_settings',
                'header' => strtoupper($roleName) . ' - ' . strtoupper($warehouseName),
            ]);

            $event->menu->addBefore('inventory', [
                'text' => strtoupper($roleName),
                'url' => '#',
                'icon' => false,
                'right_badge' => ['text' => strtoupper($roleName), 'type' => 'success'],
                'classes' => 'text-uppercase small ml-2 font-weight-bold p-0',
                'key' => 'role-menu-item'
            ]);

            $event->menu->addBefore('inventory', [
                'text' => strtoupper($warehouseName),
                'url' => '#',
                'icon' => false,
                'right_badge' => ['text' => strtoupper($warehouseName), 'type' => 'maroon'],
                'classes' => 'text-uppercase small ml-2 font-weight-bold p-0',
                'key' => 'warehouse-menu-item'
            ]);
        });
    }
}
