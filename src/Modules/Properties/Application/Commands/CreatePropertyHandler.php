<?php

declare(strict_types=1);

namespace Src\Modules\Properties\Application\Commands;

use Src\Modules\Properties\Application\DTOs\StorePropertyData;
use Src\Modules\Properties\Domain\Entities\Property;
use Src\Modules\Properties\Domain\Ports\PropertyRepositoryPort;
use Src\Modules\Properties\Domain\ValueObjects\PropertyId;

final class CreatePropertyHandler
{
    public function __construct(
        private readonly PropertyRepositoryPort $repository,
    ) {}

    #[\NoDiscard('UUID of the created property must be captured')]
    public function handle(StorePropertyData $data): string
    {
        $id = PropertyId::generate();
        $property = Property::create(
            id: $id,
            propertyAddress: $data->propertyAddress,
            propertyAddress2: $data->propertyAddress2,
            propertyState: $data->propertyState,
            propertyCity: $data->propertyCity,
            propertyPostalCode: $data->propertyPostalCode,
            propertyCountry: $data->propertyCountry,
            propertyLatitude: $data->propertyLatitude,
            propertyLongitude: $data->propertyLongitude,
            createdAt: now()->toIso8601String(),
        );

        $this->repository->save($property);

        return $id->toString();
    }
}
