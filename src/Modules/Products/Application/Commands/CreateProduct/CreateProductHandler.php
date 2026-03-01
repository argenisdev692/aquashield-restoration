<?php

declare(strict_types=1);

namespace Src\Modules\Products\Application\Commands\CreateProduct;

use Illuminate\Support\Facades\Cache;
use Src\Modules\Products\Domain\Entities\Product;
use Src\Modules\Products\Domain\Events\ProductCreated;
use Src\Modules\Products\Domain\Ports\ProductRepositoryPort;
use Src\Modules\Products\Domain\ValueObjects\ProductId;

class CreateProductHandler
{
    public function __construct(
        private readonly ProductRepositoryPort $repository
    ) {}

    public function handle(CreateProductCommand $command): string
    {
        $productId = ProductId::generate();
        $now = now()->toIso8601String();

        $product = Product::create(
            id: $productId,
            categoryId: $command->categoryId,
            name: $command->name,
            description: $command->description,
            price: $command->price,
            unit: $command->unit,
            orderPosition: $command->orderPosition,
            createdAt: $now
        );

        $this->repository->save($product);

        event(new ProductCreated(
            productId: $productId,
            name: $command->name,
            occurredOn: $now
        ));

        try {
            Cache::tags(['products_list'])->flush();
        } catch (\Exception $e) {
            // Tags not supported, expires naturally
        }

        return $productId->toString();
    }
}
