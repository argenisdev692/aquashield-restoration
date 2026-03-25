<?php

declare(strict_types=1);

namespace Src\Modules\Properties\Infrastructure\Persistence\Mappers;

use Src\Modules\Properties\Domain\Entities\Property;
use Src\Modules\Properties\Domain\ValueObjects\PropertyId;
use Src\Modules\Properties\Infrastructure\Persistence\Eloquent\Models\PropertyEloquentModel;

final class PropertyMapper
{
    public function toDomain(PropertyEloquentModel $model): Property
    {
        return Property::reconstitute(
            id: PropertyId::fromString($model->uuid),
            propertyAddress: $model->property_address,
            propertyAddress2: $model->property_address_2,
            propertyState: $model->property_state,
            propertyCity: $model->property_city,
            propertyPostalCode: $model->property_postal_code,
            propertyCountry: $model->property_country,
            propertyLatitude: $model->property_latitude,
            propertyLongitude: $model->property_longitude,
            createdAt: $model->created_at?->toIso8601String() ?? '',
            updatedAt: $model->updated_at?->toIso8601String() ?? '',
            deletedAt: $model->deleted_at?->toIso8601String(),
        );
    }

    public function toEloquent(Property $property): PropertyEloquentModel
    {
        $model = PropertyEloquentModel::withTrashed()->firstOrNew([
            'uuid' => $property->id()->toString(),
        ]);

        $model->uuid               = $property->id()->toString();
        $model->property_address   = $property->propertyAddress();
        $model->property_address_2 = $property->propertyAddress2();
        $model->property_state     = $property->propertyState();
        $model->property_city      = $property->propertyCity();
        $model->property_postal_code = $property->propertyPostalCode();
        $model->property_country   = $property->propertyCountry();
        $model->property_latitude  = $property->propertyLatitude();
        $model->property_longitude = $property->propertyLongitude();

        return $model;
    }
}
