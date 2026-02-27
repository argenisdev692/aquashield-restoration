<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceCategory;
use Illuminate\Support\Str;

class ServiceCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $serviceCategories = [
            'Roof Repair',
            'New Roof',
            'Storm Damage',
            'Mold Remediation',
            'Mitigation',
            'Tarp',
            'ReTarp',
            'Rebuild',
            'Roof Paint',
            'Tarp Inspection',
            'Repair',
            'Mitigation & Tarp',
            'Roofing & Rebuild',
            'Other'
        ];

        foreach ($serviceCategories as $category) {
            ServiceCategory::create([
                'uuid' => (string) Str::uuid(),
                'category' => $category,
                'user_id' => 1,
            ]);
        }
    }
} 