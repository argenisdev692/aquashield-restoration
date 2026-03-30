<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\CallHistoryPermissionsSeeder;
use Database\Seeders\ClaimsPermissionsSeeder;
use Database\Seeders\ClaimStatusPermissionsSeeder;
use Database\Seeders\CustomerPermissionsSeeder;
use Database\Seeders\DocumentTemplateAdjusterPermissionsSeeder;
use Database\Seeders\DocumentTemplatePermissionsSeeder;
use Database\Seeders\FilesEsxPermissionsSeeder;
use Database\Seeders\MortgageCompanyPermissionsSeeder;
use Database\Seeders\PortfolioPermissionsSeeder;
use Database\Seeders\PropertyPermissionsSeeder;
use Database\Seeders\ZonePermissionsSeeder;
use Modules\Blog\Infrastructure\Persistence\Eloquent\Seeders\PostPermissionsSeeder;
use Modules\Users\Infrastructure\Persistence\Eloquent\Seeders\UsersPermissionsSeeder;

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
        $this->call(UsersPermissionsSeeder::class);

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
        $this->call(ClaimStatusPermissionsSeeder::class);

        // PUBLIC COMPANIES - Call PublicCompanySeeder
        $this->call(PublicCompanySeeder::class);

        // ALLIANCE COMPANIES - Call AllianceCompanySeeder
        $this->call(AllianceCompanySeeder::class);

        // CUSTOMER / PROPERTY / PORTFOLIO - Call related seeders
        $this->call(CustomerPermissionsSeeder::class);
        $this->call(PropertyPermissionsSeeder::class);
        $this->call(PortfolioPermissionsSeeder::class);

        // MORTGAGE COMPANY - Call permissions seeder
        $this->call(MortgageCompanyPermissionsSeeder::class);

        // ZONES - Call ZoneSeeder
        $this->call(ZoneSeeder::class);
        $this->call(ZonePermissionsSeeder::class);

        // BLOG AND EMAIL DATA - Call Blog and Email Seeders
        $this->call(BlogCategorySeeder::class);
        $this->call(PostPermissionsSeeder::class);
        $this->call(DocumentTemplatePermissionsSeeder::class);
        $this->call(DocumentTemplateAdjusterPermissionsSeeder::class);
        $this->call(EmailDataSeeder::class);

        // FILES ESX - Call permissions seeder
        $this->call(FilesEsxPermissionsSeeder::class);

        // DASHBOARD - Dashboard Permissions (Kanban board)
        $this->call(DashboardPermissionsSeeder::class);

        // CALL HISTORY - Call History Permissions
        $this->call(CallHistoryPermissionsSeeder::class);

        // DOCUMENT TEMPLATE ALLIANCES - Call Permissions Seeder
        $this->call(DocumentTemplateAlliancePermissionsSeeder::class);

        // CLAIMS - Call Claims Permissions Seeder
        $this->call(ClaimsPermissionsSeeder::class);
    }
}
