<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Milon\Barcode\Facades\DNS1DFacade;
use Milon\Barcode\Facades\DNS2DFacade;

class BarcodeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register barcode facades manually
        // class_alias(DNS1DFacade::class, 'DNS1D');
        // class_alias(DNS2DFacade::class, 'DNS2D');
    }

    public function boot(): void
    {
        //
    }
}
