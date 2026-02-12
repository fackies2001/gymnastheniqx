<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

/**
 * Verification Seeder - Run this to check if your database is ready for ReportDataSeeder
 * 
 * Usage: php artisan db:seed --class=VerifyReportTablesSeeder
 */
class VerifyReportTablesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('🔍 GYMNASTHENIQX REPORT DATABASE VERIFICATION');
        $this->command->line('================================================');
        $this->command->info('');

        $requiredTables = [
            'user' => ['id', 'name', 'email'],
            'category' => ['id', 'name'],
            'supplier' => ['id', 'name', 'email'],
            'supplier_product' => ['id', 'name', 'supplier_id', 'category_id', 'system_sku', 'cost_price', 'stock'],
            'product_status' => ['id', 'name'],
            'purchase_request' => ['id', 'request_number', 'requested_by', 'supplier_id', 'total_amount'],
            'purchase_order' => ['id', 'po_number', 'supplier_id', 'order_date'],
            'serialized_product' => ['id', 'serial_number', 'supplier_product_id', 'status'],
            'retailer_order' => ['id', 'product_name', 'quantity', 'total_amount', 'status'],
        ];

        $allPassed = true;

        foreach ($requiredTables as $table => $requiredColumns) {
            if (!Schema::hasTable($table)) {
                $this->command->error("❌ Table '$table' NOT FOUND");
                $allPassed = false;
                continue;
            }

            $actualColumns = Schema::getColumnListing($table);
            $missingColumns = array_diff($requiredColumns, $actualColumns);

            if (empty($missingColumns)) {
                $this->command->info("✅ Table '$table' - OK");
            } else {
                $this->command->warn("⚠️  Table '$table' - MISSING COLUMNS: " . implode(', ', $missingColumns));
                $this->command->line("   Actual columns: " . implode(', ', $actualColumns));
                $allPassed = false;
            }
        }

        $this->command->info('');
        $this->command->line('================================================');

        if ($allPassed) {
            $this->command->info('✅ ALL TABLES VERIFIED - Ready to run ReportDataSeeder!');
            $this->command->info('');
            $this->command->info('Next step:');
            $this->command->line('   php artisan db:seed --class=ReportDataSeeder');
        } else {
            $this->command->error('❌ VERIFICATION FAILED - Please fix missing tables/columns first');
            $this->command->info('');
            $this->command->info('Common fixes:');
            $this->command->line('   1. Run pending migrations: php artisan migrate');
            $this->command->line('   2. Create missing migrations for tables marked as NOT FOUND');
            $this->command->line('   3. Add missing columns to existing tables');
        }

        $this->command->info('');
        $this->command->line('================================================');
        $this->command->info('📊 CURRENT DATA COUNTS:');
        $this->command->line('================================================');

        $counts = [
            'user' => DB::table('user')->count(),
            'category' => Schema::hasTable('category') ? DB::table('category')->count() : 0,
            'supplier' => Schema::hasTable('supplier') ? DB::table('supplier')->count() : 0,
            'supplier_product' => Schema::hasTable('supplier_product') ? DB::table('supplier_product')->count() : 0,
            'purchase_request' => Schema::hasTable('purchase_request') ? DB::table('purchase_request')->count() : 0,
            'purchase_order' => Schema::hasTable('purchase_order') ? DB::table('purchase_order')->count() : 0,
            'serialized_product' => Schema::hasTable('serialized_product') ? DB::table('serialized_product')->count() : 0,
            'retailer_order' => Schema::hasTable('retailer_order') ? DB::table('retailer_order')->count() : 0,
        ];

        foreach ($counts as $table => $count) {
            $this->command->line(sprintf("   %-25s: %d records", ucfirst(str_replace('_', ' ', $table)), $count));
        }

        $this->command->info('');

        // Check today's data (for Daily Report)
        if (Schema::hasTable('serialized_product')) {
            $todaySerials = DB::table('serialized_product')
                ->whereDate('created_at', Carbon::today())
                ->count();
            $this->command->line("   Serialized products (today): $todaySerials");
        }

        if (Schema::hasTable('retailer_order')) {
            $todaySales = DB::table('retailer_order')
                ->whereDate('created_at', Carbon::today())
                ->where('status', 'Approved')
                ->count();
            $this->command->line("   Retailer orders (today):     $todaySales");
        }

        $this->command->info('');
        $this->command->line('================================================');

        if ($allPassed && $counts['supplier_products'] == 0) {
            $this->command->warn('⚠️  No data found. Seeder will create sample data.');
        } elseif ($allPassed && $counts['supplier_products'] > 0) {
            $this->command->info('ℹ️  Existing data found. Seeder will ADD to existing data.');
            $this->command->warn('   If you want fresh data, run: php artisan migrate:fresh --seed');
        }

        $this->command->info('');
    }
}
