<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,
    App\Providers\TelescopeServiceProvider::class,

    Src\Core\Providers\CoreServiceProvider::class,

    // ── Bounded Context Providers ──
    Src\Contexts\Auth\Providers\AuthServiceProvider::class,
    Src\Contexts\Users\Providers\UsersServiceProvider::class,
    Src\Contexts\CompanyData\Providers\CompanyDataServiceProvider::class,
];
