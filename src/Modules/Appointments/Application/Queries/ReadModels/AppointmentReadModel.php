<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Application\Queries\ReadModels;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapOutputName(SnakeCaseMapper::class)]
final class AppointmentReadModel extends Data
{
    public function __construct(
        public string $uuid,
        public string $fullName,
        public string $firstName,
        public string $lastName,
        public ?string $phone,
        public ?string $email,
        public ?string $address,
        public ?string $address2,
        public ?string $city,
        public ?string $state,
        public ?string $zipcode,
        public ?string $country,
        public bool $insuranceProperty,
        public ?string $message,
        public bool $smsConsent,
        public ?string $registrationDate,
        public ?string $inspectionDate,
        public ?string $inspectionTime,
        public ?string $notes,
        public ?string $owner,
        public ?string $damageDetail,
        public bool $intentToClaim,
        public ?string $leadSource,
        public ?string $followUpDate,
        public ?string $additionalNote,
        public string $inspectionStatus,
        public string $statusLead,
        public ?float $latitude,
        public ?float $longitude,
        public string $createdAt,
        public string $updatedAt,
        public ?string $deletedAt = null,
    ) {}
}
