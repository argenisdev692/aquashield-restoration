<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Application\Commands;

use Modules\MortgageCompanies\Application\DTOs\BulkDeleteMortgageCompanyData;
use Modules\MortgageCompanies\Domain\Ports\MortgageCompanyRepositoryPort;
use Modules\MortgageCompanies\Domain\ValueObjects\MortgageCompanyId;

final class BulkDeleteMortgageCompanyHandler
{
    public function __construct(
        private readonly MortgageCompanyRepositoryPort $repository,
    ) {}

    #[\NoDiscard('The deleted count must be captured')]
    public function handle(BulkDeleteMortgageCompanyData $data): int
    {
        $ids = array_map(
            static fn (string $uuid): MortgageCompanyId => MortgageCompanyId::fromString($uuid),
            $data->uuids,
        );

        return $this->repository->bulkSoftDelete($ids);
    }
}
