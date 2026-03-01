<?php
$files = [
    'src/Modules/PublicCompanies/Infrastructure/Persistence/Repositories/EloquentPublicCompanyRepository.php',
    'src/Modules/PublicCompanies/Infrastructure/Persistence/Mappers/PublicCompanyMapper.php',
    'src/Modules/PublicCompanies/Infrastructure/Persistence/Eloquent/Models/PublicCompanyEloquentModel.php',
    'src/Modules/PublicCompanies/Infrastructure/Http/Resources/PublicCompanyResource.php',
    'src/Modules/PublicCompanies/Infrastructure/Http/Requests/CreatePublicCompanyRequest.php',
    'src/Modules/PublicCompanies/Infrastructure/Http/Requests/UpdatePublicCompanyRequest.php',
    'src/Modules/PublicCompanies/Infrastructure/Http/Controllers/Api/PublicCompanyController.php',
    'src/Modules/PublicCompanies/Application/DTOs/PublicCompanyDTO.php',
    'src/Modules/PublicCompanies/Application/Commands/UpdatePublicCompany/UpdatePublicCompanyHandler.php',
    'src/Modules/PublicCompanies/Application/Commands/CreatePublicCompany/CreatePublicCompanyHandler.php',
    'src/Modules/PublicCompanies/Domain/Entities/PublicCompany.php',
    'resources/js/modules/public-companies/types.ts',
    'resources/js/pages/public-companies/PublicCompanyShowPage.tsx',
    'resources/js/pages/public-companies/components/PublicCompanyForm.tsx',
    'resources/js/pages/public-companies/components/PublicCompaniesTable.tsx'
];

foreach ($files as $file) {
    if (!file_exists($file))
        continue;
    $content = file_get_contents($file);

    // DTO / Requests / Resource / Mapper / Handlers / Controllers
    $content = preg_replace("/('website' =>.*?)/", "$1\n            'unit' => \$this->resource->getUnit(),", $content);
    // Be careful with replacement.
}
