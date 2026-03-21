<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class UpdatePublicCompanyData extends Data
{
    public function __construct(
        public string $publicCompanyName,
        public ?string $address = null,
        public ?string $address2 = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $website = null,
        public ?string $unit = null,
    ) {}
}
