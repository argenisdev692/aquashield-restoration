<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Domain\ValueObjects;

use Shared\Domain\ValueObjects\Uuid as BaseUuid;

final readonly class PublicCompanyId extends BaseUuid
{
    public function value(): string
    {
        return $this->value;
    }
}