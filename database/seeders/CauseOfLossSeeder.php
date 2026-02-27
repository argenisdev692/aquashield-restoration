<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CauseOfLoss;
use Ramsey\Uuid\Uuid;

class CauseOfLossSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $causeOfLosses = [
            'Hail',
            'Wind',
            'Hail & Wind',
            'Hurricane Wind',
            'Hurricane Flood',
            'Flood',
            'Fire',
            'Smoke',
            'Fire & Smoke',
            'Fallen Tree',
            'Lightning',
            'Tornado',
            'Vandalism',
            'Marine',
            'Water',
            'Other'
        ];

        foreach ($causeOfLosses as $cause) {
            CauseOfLoss::create([
                'uuid' => Uuid::uuid4()->toString(),
                'cause_loss_name' => $cause,
                'description' => 'Descripción de ' . $cause,
                'severity' => 'low',
            ]);
        }
    }
} 