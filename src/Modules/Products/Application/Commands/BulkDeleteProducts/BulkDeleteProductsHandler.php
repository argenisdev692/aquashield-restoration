<?php

declare(strict_types=1);

namespace Src\Modules\Products\Application\Commands\BulkDeleteProducts;

use Src\Modules\Products\Application\Commands\DeleteProduct\DeleteProductCommand;
use Src\Modules\Products\Application\Commands\DeleteProduct\DeleteProductHandler;

final readonly class BulkDeleteProductsHandler
{
    public function __construct(
        private DeleteProductHandler $deleteHandler
    ) {}

    public function handle(BulkDeleteProductsCommand $command): int
    {
        $deletedCount = 0;

        foreach ($command->uuids as $uuid) {
            try {
                $this->deleteHandler->handle(new DeleteProductCommand($uuid));
                $deletedCount++;
            } catch (\Exception $e) {
                // Log error but continue with other deletions
                \Log::warning("Failed to delete product {$uuid}: {$e->getMessage()}");
            }
        }

        return $deletedCount;
    }
}
