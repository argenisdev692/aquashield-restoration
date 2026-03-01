<?php

declare(strict_types=1);

namespace Src\Modules\Products\Application\Commands\BulkDeleteProducts;

final readonly class BulkDeleteProductsCommand
{
    /**
     * @param array<string> $uuids
     */
    public function __construct(
        public array $uuids
    ) {}
}
