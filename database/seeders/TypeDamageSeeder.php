<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TypeDamage;
use Ramsey\Uuid\Uuid;

class TypeDamageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $typeDamages = [
            'Kitchen',
            'Bathroom',
            'AC',
            'Heater',
            'Mold',
            'Roof Leak',
            'Flood',
            'Broke Pipe',
            'Internal Pipe',
            'Water Heater',
            'Roof',
            'Overflow',
            'Windstorm',
            'Water Leak',
            'Unknown',
            'Fire Damage',
            'Wind Damage',
            'Hurricane',
            'Water Damage',
            'Slab Leak',
            'TARP',
            'Hail Storm',
            'Shrink Wrap Roof',
            'Invoice',
            'Retarp',
            'Mold Testing',
            'Post-Hurricane',
            'Mitigation',
            'Mold Testing Clearance',
            'Rebuild',
            'Mold Remediation',
            'Plumbing',
            'Post-Storm',
            'Other',
        ];

        foreach ($typeDamages as $damage) {
            TypeDamage::create([
                'uuid' => Uuid::uuid4()->toString(),
                'type_damage_name' => $damage,
                'description' => 'Descripción de ' . $damage,
                'severity' => 'low',
            ]);
        }
    }
} 