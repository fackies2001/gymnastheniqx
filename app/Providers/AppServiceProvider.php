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
        // Gamitin ang View facade (Capital V)
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            if (auth()->check()) {
                $today = \Carbon\Carbon::today()->toDateString();

                // Siguraduhin na singular ang Models (walang "s")
                $lowStockCount = \App\Models\SupplierProduct::where('stock', '<=', 5)->count();
                $newArrivals = \App\Models\SerializedProduct::whereDate('created_at', $today)->count();

                // I-pass ang data sa view gamit ang lowercase $view variable
                $view->with([
                    'lowStockCount' => $lowStockCount,
                    'newArrivals'   => $newArrivals,
                    // ... iba pang data
                ]);
            }
        });; // <-- Dito dapat nagtatapos ang View Composer

        // --- PART 2: ADMINLTE SIDEBAR MENU LOGIC ---
        // Ginamitan nat sa Event::listen para sigurado
        Event::listen(BuildingMenu::class, function (BuildingMenu $event) {
            $user = auth()->user();

            // Gumamit ng optional o null coalescing para iwas 500 error kung null ang relations
            $warehouseName = $user?->adminlte_warehouse() ?? 'No Warehouse';
            $roleName = $user?->employee?->role?->role_name ?? 'No Role';

            // Add header
            $event->menu->addBefore('inventory', [
                'key' => 'account_settings',
                'header' => strtoupper($roleName) . ' - ' . strtoupper($warehouseName),
            ]);

            // Add role badge
            $event->menu->addBefore('inventory', [
                'text' => strtoupper($roleName),
                'url' => '#',
                'icon' => false,
                'right_badge' => ['text' => strtoupper($roleName), 'type' => 'success'],
                'classes' => 'text-uppercase small ml-2 font-weight-bold p-0',
                'key' => 'role-menu-item'
            ]);

            // Add warehouse badge
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
