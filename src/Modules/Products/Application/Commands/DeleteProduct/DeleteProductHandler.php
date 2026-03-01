<?php

declare(strict_types=1);

namespace Src\Modules\Products\Application\Commands\DeleteProduct;

use Illuminate\Support\Facades\Cache;
use Src\Modules\Products\Domain\Events\ProductDeleted;
use Src\Modules\Products\Domain\Ports\ProductRepositoryPort;
use Src\Modules\Products\Domain\ValueObjects\ProductId;

class DeleteProductHandler
{
    public function __construct(
        private readonly ProductRepositoryPort $repository
    ) {}

    public function handle(DeleteProductCommand $command): void
    {
        $productId = ProductId::fromString($command->uuid);
        $product = $this->repository->find($productId);

        if (!$product) {
            throw new \DomainException('Product not found');
        }

        $this->repository->softDelete($productId);

        event(new ProductDeleted(
            productId: $productId,
            name: $product->name(),
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
