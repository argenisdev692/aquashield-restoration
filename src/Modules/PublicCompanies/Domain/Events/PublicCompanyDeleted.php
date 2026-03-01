<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Domain\Events;

use Modules\PublicCompanies\Domain\ValueObjects\PublicCompanyId;
use Shared\Domain\Events\DomainEvent;

final class PublicCompanyDeleted extends DomainEvent
{
    public function __construct(
        public readonly PublicCompanyId $id
    ) {
        parent::__construct($id->value);
    }

    public static function eventName(): string
    {
        return 'public_company.deleted';
    }
}
