<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;
use Src\Modules\TypeDamages\Infrastructure\Persistence\Eloquent\Models\TypeDamageEloquentModel;

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
            TypeDamageEloquentModel::query()->firstOrCreate(
                ['type_damage_name' => $damage],
                [
                    'uuid' => Uuid::uuid4()->toString(),
                    'description' => 'Descripción de ' . $damage,
                    'severity' => 'low',
                ],
            );
        }
    }
}