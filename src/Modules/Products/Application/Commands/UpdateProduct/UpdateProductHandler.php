<?php

declare(strict_types=1);

namespace Src\Modules\Products\Application\Commands\UpdateProduct;

use Illuminate\Support\Facades\Cache;
use Src\Modules\Products\Domain\Events\ProductUpdated;
use Src\Modules\Products\Domain\Ports\ProductRepositoryPort;
use Src\Modules\Products\Domain\ValueObjects\ProductId;

class UpdateProductHandler
{
    public function __construct(
        private readonly ProductRepositoryPort $repository
    ) {}

    public function handle(UpdateProductCommand $command): void
    {
        $productId = ProductId::fromString($command->uuid);
        $product = $this->repository->find($productId);

        if (!$product) {
            throw new \DomainException('Product not found');
        }

        $product->update(
            categoryId: $command->categoryId,
            name: $command->name,
            description: $command->description,
            price: $command->price,
            unit: $command->unit,
            orderPosition: $command->orderPosition,
            updatedAt: now()->toIso8601String()
        );

        $this->repository->save($product);

        event(new ProductUpdated(
            productId: $productId,
            name: $command->name,
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
