<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class UpdateAllianceCompanyData extends Data
{
    public function __construct(
        public string $allianceCompanyName,
        public ?string $address,
        public ?string $phone,
        public ?string $email,
        public ?string $website,
    ) {}
}
