<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,
    App\Providers\TelescopeServiceProvider::class,

    Src\Providers\CoreServiceProvider::class,
    Src\Providers\EventServiceProvider::class,

    // ── Bounded Context Providers ──
    Modules\Auth\Providers\AuthServiceProvider::class,
    Modules\AccessControl\Providers\AccessControlServiceProvider::class,
    Modules\Roles\Providers\RolesServiceProvider::class,
    Modules\Users\Providers\UsersServiceProvider::class,
    Modules\CompanyData\Providers\CompanyDataServiceProvider::class,
    Modules\Blog\Providers\BlogServiceProvider::class,
    Modules\InsuranceCompanies\Providers\InsuranceCompaniesServiceProvider::class,
    Modules\MortgageCompanies\Providers\MortgageCompaniesServiceProvider::class,
    Modules\AllianceCompanies\Providers\AllianceCompaniesServiceProvider::class,
    Modules\EmailData\Providers\EmailDataServiceProvider::class,
    Modules\PublicCompanies\Providers\PublicCompaniesServiceProvider::class,
    Src\Modules\Appointments\Providers\AppointmentsServiceProvider::class,
    Src\Modules\ContactSupports\Providers\ContactSupportsServiceProvider::class,
    Src\Modules\ServiceRequests\Providers\ServiceRequestsServiceProvider::class,
    Src\Modules\Products\Providers\ProductsServiceProvider::class,
    Src\Modules\TypeDamages\Providers\TypeDamagesServiceProvider::class,
    Src\Modules\CauseOfLosses\Providers\CauseOfLossesServiceProvider::class,
    Src\Modules\CategoryProducts\Providers\CategoryProductsServiceProvider::class,
    Src\Modules\ServiceCategories\Providers\ServiceCategoriesServiceProvider::class,
    Src\Modules\ProjectTypes\Providers\ProjectTypesServiceProvider::class,
    Src\Modules\Portfolios\Providers\PortfoliosServiceProvider::class,
    Modules\CallHistory\Providers\CallHistoryServiceProvider::class,
    Src\Modules\ClaimStatuses\Providers\ClaimStatusesServiceProvider::class,
    Src\Modules\DocumentTemplateAlliances\Providers\DocumentTemplateAlliancesServiceProvider::class,
];
