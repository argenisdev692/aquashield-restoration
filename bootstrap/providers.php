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
    Modules\Users\Providers\UsersServiceProvider::class,
    Modules\CompanyData\Providers\CompanyDataServiceProvider::class,
    Modules\Blog\Providers\BlogServiceProvider::class,
    Modules\InsuranceCompanies\Providers\InsuranceCompaniesServiceProvider::class,
    Modules\MortgageCompanies\Providers\MortgageCompaniesServiceProvider::class,
    Modules\AllianceCompanies\Providers\AllianceCompaniesServiceProvider::class,
    Modules\PublicCompanies\Providers\PublicCompaniesServiceProvider::class,
    Src\Modules\Products\Providers\ProductsServiceProvider::class,
];
