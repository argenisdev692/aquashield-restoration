<?php

declare(strict_types=1);

namespace Src\Modules\ContactSupports\Application\Queries\ReadModels;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapOutputName(SnakeCaseMapper::class)]
final class ContactSupportListReadModel extends Data
{
    public function __construct(
        public string $uuid,
        public string $fullName,
        public string $email,
        public ?string $phone,
        public bool $smsConsent,
        public bool $readed,
        public string $createdAt,
        public ?string $deletedAt = null,
    ) {}
}
