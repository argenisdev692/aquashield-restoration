<?php

declare(strict_types=1);

namespace Src\Modules\Portfolios\Application\Commands;

use RuntimeException;
use Src\Modules\Portfolios\Application\DTOs\UpdatePortfolioData;
use Src\Modules\Portfolios\Domain\Ports\PortfolioRepositoryPort;
use Src\Modules\Portfolios\Domain\ValueObjects\PortfolioId;
use Src\Modules\ProjectTypes\Infrastructure\Persistence\Eloquent\Models\ProjectTypeEloquentModel;

final class UpdatePortfolioHandler
{
    public function __construct(
        private readonly PortfolioRepositoryPort $repository,
    ) {}

    public function handle(string $uuid, UpdatePortfolioData $data): void
    {
        $portfolio = $this->repository->find(PortfolioId::fromString($uuid));

        if ($portfolio === null) {
            throw new RuntimeException('Portfolio not found.');
        }

        if ($data->projectTypeUuid !== null) {
            $exists = ProjectTypeEloquentModel::query()
                ->where('uuid', $data->projectTypeUuid)
                ->whereNull('deleted_at')
                ->exists();

            if (!$exists) {
                throw new RuntimeException('Project type not found.');
            }
        }

        $portfolio->update(
            projectTypeUuid: $data->projectTypeUuid,
            updatedAt: now()->toIso8601String(),
        );

        $this->repository->save($portfolio);
    }
}
