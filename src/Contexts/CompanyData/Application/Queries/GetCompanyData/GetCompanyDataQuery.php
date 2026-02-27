<?php

declare(strict_types=1);

namespace Src\Contexts\CompanyData\Application\Queries\GetCompanyData;

final readonly class GetCompanyDataQuery
{
    public function __construct(
        // we can find by either uuid or user_id
        public ?string $id = null,
        public ?int $userId = null
    ) {
        if ($id === null && $userId === null) {
            throw new \InvalidArgumentException("Must provide either id or userId to GetCompanyDataQuery.");
        }
    }
}
