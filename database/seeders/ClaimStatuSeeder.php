<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClaimStatu;
use Ramsey\Uuid\Uuid;

class ClaimStatuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $claimStatuses = [
            ['name' => 'New', 'color' => '#4CAF50'],
            ['name' => 'Initial Review', 'color' => '#2196F3'],
            ['name' => 'Additional Information Required', 'color' => '#FFC107'],
            ['name' => 'Awaiting Documentation', 'color' => '#FF9800'],
            ['name' => 'Under Investigation', 'color' => '#9C27B0'],
            ['name' => 'Medical Evaluation', 'color' => '#00BCD4'],
            ['name' => 'In Negotiation', 'color' => '#795548'],
            ['name' => 'Partially Approved', 'color' => '#8BC34A'],
            ['name' => 'Approved', 'color' => '#4CAF50'],
            ['name' => 'Payment Processing', 'color' => '#009688'],
            ['name' => 'Paid', 'color' => '#3F51B5'],
            ['name' => 'Rejected', 'color' => '#F44336'],
            ['name' => 'Under Appeal', 'color' => '#FF5722'],
            ['name' => 'Closed', 'color' => '#607D8B'],
            ['name' => 'Reopened', 'color' => '#E91E63'],
            ['name' => 'In Litigation', 'color' => '#9E9E9E'],
            ['name' => 'Waiting for Third Party', 'color' => '#CDDC39'],
            ['name' => 'Cancelled', 'color' => '#FF5252'],
            ['name' => 'Duplicate', 'color' => '#7C4DFF'],
            ['name' => 'Suspended', 'color' => '#DC2626'],
        ];

        foreach ($claimStatuses as $status) {
            ClaimStatu::create([
                'uuid' => Uuid::uuid4()->toString(),
                'claim_status_name' => $status['name'],
                'background_color' => $status['color'],
            ]);
        }
    }
} 