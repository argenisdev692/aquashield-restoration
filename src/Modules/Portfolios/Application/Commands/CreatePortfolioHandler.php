<?php

declare(strict_types=1);

namespace Src\Modules\Portfolios\Application\Commands;

use RuntimeException;
use Src\Modules\Portfolios\Application\DTOs\StorePortfolioData;
use Src\Modules\Portfolios\Domain\Entities\Portfolio;
use Src\Modules\Portfolios\Domain\Ports\PortfolioRepositoryPort;
use Src\Modules\Portfolios\Domain\ValueObjects\PortfolioId;
use Src\Modules\ProjectTypes\Infrastructure\Persistence\Eloquent\Models\ProjectTypeEloquentModel;

final class CreatePortfolioHandler
{
    public function __construct(
        private readonly PortfolioRepositoryPort $repository,
    ) {}

    #[\NoDiscard('UUID of the created portfolio must be captured')]
    public function handle(StorePortfolioData $data): string
    {
        if ($data->projectTypeUuid !== null) {
            $exists = ProjectTypeEloquentModel::query()
                ->where('uuid', $data->projectTypeUuid)
                ->whereNull('deleted_at')
                ->exists();

            if (!$exists) {
                throw new RuntimeException('Project type not found.');
            }
        }

        $id        = PortfolioId::generate();
        $portfolio = Portfolio::create(
            id: $id,
            projectTypeUuid: $data->projectTypeUuid,
            createdAt: now()->toIso8601String(),
        );

        $this->repository->save($portfolio);

        return $id->toString();
    }
}
