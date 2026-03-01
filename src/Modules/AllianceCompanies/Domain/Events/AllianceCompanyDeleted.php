<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Domain\Events;

use Modules\AllianceCompanies\Domain\ValueObjects\AllianceCompanyId;
use Shared\Domain\Events\DomainEvent;

final class AllianceCompanyDeleted extends DomainEvent
{
    public function __construct(
        public readonly AllianceCompanyId $id
    ) {
        parent::__construct($id->value);
    }

    public static function eventName(): string
    {
        return 'alliance_company.deleted';
    }
}
