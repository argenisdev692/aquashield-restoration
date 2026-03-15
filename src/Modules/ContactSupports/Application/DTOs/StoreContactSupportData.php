<?php

declare(strict_types=1);

namespace Src\Modules\ContactSupports\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class StoreContactSupportData extends Data
{
    public function __construct(
        public string $firstName,
        public ?string $lastName = null,
        public string $email,
        public ?string $phone = null,
        public string $message,
        public bool $smsConsent = false,
        public bool $readed = false,
    ) {}
}
