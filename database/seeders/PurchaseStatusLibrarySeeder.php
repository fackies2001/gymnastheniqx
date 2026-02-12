<?php

// database/seeders/PurchaseStatusLibrarySeeder.php
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
                'color' => '#F59E0B', // amber-500
                'description' => 'Awaiting review or approval.'
            ],
            [
                'name' => 'Reviewed',
                'code' => 'REVIEWED',
                'color' => '#3B82F6', // blue-500
                'description' => 'Reviewed but not yet approved.'
            ],
            [
                'name' => 'Approved',
                'code' => 'APPROVED',
                'color' => '#10B981', // green-500
                'description' => 'Approved and ready for processing.'
            ],
            [
                'name' => 'Partially Ordered',
                'code' => 'PARTIAL_ORDER',
                'color' => '#6366F1', // indigo-500
                'description' => 'Part of the request has been ordered.'
            ],
            [
                'name' => 'Ordered',
                'code' => 'ORDERED',
                'color' => '#2563EB', // blue-600
                'description' => 'All items have been ordered.'
            ],
            [
                'name' => 'Received',
                'code' => 'RECEIVED',
                'color' => '#059669', // green-600
                'description' => 'All ordered items have been received.'
            ],
            [
                'name' => 'Cancelled',
                'code' => 'CANCELLED',
                'color' => '#6B7280', // gray-500
                'description' => 'Request has been cancelled.'
            ],
            [
                'name' => 'Rejected',
                'code' => 'REJECTED',
                'color' => '#DC2626', // red-600
                'description' => 'Request was rejected by approver.'
            ],
            [
                'name' => 'Completed',
                'code' => 'COMPLETED',
                'color' => '#DC2626', // red-600
                'description' => 'Purchase complete.'
            ],
        ];


        foreach ($statuses as $status) {
            PurchaseStatusLibrary::updateOrCreate(
                ['code' => $status['code']],
                $status
            );
        }
    }
}
