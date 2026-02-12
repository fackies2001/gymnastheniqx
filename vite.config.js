import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js', 
                'resources/css/adminlte.css',
                'resources/js/dashboard.js',
                'resources/js/purchase-request/purchase-request.js',
                'resources/js/purchase-order/purchase-order.js',
                'resources/js/barcode-scanner/barcode-scanner.js',
                'resources/js/pincode_LOCKED.js',
                'resources/js/reports.js'
            ],  
            refresh: true,
        }),
    ],
    // ✅ ADD THIS PART - jQuery Alias Configuration
    resolve: {
        alias: {
            '$': 'jquery',              // ← Para sa $
            'jQuery': 'jquery',         // ← ADD THIS LINE (capital J, capital Q)
            'jquery': 'jquery',         // ← Already exists
        },
    },
});