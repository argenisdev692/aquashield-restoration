<?php

declare(strict_types=1);

namespace Modules\CompanyData\Infrastructure\Persistence\Mappers;

use Modules\CompanyData\Domain\Entities\CompanyData;
use Modules\CompanyData\Domain\Enums\CompanyStatus;
use Modules\CompanyData\Domain\ValueObjects\CompanyDataId;
use Modules\CompanyData\Domain\ValueObjects\Coordinates;
use Modules\CompanyData\Domain\ValueObjects\SocialLinks;
use Modules\CompanyData\Domain\ValueObjects\UserId;
use Modules\CompanyData\Infrastructure\Persistence\Eloquent\Models\CompanyDataEloquentModel;

/**
 * CompanyDataMapper
 */
final class CompanyDataMapper
{
    public static function toDomain(CompanyDataEloquentModel $model): CompanyData
    {
        return new CompanyData(
            id: new CompanyDataId($model->uuid),
            userId: new UserId($model->user?->uuid ?? ''),
            companyName: $model->company_name,
            name: $model->name,
            email: $model->email,
            phone: $model->phone,
            address: $model->address,
            address2: $model->getAttribute('address_2') ?? $model->getAttribute('adress_2'),
            socialLinks: new SocialLinks(
                facebook: $model->facebook_link,
                instagram: $model->instagram_link,
                linkedin: $model->linkedin_link,
                twitter: $model->twitter_link,
                website: $model->website
            ),
            coordinates: new Coordinates(
                latitude: $model->latitude !== null ? (float) $model->latitude : null,
                longitude: $model->longitude !== null ? (float) $model->longitude : null
            ),
            signaturePath: $model->signature_path,
            status: CompanyStatus::from($model->status ?? 'active'),
            createdAt: $model->created_at?->toIso8601String(),
            updatedAt: $model->updated_at?->toIso8601String(),
            deletedAt: $model->deleted_at?->toIso8601String()
        );
    }

    public static function toPersistenceArray(CompanyData $companyData, int $userId): array
    {
        $socialLinks = $companyData->socialLinks->toArray();
        $coords = $companyData->coordinates->toArray();

        return [
            'uuid' => $companyData->id->value,
            'user_id' => $userId,
            'company_name' => $companyData->companyName,
            'name' => $companyData->name,
            'email' => $companyData->email,
            'phone' => $companyData->phone,
            'address' => $companyData->address,
            'address_2' => $companyData->address2,
            'website' => $socialLinks['website'],
            'facebook_link' => $socialLinks['facebook'],
            'instagram_link' => $socialLinks['instagram'],
            'linkedin_link' => $socialLinks['linkedin'],
            'twitter_link' => $socialLinks['twitter'],
            'latitude' => $coords['latitude'],
            'longitude' => $coords['longitude'],
            'signature_path' => $companyData->signaturePath,
            'status' => $companyData->status->value,
            'deleted_at' => $companyData->deletedAt,
        ];
    }
}
