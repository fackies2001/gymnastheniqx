<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Laravel\Pennant\Feature;
use App\Models\User;

class FeatureServiceProvider extends ServiceProvider
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
    public function boot(): void
    {

        Feature::define(
            feature: 'is_api',
            resolver: fn(User $user): bool => $user->id === 1
        );

        // @is_api
        Blade::if('is_api', function () {
            return Feature::active('is_api');
        });

        // @not_api
        Blade::if('not_api', function () {
            return ! Feature::active('is_api');
        });

        Feature::define(
            feature: 'is_premium',
            resolver: fn(User $user): bool => $user->id === 1
        );


        Blade::if(
            name: 'premium',
            callback: fn(): bool => Feature::for(scope: auth()->user())
                ->active(feature: 'is_premium')
        );
    }
}
