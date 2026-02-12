<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
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



        Gate::define(
            'can-manage-users-deletion',
            fn(User $user) =>
            $user->employee?->role?->getAttribute('id') === 1
        );
        Gate::define(
            'can-manage-request-status',
            fn(User $user) =>
            in_array($user->employee?->role?->getAttribute('id'), [1, 3, 4])
        );

        Gate::define('can-create-supplier', fn(User $user) => in_array($user->employee?->role?->getAttribute('id'), [1, 4]));
        Gate::define('can-create-supplier-product', fn(User $user) => in_array($user->employee?->role?->getAttribute('id'), [1, 2, 3, 4]));

        Gate::define('can-see-purchase-requests', fn(User $user) => in_array($user->employee?->role?->getAttribute('id'), [1, 2, 3, 4]));
        Gate::define('can-see-purchase-orders', fn(User $user) => in_array($user->employee?->role?->getAttribute('id'), [1, 2, 3, 4]));

        Gate::define('can-see-warehouse-menu', fn(User $user) => in_array($user->employee?->role?->getAttribute('id'), [1, 4]));
        Gate::define('can-see-reports-menu', fn(User $user) => in_array($user->employee?->role?->getAttribute('id'), [1, 4]));

        Gate::define('can-review-purchase-orders', fn(User $user) => in_array($user->employee?->role?->getAttribute('id'), [1, 3, 4]));

        /**
         * PROFESSIONAL CAPABILITY
         */
        Gate::define('can-create-supplier-api', fn(User $user) => !$user->is_student);
        Gate::define('can-see-suppliers-api', fn(User $user) => !$user->is_student);
        Gate::define('can-see-stocks', fn(User $user) => !$user->is_student);
        Gate::define('can-see-discounts', fn(User $user) => !$user->is_student);
        Gate::define('can-see-images', fn(User $user) => !$user->is_student);
        Gate::define('can-render-original-supplier-products-data', fn(User $user) => !$user->is_student);
        Gate::define('can-render-original-data', fn(User $user) => !$user->is_student);
        Gate::define('can-render-original-data-table', fn(User $user) => !$user->is_student);
        Gate::define('can-store-original-supplier-product', fn(User $user) => !$user->is_student);
        Gate::define('can-store-original-purchase-order', fn(User $user) => !$user->is_student);
        Gate::define('can-store-original-purchase-request', fn(User $user) => !$user->is_student);
        Gate::define('can-store-original-serial-number', fn(User $user) => !$user->is_student);


        /**
         * STUDENT CAPABILITY
         */
        Gate::define('can-create-supplier-product', fn(User $user) => $user->is_student);
        Gate::define('can-create-category', fn(User $user) => $user->is_student);
    }
}
