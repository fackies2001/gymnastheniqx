<?php

/*
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;
use App\Models\SerializedProduct;
use App\Models\ProductStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ReportDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
        $this->command->info('🚀 Starting Report Data Seeder...');

        // Step 1: Ensure we have basic dependencies
        $this->ensureBasicData();

        // Step 2: Create Product Statuses
        $this->createProductStatuses();

        // Step 3: Create Categories
        $categories = $this->createCategories();

        // Step 4: Create Suppliers
        $suppliers = $this->createSuppliers();

        // Step 5: Create Products & Supplier Products
        $products = $this->createProducts($categories, $suppliers);

        // Step 6: Create Purchase Requests (for daily report)
        $this->createPurchaseRequests($products, $suppliers);

        // Step 7: Create Purchase Orders with Serial Numbers
        $this->createPurchaseOrders($suppliers);

        // Step 8: Create Serialized Products (for daily counts)
        $this->createSerializedProducts($products);

        // Step 9: Create Retailer Orders (for sales data)
        $this->createRetailerOrders($products);

        // Step 10: Create Historical Sales (for weekly/monthly/yearly reports)
        $this->createHistoricalSales($products);

        $this->command->info('✅ Report Data Seeder completed successfully!');
        $this->command->newLine();
        $this->command->info('📊 You can now test:');
        $this->command->info('   - Daily Report: /daily-reports');
        $this->command->info('   - Weekly Report: /weekly-reports');
        $this->command->info('   - Monthly Report: /monthly-reports');
        $this->command->info('   - Strategic Report: /strategic-reports');
    }

    private function ensureBasicData()
    {
        $this->command->info('📝 Checking basic dependencies...');

        // Ensure we have at least one user
        if (User::count() === 0) {
            $this->command->warn('⚠️  No users found. Creating test user...');
            User::create([
                'name' => 'Test User',
                'email' => 'test@gymnastheniqx.com',
                'password' => bcrypt('password'),
                'role_id' => 1,
            ]);
            $this->command->info('   ✓ Test user created');
        } else {
            $this->command->info('   ✓ Found ' . User::count() . ' existing user(s)');
        }
    }

    private function createProductStatuses()
    {
        $this->command->info('📦 Creating Product Statuses...');

        $statuses = [
            ['id' => 1, 'name' => 'Available', 'description' => 'In stock and ready to sell'],
            ['id' => 2, 'name' => 'Reserved', 'description' => 'Reserved for customer'],
            ['id' => 3, 'name' => 'Sold', 'description' => 'Successfully sold'],
            ['id' => 4, 'name' => 'Damaged', 'description' => 'Product is damaged'],
            ['id' => 5, 'name' => 'Lost', 'description' => 'Product is lost'],
            ['id' => 6, 'name' => 'Returned', 'description' => 'Product returned by customer'],
            ['id' => 7, 'name' => 'Under Repair', 'description' => 'Product under repair'],
        ];

        foreach ($statuses as $status) {
            ProductStatus::updateOrCreate(['id' => $status['id']], $status);
        }
    }

    private function createCategories()
    {
        $this->command->info('🏷️  Creating Categories...');

        $categoryData = [
            ['name' => 'Dumbbells', 'description' => 'Various weight dumbbells'],
            ['name' => 'Barbells', 'description' => 'Olympic and standard barbells'],
            ['name' => 'Weight Plates', 'description' => 'Weight plates for barbells'],
            ['name' => 'Resistance Bands', 'description' => 'Elastic resistance bands'],
            ['name' => 'Kettlebells', 'description' => 'Cast iron kettlebells'],
            ['name' => 'Yoga Mats', 'description' => 'Exercise and yoga mats'],
            ['name' => 'Gym Accessories', 'description' => 'Gloves, straps, and more'],
        ];

        $categories = [];
        foreach ($categoryData as $data) {
            $categories[] = Category::firstOrCreate(['name' => $data['name']], $data);
        }

        return collect($categories);
    }

    private function createSuppliers()
    {
        $this->command->info('🚚 Creating Suppliers...');

        $supplierData = [
            ['name' => 'FitGear International', 'contact_person' => 'John Doe', 'email' => 'john@fitgear.com', 'phone' => '09171234567'],
            ['name' => 'ProStrength Corp', 'contact_person' => 'Jane Smith', 'email' => 'jane@prostrength.com', 'phone' => '09181234567'],
            ['name' => 'Muscle World Suppliers', 'contact_person' => 'Bob Wilson', 'email' => 'bob@muscleworld.com', 'phone' => '09191234567'],
            ['name' => 'Elite Fitness Equipment', 'contact_person' => 'Sarah Lee', 'email' => 'sarah@elitefitness.com', 'phone' => '09201234567'],
        ];

        $suppliers = [];
        foreach ($supplierData as $data) {
            $suppliers[] = Supplier::firstOrCreate(['email' => $data['email']], $data);
        }

        return collect($suppliers);
    }

    private function createProducts($categories, $suppliers)
    {
        $this->command->info('🏋️  Creating Supplier Products...');

        $productData = [
            // Dumbbells
            ['name' => 'Hex Dumbbell 5kg', 'category' => 'Dumbbells', 'cost' => 450, 'price' => 650, 'stock' => 25],
            ['name' => 'Hex Dumbbell 10kg', 'category' => 'Dumbbells', 'cost' => 850, 'price' => 1200, 'stock' => 20],
            ['name' => 'Adjustable Dumbbell Set', 'category' => 'Dumbbells', 'cost' => 2500, 'price' => 3500, 'stock' => 5],

            // Barbells
            ['name' => 'Olympic Barbell 20kg', 'category' => 'Barbells', 'cost' => 3000, 'price' => 4200, 'stock' => 8],
            ['name' => 'EZ Curl Bar', 'category' => 'Barbells', 'cost' => 1200, 'price' => 1800, 'stock' => 12],

            // Weight Plates
            ['name' => 'Bumper Plate 10kg', 'category' => 'Weight Plates', 'cost' => 1500, 'price' => 2100, 'stock' => 30],
            ['name' => 'Bumper Plate 20kg', 'category' => 'Weight Plates', 'cost' => 2800, 'price' => 3800, 'stock' => 25],
            ['name' => 'Iron Plate 5kg', 'category' => 'Weight Plates', 'cost' => 650, 'price' => 950, 'stock' => 40],

            // Resistance Bands
            ['name' => 'Resistance Band Set (Light-Heavy)', 'category' => 'Resistance Bands', 'cost' => 350, 'price' => 550, 'stock' => 50],
            ['name' => 'Loop Resistance Bands', 'category' => 'Resistance Bands', 'cost' => 200, 'price' => 350, 'stock' => 60],

            // Kettlebells
            ['name' => 'Kettlebell 8kg', 'category' => 'Kettlebells', 'cost' => 650, 'price' => 950, 'stock' => 18],
            ['name' => 'Kettlebell 16kg', 'category' => 'Kettlebells', 'cost' => 1200, 'price' => 1700, 'stock' => 15],

            // Yoga Mats
            ['name' => 'Premium Yoga Mat 6mm', 'category' => 'Yoga Mats', 'cost' => 450, 'price' => 750, 'stock' => 35],
            ['name' => 'Travel Yoga Mat 3mm', 'category' => 'Yoga Mats', 'cost' => 300, 'price' => 500, 'stock' => 28],

            // Accessories
            ['name' => 'Gym Gloves Pro', 'category' => 'Gym Accessories', 'cost' => 250, 'price' => 450, 'stock' => 45],
            ['name' => 'Lifting Straps', 'category' => 'Gym Accessories', 'cost' => 150, 'price' => 300, 'stock' => 55],
            ['name' => 'Wrist Wraps Set', 'category' => 'Gym Accessories', 'cost' => 200, 'price' => 380, 'stock' => 48],

            // Low stock items (for alerts)
            ['name' => 'Competition Barbell 20kg', 'category' => 'Barbells', 'cost' => 8000, 'price' => 12000, 'stock' => 2],
            ['name' => 'Kettlebell 32kg', 'category' => 'Kettlebells', 'cost' => 3500, 'price' => 5000, 'stock' => 1],
        ];

        $products = [];

        foreach ($productData as $data) {
            $category = $categories->firstWhere('name', $data['category']);
            if (!$category) {
                $this->command->warn("⚠️  Category '{$data['category']}' not found, skipping {$data['name']}");
                continue;
            }

            $supplier = $suppliers->random();

            // Create ONLY SupplierProduct (no Product table dependency)
            $supplierProduct = SupplierProduct::firstOrCreate(
                ['name' => $data['name']],
                [
                    'supplier_id' => $supplier->id,
                    'category_id' => $category->id,
                    'supplier_sku' => 'SKU-' . rand(100, 999),
                    'system_sku' => 'GYM-' . strtoupper(Str::random(8)),
                    'cost_price' => $data['cost'],
                    'stock' => $data['stock'],
                    'is_consumable' => 0, // Gym equipment is not consumable
                    'barcode' => '88' . str_pad(rand(0, 999999999999), 12, '0', STR_PAD_LEFT),
                    'images' => '[]',
                    'dimensions' => json_encode(['depth' => null, 'width' => null, 'height' => null, 'weight' => null]),
                ]
            );

            $products[] = $supplierProduct;
        }

        $this->command->info('   ✓ Created ' . count($products) . ' supplier products');
        return collect($products);
    }

    private function createPurchaseRequests($products, $suppliers)
    {
        $this->command->info('📋 Creating Purchase Requests (for Daily Report)...');

        $user = User::first();
        $today = Carbon::today();

        // Create 3 purchase requests for today
        for ($i = 1; $i <= 3; $i++) {
            $supplier = $suppliers->random();
            $selectedProducts = $products->random(rand(2, 4));

            $totalAmount = $selectedProducts->sum(function ($product) {
                return $product->cost_price * rand(5, 15);
            });

            $pr = PurchaseRequest::create([
                'request_number' => 'PR-' . $today->format('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT) . '-' . time(),
                'user_id' => $user->id,
                'department_id' => 1, // Default department - adjust if needed
                'supplier_id' => $supplier->id,
                'status_id' => 1, // Pending
                'order_date' => $today,
                'remarks' => 'Restock request for popular items',
                'created_at' => $today->copy()->addHours(rand(8, 17)),
            ]);

            // Attach products to PR (adjust based on your actual PR-Product relationship)
            // This assumes you have a pr_products relationship or similar
            foreach ($selectedProducts as $product) {
                // You may need to adjust this based on your actual schema
                // This is a placeholder - adapt to your actual relationship
            }
        }
    }

    private function createPurchaseOrders($suppliers)
    {
        $this->command->info('🛒 Creating Purchase Orders (for Daily Report)...');

        $user = User::first();
        $today = Carbon::today();

        // Get recent purchase requests to link
        $recentPRs = PurchaseRequest::latest()->take(2)->get();

        // Create 2 POs for today
        for ($i = 1; $i <= 2; $i++) {
            $supplier = $suppliers->random();
            $pr = $recentPRs->get($i - 1); // Use the PRs we just created

            PurchaseOrder::create([
                'po_number' => 'PO-' . $today->format('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT) . '-' . time(),
                'purchase_request_id' => $pr ? $pr->id : null,
                'supplier_id' => $supplier->id,
                'approved_by' => $user->id,
                'order_date' => $today,
                'delivery_date' => $today->copy()->addDays(7),
                'payment_terms' => 'cash_on_delivery',
                'status' => 'pending_scan',
                'grand_total' => rand(10000, 50000),
                'created_at' => $today->copy()->addHours(rand(9, 16)),
            ]);
        }

        $this->command->info('   ✓ Created 2 purchase orders for today');
    }

    private function createSerializedProducts($products)
    {
        $this->command->info('🔢 Creating Serialized Products (for Stock Counts)...');

        $today = Carbon::today();
        $user = User::first();

        // Status distribution for today's report
        $statusDistribution = [
            'in_inventory' => 15,  // Available items (15 new arrivals)
            'sold' => 8,           // Sold today (Daily Outflow)
            'pending' => 5,        // Pending/damaged/returned
        ];

        foreach ($statusDistribution as $status => $count) {
            for ($i = 0; $i < $count; $i++) {
                $product = $products->random();

                $createdAt = $status == 'in_inventory'
                    ? $today->copy()->addHours(rand(8, 17))  // New arrivals today
                    : $today->copy()->subDays(rand(1, 30));  // Older items

                $updatedAt = in_array($status, ['sold', 'pending'])
                    ? $today->copy()->addHours(rand(10, 18))  // Status changed today
                    : $createdAt;

                SerializedProduct::create([
                    'serial_number' => 'SN-' . strtoupper(Str::random(12)),
                    'barcode' => '88' . str_pad(rand(0, 99999999999), 11, '0', STR_PAD_LEFT), // 13 digits total
                    'product_id' => $product->id,
                    'status' => $status,
                    'purchase_order_id' => null,
                    'scanned_at' => $updatedAt,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ]);
            }
        }

        // Add some older serialized products for stock counts
        for ($i = 0; $i < 50; $i++) {
            $product = $products->random();

            SerializedProduct::create([
                'serial_number' => 'SN-' . strtoupper(Str::random(12)),
                'barcode' => '88' . str_pad(rand(0, 99999999999), 11, '0', STR_PAD_LEFT), // 13 digits total
                'product_id' => $product->id,
                'status' => 'in_inventory', // Available
                'purchase_order_id' => null,
                'scanned_at' => $today->copy()->subDays(rand(2, 60)),
                'created_at' => $today->copy()->subDays(rand(2, 60)),
            ]);
        }
    }

    private function createRetailerOrders($products)
    {
        $this->command->info('💰 Creating Retailer Orders (for Sales Reports)...');

        $today = Carbon::today();
        $user = User::first();

        // Create sales for today (for daily report)
        for ($i = 0; $i < 10; $i++) {
            $product = $products->random();
            $quantity = rand(1, 5);
            $unitPrice = $product->cost_price * 1.5; // Add 50% margin

            DB::table('retailer_orders')->insert([
                'retailer_name' => 'Sample Retailer ' . ($i + 1),
                'sku' => $product->system_sku,
                'product_name' => $product->name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_amount' => $unitPrice * $quantity,
                'status' => 'Approved',
                'created_by' => $user->id,
                'approved_by' => $user->id,
                'approved_at' => $today->copy()->addHours(rand(10, 19)),
                'created_at' => $today->copy()->addHours(rand(10, 19)),
                'updated_at' => $today->copy()->addHours(rand(10, 19)),
            ]);
        }

        $this->command->info('   ✓ Created ' . DB::table('retailer_orders')->whereDate('created_at', $today)->count() . ' retailer orders for today');
    }

    private function createHistoricalSales($products)
    {
        $this->command->info('📈 Creating Historical Sales Data...');

        $user = User::first();

        // Create sales for the past 12 months
        for ($monthsAgo = 0; $monthsAgo < 12; $monthsAgo++) {
            $targetDate = Carbon::now()->subMonths($monthsAgo);
            $salesCount = rand(30, 80);

            for ($i = 0; $i < $salesCount; $i++) {
                $product = $products->random();
                $quantity = rand(1, 8);
                $unitPrice = $product->cost_price * 1.5; // 50% margin
                $saleDate = $targetDate->copy()->addDays(rand(0, 27))->addHours(rand(9, 20));

                DB::table('retailer_orders')->insert([
                    'retailer_name' => 'Retailer ' . chr(65 + rand(0, 25)), // A-Z
                    'sku' => $product->system_sku,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_amount' => $unitPrice * $quantity,
                    'status' => 'Approved',
                    'created_by' => $user->id,
                    'approved_by' => $user->id,
                    'approved_at' => $saleDate,
                    'created_at' => $saleDate,
                    'updated_at' => $saleDate,
                ]);
            }
        }

        // Create historical purchase orders for cost tracking
        $this->command->info('📦 Creating Historical Purchase Orders...');

        for ($monthsAgo = 0; $monthsAgo < 12; $monthsAgo++) {
            $targetDate = Carbon::now()->subMonths($monthsAgo);
            $poCount = rand(5, 15);

            for ($i = 0; $i < $poCount; $i++) {
                $supplier = Supplier::inRandomOrder()->first();
                $orderDate = $targetDate->copy()->addDays(rand(0, 27));

                $pr = PurchaseRequest::create([
                    'request_number' => 'PR-' . $orderDate->format('Ymd') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT) . '-' . $monthsAgo . $i,
                    'user_id' => User::first()->id,
                    'department_id' => 1,
                    'supplier_id' => $supplier->id,
                    'status_id' => 3, // Approved
                    'order_date' => $orderDate->copy()->subDays(3),
                    'created_at' => $orderDate->copy()->subDays(3),
                ]);

                PurchaseOrder::create([
                    'po_number' => 'PO-' . $orderDate->format('Ymd') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT) . '-' . $monthsAgo . $i,
                    'purchase_request_id' => $pr->id,
                    'supplier_id' => $supplier->id,
                    'approved_by' => User::first()->id,
                    'order_date' => $orderDate,
                    'delivery_date' => $orderDate->copy()->addDays(7),
                    'payment_terms' => 'cash_on_delivery',
                    'status' => 'pending_scan',
                    'grand_total' => rand(10000, 50000),
                    'created_at' => $orderDate,
                ]);
            }
        }

        $salesTotal = DB::table('retailer_orders')->where('status', 'Approved')->count();
        $this->command->info('   ✓ Created ' . $salesTotal . ' total sales records');
        $this->command->info('   ✓ Created ' . PurchaseOrder::count() . ' total purchase orders');
    }
}

25-01-2026