<?php

declare(strict_types=1);

namespace Src\Modules\ContactSupports\Domain\Entities;

use InvalidArgumentException;
use Shared\Domain\Entities\AggregateRoot;
use Src\Modules\ContactSupports\Domain\ValueObjects\ContactSupportId;

final class ContactSupport extends AggregateRoot
{
    private function __construct(
        private ContactSupportId $id,
        private string $firstName,
        private ?string $lastName,
        private string $email,
        private ?string $phone,
        private string $message,
        private bool $smsConsent,
        private bool $readed,
        private string $createdAt,
        private string $updatedAt,
        private ?string $deletedAt = null,
    ) {
        $this->firstName = self::normalizeRequiredText($firstName, 'First name is required.');
        $this->lastName = self::normalizeOptionalText($lastName);
        $this->email = self::normalizeEmail($email);
        $this->phone = self::normalizeOptionalText($phone);
        $this->message = self::normalizeRequiredText($message, 'Message is required.');
    }

    public static function create(
        ContactSupportId $id,
        string $firstName,
        ?string $lastName,
        string $email,
        ?string $phone,
        string $message,
        bool $smsConsent,
        bool $readed,
        string $createdAt,
    ): self {
        return new self(
            id: $id,
            firstName: $firstName,
            lastName: $lastName,
            email: $email,
            phone: $phone,
            message: $message,
            smsConsent: $smsConsent,
            readed: $readed,
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );
    }

    public static function reconstitute(
        ContactSupportId $id,
        string $firstName,
        ?string $lastName,
        string $email,
        ?string $phone,
        string $message,
        bool $smsConsent,
        bool $readed,
        string $createdAt,
        string $updatedAt,
        ?string $deletedAt,
    ): self {
        return new self(
            id: $id,
            firstName: $firstName,
            lastName: $lastName,
            email: $email,
            phone: $phone,
            message: $message,
            smsConsent: $smsConsent,
            readed: $readed,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            deletedAt: $deletedAt,
        );
    }

    public function update(
        string $firstName,
        ?string $lastName,
        string $email,
        ?string $phone,
        string $message,
        bool $smsConsent,
        bool $readed,
        string $updatedAt,
    ): void {
        $this->firstName = self::normalizeRequiredText($firstName, 'First name is required.');
        $this->lastName = self::normalizeOptionalText($lastName);
        $this->email = self::normalizeEmail($email);
        $this->phone = self::normalizeOptionalText($phone);
        $this->message = self::normalizeRequiredText($message, 'Message is required.');
        $this->smsConsent = $smsConsent;
        $this->readed = $readed;
        $this->updatedAt = $updatedAt;
    }

    public function id(): ContactSupportId
    {
        return $this->id;
    }

    public function firstName(): string
    {
        return $this->firstName;
    }

    public function lastName(): ?string
    {
        return $this->lastName;
    }

    public function fullName(): string
    {
        return trim(implode(' ', array_filter([$this->firstName, $this->lastName])));
    }

    public function email(): string
    {
        return $this->email;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function smsConsent(): bool
    {
        return $this->smsConsent;
    }

    public function readed(): bool
    {
        return $this->readed;
    }

    public function createdAt(): string
    {
        return $this->createdAt;
    }

    public function updatedAt(): string
    {
        return $this->updatedAt;
    }

    public function deletedAt(): ?string
    {
        return $this->deletedAt;
    }

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

    private static function normalizeEmail(string $email): string
    {
        $normalized = strtolower(trim($email));

        if ($normalized === '' || filter_var($normalized, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('A valid email is required.');
        }

        return $normalized;
    }
}
