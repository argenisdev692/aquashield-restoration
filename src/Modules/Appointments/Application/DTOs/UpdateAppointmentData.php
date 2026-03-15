<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class UpdateAppointmentData extends Data
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $address = null,
        public ?string $address2 = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $zipcode = null,
        public ?string $country = null,
        public bool $insuranceProperty = false,
        public ?string $message = null,
        public bool $smsConsent = false,
        public ?string $registrationDate = null,
        public ?string $inspectionDate = null,
        public ?string $inspectionTime = null,
        public ?string $notes = null,
        public ?string $owner = null,
        public ?string $damageDetail = null,
        public bool $intentToClaim = false,
        public ?string $leadSource = null,
        public ?string $followUpDate = null,
        public ?string $additionalNote = null,
        public ?string $inspectionStatus = null,
        public ?string $statusLead = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
    ) {}
}
