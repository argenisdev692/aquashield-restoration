<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Domain\ValueObjects;

use Shared\Domain\ValueObjects\Uuid as BaseUuid;

final readonly class InsuranceCompanyId extends BaseUuid
{
    public function value(): string
    {
        return $this->value;
    }
}