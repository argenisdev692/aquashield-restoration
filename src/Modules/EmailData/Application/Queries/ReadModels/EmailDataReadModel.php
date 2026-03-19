<?php

declare(strict_types=1);

namespace Modules\EmailData\Application\Queries\ReadModels;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapOutputName(SnakeCaseMapper::class)]
final class EmailDataReadModel extends Data
{
    public function __construct(
        public string $uuid,
        public ?string $description,
        public string $email,
        public ?string $phone,
        public ?string $type,
        public int $userId,
        public string $createdAt,
        public string $updatedAt,
        public ?string $deletedAt = null,
    ) {}
}
