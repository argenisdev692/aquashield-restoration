<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // SIEMPRE primero
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // User::factory(10)->create();

        // USERS AND ROLES - Call UserSeeder (Refactored)
        $this->call(UserSeeder::class);

        // COMPANY DATA - Call CompanySeeder
        $this->call(CompanySeeder::class);

        // CATEGORIES - Call Category Seeders
        $this->call(ServiceCategorySeeder::class);
        $this->call(CategoryProductSeeder::class);

        // PRODUCTS - Call ProductSeeder
        $this->call(ProductSeeder::class);

        // INSURANCE COMPANIES - Call InsuranceCompanySeeder
        $this->call(InsuranceCompanySeeder::class);

        // TYPE DAMAGES - Call TypeDamageSeeder
        $this->call(TypeDamageSeeder::class);

        // CAUSE OF LOSS - Call CauseOfLossSeeder
        $this->call(CauseOfLossSeeder::class);

        // CLAIM STATUS - Call ClaimStatuSeeder
        $this->call(ClaimStatuSeeder::class);

        // PUBLIC COMPANIES - Call PublicCompanySeeder
        $this->call(PublicCompanySeeder::class);

        // ALLIANCE COMPANIES - Call AllianceCompanySeeder
        $this->call(AllianceCompanySeeder::class);

        // ZONES - Call ZoneSeeder
        $this->call(ZoneSeeder::class);

        // BLOG AND EMAIL DATA - Call Blog and Email Seeders
        $this->call(BlogCategorySeeder::class);
        $this->call(EmailDataSeeder::class);
    }
}
