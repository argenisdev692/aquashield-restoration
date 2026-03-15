<?php

declare(strict_types=1);

namespace Src\Modules\ContactSupports\Application\Queries\ReadModels;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapOutputName(SnakeCaseMapper::class)]
final class ContactSupportReadModel extends Data
{
    public function __construct(
        public string $uuid,
        public string $fullName,
        public string $firstName,
        public ?string $lastName,
        public string $email,
        public ?string $phone,
        public string $message,
        public bool $smsConsent,
        public bool $readed,
        public string $createdAt,
        public string $updatedAt,
        public ?string $deletedAt = null,
    ) {}
}
