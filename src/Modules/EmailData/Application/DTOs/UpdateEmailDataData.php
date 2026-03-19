<?php

declare(strict_types=1);

namespace Modules\EmailData\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class UpdateEmailDataData extends Data
{
    public function __construct(
        public ?string $description = null,
        public string $email = '',
        public ?string $phone = null,
        public ?string $type = null,
    ) {}
}
