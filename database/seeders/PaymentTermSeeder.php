<?php

namespace Database\Seeders;

use App\Models\PaymentTerm;
use Illuminate\Database\Seeder;

class PaymentTermSeeder extends Seeder
{
    public function run(): void
    {
        $term = [
            [
                'name' => 'Net 15',
                'description' => 'Payment due in 15 days',
                'days' => 15,
                'discount_rate' => null,
                'discount_days' => null,
            ],
            [
                'name' => 'Net 30',
                'description' => 'Payment due in 30 days',
                'days' => 30,
                'discount_rate' => null,
                'discount_days' => null,
            ],
            [
                'name' => 'Cash on Delivery',
                'description' => 'Payment due immediately upon receipt',
                'days' => 0,
                'discount_rate' => null,
                'discount_days' => null,
            ],
            [
                'name' => '2/10 Net 30',
                'description' => 'Payment due in 30 days with 2% discount if paid within 10 days',
                'days' => 30,
                'discount_rate' => 2.00,
                'discount_days' => 10,
            ],
        ];

        foreach ($term as $term) {
            PaymentTerm::updateOrCreate(
                ['name' => $term['name']], // unique key
                $term
            );
        }
    }
}
