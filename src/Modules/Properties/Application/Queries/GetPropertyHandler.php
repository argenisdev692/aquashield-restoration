<?php

declare(strict_types=1);

namespace Src\Modules\Properties\Application\Queries;

use Src\Modules\Properties\Application\Queries\ReadModels\PropertyReadModel;
use Src\Modules\Properties\Infrastructure\Persistence\Eloquent\Models\PropertyEloquentModel;

final class GetPropertyHandler
{
    public function handle(string $uuid): ?PropertyReadModel
    {
        $property = PropertyEloquentModel::withTrashed()
            ->select([
                'uuid',
                'property_address',
                'property_address_2',
                'property_state',
                'property_city',
                'property_postal_code',
                'property_country',
                'property_latitude',
                'property_longitude',
                'created_at',
                'updated_at',
                'deleted_at',
            ])
            ->where('uuid', $uuid)
            ->first();

        if ($property === null) {
            return null;
        }

        return new PropertyReadModel(
            uuid: $property->uuid,
            propertyAddress: $property->property_address,
            propertyAddress2: $property->property_address_2,
            propertyState: $property->property_state,
            propertyCity: $property->property_city,
            propertyPostalCode: $property->property_postal_code,
            propertyCountry: $property->property_country,
            propertyLatitude: $property->property_latitude,
            propertyLongitude: $property->property_longitude,
            createdAt: $property->created_at?->toIso8601String() ?? '',
            updatedAt: $property->updated_at?->toIso8601String() ?? '',
            deletedAt: $property->deleted_at?->toIso8601String(),
        );
    }
}
