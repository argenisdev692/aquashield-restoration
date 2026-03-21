<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\PublicCompanies\Infrastructure\Persistence\Eloquent\Models\PublicCompanyEloquentModel;
use Ramsey\Uuid\Uuid;

final class PublicCompanySeeder extends Seeder
{
    public function run(): void
    {
        $publicCompanies = [
            [
                'public_company_name' => 'Integrity Claims',
                'unit' => null,
                'address' => null,
                'phone' => '8002138069',
                'email' => null,
                'website' => 'https://integrityclaimsgroup.com',
                'user_id' => 1,
            ],
            [
                'public_company_name' => 'National Virtual Adjuster',
                'unit' => null,
                'phone' => '8557001672',
                'email' => 'info@nationalvirtualadjuster.com',
                'address' => '1117B S 21st Ave, Hollywood, FL 33020',
                'website' => 'www.nationalvirtualadjuster.com',
                'user_id' => 1,
            ],
        ];

        foreach ($publicCompanies as $company) {
            PublicCompanyEloquentModel::query()->create([
                'uuid' => Uuid::uuid4()->toString(),
                'public_company_name' => $company['public_company_name'],
                'unit' => $company['unit'],
                'address' => $company['address'] ?? null,
                'address_2' => $company['unit'],
                'phone' => '+1' . $company['phone'],
                'email' => $company['email'],
                'website' => $company['website'],
                'user_id' => $company['user_id'],
            ]);
        }
    }
}
