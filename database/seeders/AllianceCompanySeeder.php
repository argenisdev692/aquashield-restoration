<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AllianceCompany;
use Ramsey\Uuid\Uuid;

class AllianceCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allianceCompanies = [
            [
                'alliance_company_name' => 'Claim Pay',
                'phone' => '+18774433007',
                'email' => 'info@claimpay.net',
                'address' => '111 E 17th St #13327 SMB#60762 Austin, TX 78701',
                'website' => 'https://claimpay.net',
                'user_id' => 1,
            ],
            [
                'alliance_company_name' => 'Servxpress Restoration, LLC ',
                'phone' => '+18323921147',
                'email' => 'claims@servxpressrestorations.com',
                'address' => '178 N Fry suite 260 Houston, TX 77084',
                'website' => 'https://servxpressrestorations.com/restoration/',
                'user_id' => 1,
            ],
        ];

        foreach ($allianceCompanies as $companyData) {
            $companyData['uuid'] = Uuid::uuid4()->toString();
            AllianceCompany::create($companyData);
        }
    }
} 