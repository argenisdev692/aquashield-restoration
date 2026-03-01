<?php

declare(strict_types=1);

namespace Src\Modules\Products\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'categoryId' => $this->categoryId,
            'categoryName' => $this->categoryName,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'unit' => $this->unit,
            'orderPosition' => $this->orderPosition,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }
}
