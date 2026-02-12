<?php

namespace Database\Seeders;

use App\Models\ProductStatus;
use Illuminate\Database\Seeder;

class ProductStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['id' => 1, 'name' => 'Available', 'color' => '#28a745', 'description' => 'Product ready for allocation'],
            ['id' => 2, 'name' => 'Reserved', 'color' => '#ffc107', 'description' => 'Product reserved for retailer order'],
            ['id' => 3, 'name' => 'Sold', 'color' => '#6c757d', 'description' => 'Product sold to retailer'],
            ['id' => 4, 'name' => 'Damaged', 'color' => '#dc3545', 'description' => 'Product is defective'],
            ['id' => 5, 'name' => 'Lost', 'color' => '#B22222', 'description' => 'Product is missing'],
            ['id' => 6, 'name' => 'Released', 'color' => '#13DAE9', 'description' => 'Product has been released/issued'], // ✅ FIXED!
            ['id' => 7, 'name' => 'Under Repair', 'color' => '#F7CF1D', 'description' => 'Product is under repair'],
        ];

        foreach ($statuses as $status) {
            ProductStatus::updateOrInsert(
                ['id' => $status['id']],
                array_merge($status, ['updated_at' => now()])
            );
        }
    }
}
