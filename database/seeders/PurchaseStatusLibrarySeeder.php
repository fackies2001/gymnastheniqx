<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PurchaseStatusLibrary;

class PurchaseStatusLibrarySeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'Pending',
                'code' => 'PENDING',
                'color' => '#F59E0B',
                'description' => 'Awaiting review or approval.'
            ],
            [
                'name' => 'Reviewed',
                'code' => 'REVIEWED',
                'color' => '#3B82F6',
                'description' => 'Reviewed but not yet approved.'
            ],
            [
                'name' => 'Approved',
                'code' => 'APPROVED',
                'color' => '#10B981',
                'description' => 'Approved and ready for processing.'
            ],
            [
                'name' => 'Partially Ordered',
                'code' => 'PARTIAL_ORDER',
                'color' => '#6366F1',
                'description' => 'Part of the request has been ordered.'
            ],
            [
                'name' => 'Ordered',
                'code' => 'ORDERED',
                'color' => '#2563EB',
                'description' => 'All items have been ordered.'
            ],
            [
                'name' => 'Received',
                'code' => 'RECEIVED',
                'color' => '#059669',
                'description' => 'All ordered items have been received.'
            ],
            [
                'name' => 'Cancelled',
                'code' => 'CANCELLED',
                'color' => '#6B7280',
                'description' => 'Request has been cancelled.'
            ],
            [
                'name' => 'Rejected',
                'code' => 'REJECTED',
                'color' => '#DC2626',
                'description' => 'Request was rejected by approver.'
            ],
            [
                'name' => 'Completed',
                'code' => 'COMPLETED',
                'color' => '#16A34A',
                'description' => 'Purchase complete.'
            ],
        ];

        foreach ($statuses as $status) {
            PurchaseStatusLibrary::updateOrCreate(
                ['code' => $status['code']],
                $status
            );
        }

        echo "\n✅ Purchase Status Library seeded successfully!\n";
    }
}
