<?php

declare(strict_types=1);

namespace Src\Modules\Properties\Application\Commands;

use RuntimeException;
use Src\Modules\Properties\Application\DTOs\UpdatePropertyData;
use Src\Modules\Properties\Domain\Ports\PropertyRepositoryPort;
use Src\Modules\Properties\Domain\ValueObjects\PropertyId;

final class UpdatePropertyHandler
{
    public function __construct(
        private readonly PropertyRepositoryPort $repository,
    ) {}

    public function handle(string $uuid, UpdatePropertyData $data): void
    {
        $id       = PropertyId::fromString($uuid);
        $property = $this->repository->find($id);

        if ($property === null) {
            throw new RuntimeException('Property not found.');
        }

        $property->update(
            propertyAddress: $data->propertyAddress,
            propertyAddress2: $data->propertyAddress2,
            propertyState: $data->propertyState,
            propertyCity: $data->propertyCity,
            propertyPostalCode: $data->propertyPostalCode,
            propertyCountry: $data->propertyCountry,
            propertyLatitude: $data->propertyLatitude,
            propertyLongitude: $data->propertyLongitude,
            updatedAt: now()->toIso8601String(),
        );

        $this->repository->save($property);
    }
}
