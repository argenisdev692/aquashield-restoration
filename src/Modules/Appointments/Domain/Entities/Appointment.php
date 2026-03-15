<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Domain\Entities;

use InvalidArgumentException;
use Shared\Domain\Entities\AggregateRoot;
use Src\Modules\Appointments\Domain\ValueObjects\AppointmentId;

final class Appointment extends AggregateRoot
{
    private function __construct(
        private AppointmentId $id,
        private string $firstName,
        private string $lastName,
        private ?string $phone,
        private ?string $email,
        private ?string $address,
        private ?string $address2,
        private ?string $city,
        private ?string $state,
        private ?string $zipcode,
        private ?string $country,
        private bool $insuranceProperty,
        private ?string $message,
        private bool $smsConsent,
        private ?string $registrationDate,
        private ?string $inspectionDate,
        private ?string $inspectionTime,
        private ?string $notes,
        private ?string $owner,
        private ?string $damageDetail,
        private bool $intentToClaim,
        private ?string $leadSource,
        private ?string $followUpDate,
        private ?string $additionalNote,
        private string $inspectionStatus,
        private string $statusLead,
        private ?float $latitude,
        private ?float $longitude,
        private string $createdAt,
        private string $updatedAt,
        private ?string $deletedAt = null,
    ) {
        $this->firstName = self::normalizeRequiredText($firstName, 'First name is required.');
        $this->lastName = self::normalizeRequiredText($lastName, 'Last name is required.');
        $this->phone = self::normalizeOptionalText($phone);
        $this->email = self::normalizeOptionalEmail($email);
        $this->address = self::normalizeOptionalText($address);
        $this->address2 = self::normalizeOptionalText($address2);
        $this->city = self::normalizeOptionalText($city);
        $this->state = self::normalizeOptionalText($state);
        $this->zipcode = self::normalizeOptionalText($zipcode);
        $this->country = self::normalizeOptionalText($country);
        $this->message = self::normalizeOptionalText($message);
        $this->registrationDate = self::normalizeOptionalText($registrationDate);
        $this->inspectionDate = self::normalizeOptionalText($inspectionDate);
        $this->inspectionTime = self::normalizeOptionalText($inspectionTime);
        $this->notes = self::normalizeOptionalText($notes);
        $this->owner = self::normalizeOptionalText($owner);
        $this->damageDetail = self::normalizeOptionalText($damageDetail);
        $this->leadSource = self::normalizeOptionalText($leadSource);
        $this->followUpDate = self::normalizeOptionalText($followUpDate);
        $this->additionalNote = self::normalizeOptionalText($additionalNote);
        $this->inspectionStatus = self::normalizeStatus($inspectionStatus, 'Pending');
        $this->statusLead = self::normalizeStatus($statusLead, 'New');
    }

    public static function create(
        AppointmentId $id,
        string $firstName,
        string $lastName,
        ?string $phone,
        ?string $email,
        ?string $address,
        ?string $address2,
        ?string $city,
        ?string $state,
        ?string $zipcode,
        ?string $country,
        bool $insuranceProperty,
        ?string $message,
        bool $smsConsent,
        ?string $registrationDate,
        ?string $inspectionDate,
        ?string $inspectionTime,
        ?string $notes,
        ?string $owner,
        ?string $damageDetail,
        bool $intentToClaim,
        ?string $leadSource,
        ?string $followUpDate,
        ?string $additionalNote,
        ?string $inspectionStatus,
        ?string $statusLead,
        ?float $latitude,
        ?float $longitude,
        string $createdAt,
    ): self {
        return new self(
            id: $id,
            firstName: $firstName,
            lastName: $lastName,
            phone: $phone,
            email: $email,
            address: $address,
            address2: $address2,
            city: $city,
            state: $state,
            zipcode: $zipcode,
            country: $country,
            insuranceProperty: $insuranceProperty,
            message: $message,
            smsConsent: $smsConsent,
            registrationDate: $registrationDate,
            inspectionDate: $inspectionDate,
            inspectionTime: $inspectionTime,
            notes: $notes,
            owner: $owner,
            damageDetail: $damageDetail,
            intentToClaim: $intentToClaim,
            leadSource: $leadSource,
            followUpDate: $followUpDate,
            additionalNote: $additionalNote,
            inspectionStatus: $inspectionStatus ?? 'Pending',
            statusLead: $statusLead ?? 'New',
            latitude: $latitude,
            longitude: $longitude,
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );
    }

    public static function reconstitute(
        AppointmentId $id,
        string $firstName,
        string $lastName,
        ?string $phone,
        ?string $email,
        ?string $address,
        ?string $address2,
        ?string $city,
        ?string $state,
        ?string $zipcode,
        ?string $country,
        bool $insuranceProperty,
        ?string $message,
        bool $smsConsent,
        ?string $registrationDate,
        ?string $inspectionDate,
        ?string $inspectionTime,
        ?string $notes,
        ?string $owner,
        ?string $damageDetail,
        bool $intentToClaim,
        ?string $leadSource,
        ?string $followUpDate,
        ?string $additionalNote,
        string $inspectionStatus,
        string $statusLead,
        ?float $latitude,
        ?float $longitude,
        string $createdAt,
        string $updatedAt,
        ?string $deletedAt,
    ): self {
        return new self(
            id: $id,
            firstName: $firstName,
            lastName: $lastName,
            phone: $phone,
            email: $email,
            address: $address,
            address2: $address2,
            city: $city,
            state: $state,
            zipcode: $zipcode,
            country: $country,
            insuranceProperty: $insuranceProperty,
            message: $message,
            smsConsent: $smsConsent,
            registrationDate: $registrationDate,
            inspectionDate: $inspectionDate,
            inspectionTime: $inspectionTime,
            notes: $notes,
            owner: $owner,
            damageDetail: $damageDetail,
            intentToClaim: $intentToClaim,
            leadSource: $leadSource,
            followUpDate: $followUpDate,
            additionalNote: $additionalNote,
            inspectionStatus: $inspectionStatus,
            statusLead: $statusLead,
            latitude: $latitude,
            longitude: $longitude,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            deletedAt: $deletedAt,
        );
    }

    public function update(
        string $firstName,
        string $lastName,
        ?string $phone,
        ?string $email,
        ?string $address,
        ?string $address2,
        ?string $city,
        ?string $state,
        ?string $zipcode,
        ?string $country,
        bool $insuranceProperty,
        ?string $message,
        bool $smsConsent,
        ?string $registrationDate,
        ?string $inspectionDate,
        ?string $inspectionTime,
        ?string $notes,
        ?string $owner,
        ?string $damageDetail,
        bool $intentToClaim,
        ?string $leadSource,
        ?string $followUpDate,
        ?string $additionalNote,
        ?string $inspectionStatus,
        ?string $statusLead,
        ?float $latitude,
        ?float $longitude,
        string $updatedAt,
    ): void {
        $this->firstName = self::normalizeRequiredText($firstName, 'First name is required.');
        $this->lastName = self::normalizeRequiredText($lastName, 'Last name is required.');
        $this->phone = self::normalizeOptionalText($phone);
        $this->email = self::normalizeOptionalEmail($email);
        $this->address = self::normalizeOptionalText($address);
        $this->address2 = self::normalizeOptionalText($address2);
        $this->city = self::normalizeOptionalText($city);
        $this->state = self::normalizeOptionalText($state);
        $this->zipcode = self::normalizeOptionalText($zipcode);
        $this->country = self::normalizeOptionalText($country);
        $this->insuranceProperty = $insuranceProperty;
        $this->message = self::normalizeOptionalText($message);
        $this->smsConsent = $smsConsent;
        $this->registrationDate = self::normalizeOptionalText($registrationDate);
        $this->inspectionDate = self::normalizeOptionalText($inspectionDate);
        $this->inspectionTime = self::normalizeOptionalText($inspectionTime);
        $this->notes = self::normalizeOptionalText($notes);
        $this->owner = self::normalizeOptionalText($owner);
        $this->damageDetail = self::normalizeOptionalText($damageDetail);
        $this->intentToClaim = $intentToClaim;
        $this->leadSource = self::normalizeOptionalText($leadSource);
        $this->followUpDate = self::normalizeOptionalText($followUpDate);
        $this->additionalNote = self::normalizeOptionalText($additionalNote);
        $this->inspectionStatus = self::normalizeStatus($inspectionStatus, 'Pending');
        $this->statusLead = self::normalizeStatus($statusLead, 'New');
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->updatedAt = $updatedAt;
    }

    public function id(): AppointmentId { return $this->id; }
    public function firstName(): string { return $this->firstName; }
    public function lastName(): string { return $this->lastName; }
    public function fullName(): string { return trim($this->firstName . ' ' . $this->lastName); }
    public function phone(): ?string { return $this->phone; }
    public function email(): ?string { return $this->email; }
    public function address(): ?string { return $this->address; }
    public function address2(): ?string { return $this->address2; }
    public function city(): ?string { return $this->city; }
    public function state(): ?string { return $this->state; }
    public function zipcode(): ?string { return $this->zipcode; }
    public function country(): ?string { return $this->country; }
    public function insuranceProperty(): bool { return $this->insuranceProperty; }
    public function message(): ?string { return $this->message; }
    public function smsConsent(): bool { return $this->smsConsent; }
    public function registrationDate(): ?string { return $this->registrationDate; }
    public function inspectionDate(): ?string { return $this->inspectionDate; }
    public function inspectionTime(): ?string { return $this->inspectionTime; }
    public function notes(): ?string { return $this->notes; }
    public function owner(): ?string { return $this->owner; }
    public function damageDetail(): ?string { return $this->damageDetail; }
    public function intentToClaim(): bool { return $this->intentToClaim; }
    public function leadSource(): ?string { return $this->leadSource; }
    public function followUpDate(): ?string { return $this->followUpDate; }
    public function additionalNote(): ?string { return $this->additionalNote; }
    public function inspectionStatus(): string { return $this->inspectionStatus; }
    public function statusLead(): string { return $this->statusLead; }
    public function latitude(): ?float { return $this->latitude; }
    public function longitude(): ?float { return $this->longitude; }
    public function createdAt(): string { return $this->createdAt; }
    public function updatedAt(): string { return $this->updatedAt; }
    public function deletedAt(): ?string { return $this->deletedAt; }

    private static function normalizeRequiredText(string $value, string $message): string
    {
        $normalized = trim($value);
        if ($normalized === '') {
            throw new InvalidArgumentException($message);
        }
        return $normalized;
    }

    private static function normalizeOptionalText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $normalized = trim($value);
        return $normalized === '' ? null : $normalized;
    }

    private static function normalizeOptionalEmail(?string $email): ?string
    {
        if ($email === null || trim($email) === '') {
            return null;
        }
        $normalized = strtolower(trim($email));
        if (filter_var($normalized, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Email must be valid.');
        }
        return $normalized;
    }

    private static function normalizeStatus(?string $status, string $default): string
    {
        $normalized = trim((string) $status);
        return $normalized === '' ? $default : $normalized;
    }
}
