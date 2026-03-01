<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Domain\ValueObjects;

use Shared\Domain\ValueObjects\Uuid as BaseUuid;

final readonly class AllianceCompanyId extends BaseUuid
{
    public function value(): string
    {
        return $this->value;
    }
}