<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Domain\Events;

use Modules\PublicCompanies\Domain\Entities\PublicCompany;
use Shared\Domain\Events\DomainEvent;

final class PublicCompanyCreated extends DomainEvent
{
    public function __construct(
        public readonly PublicCompany $PublicCompany
    ) {
        parent::__construct($PublicCompany->getId()->value);
    }

    public static function eventName(): string
    {
        return 'public_company.created';
    }
}
