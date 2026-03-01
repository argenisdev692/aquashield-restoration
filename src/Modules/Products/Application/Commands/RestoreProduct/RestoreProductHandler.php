<?php

declare(strict_types=1);

namespace Src\Modules\Products\Application\Commands\RestoreProduct;

use Illuminate\Support\Facades\Cache;
use Src\Modules\Products\Domain\Events\ProductRestored;
use Src\Modules\Products\Domain\Ports\ProductRepositoryPort;
use Src\Modules\Products\Domain\ValueObjects\ProductId;

class RestoreProductHandler
{
    public function __construct(
        private readonly ProductRepositoryPort $repository
    ) {}

    public function handle(RestoreProductCommand $command): void
    {
        $productId = ProductId::fromString($command->uuid);

        $this->repository->restore($productId);

        event(new ProductRestored(
            productId: $productId,
            name: 'Product',
            occurredOn: now()->toIso8601String()
        ));

        Cache::forget("product_{$command->uuid}");
        try {
            Cache::tags(['products_list'])->flush();
        } catch (\Exception $e) {
            // Tags not supported, expires naturally
        }
    }
}
