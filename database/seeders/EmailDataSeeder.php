<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\EmailData\Infrastructure\Persistence\Eloquent\Models\EmailDataEloquentModel;
use Ramsey\Uuid\Uuid;

class EmailDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $emailsData = [
            [
                'description' => 'Correo para colecciones y pagos',
                'email' => 'collection@vgeneralcontractors.com',
                'phone' => '+17133646240',
                'type' => 'Collections',
                'user_id' => 1,
            ],
            [
                'description' => 'Correo para información general',
                'email' => 'info@vgeneralcontractors.com',
                'phone' => '+17135876423',
                'type' => 'Info',
                'user_id' => 1,
            ],
            [
                'description' => 'Correo para citas y agendamiento',
                'email' => 'admin@vgeneralcontractors.com',
                'phone' => '+17135876423',
                'type' => 'Admin',
                'user_id' => 2,
            ]
        ];

        foreach ($emailsData as $emailData) {
            $emailData['uuid'] = Uuid::uuid4()->toString();
            EmailDataEloquentModel::query()->create($emailData);
        }
    }
}