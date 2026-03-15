<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Application\Queries\ReadModels;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapOutputName(SnakeCaseMapper::class)]
final class AppointmentListReadModel extends Data
{
    public function __construct(
        public string $uuid,
        public string $fullName,
        public ?string $phone,
        public ?string $email,
        public string $inspectionStatus,
        public string $statusLead,
        public ?string $inspectionDate,
        public string $createdAt,
        public ?string $deletedAt = null,
    ) {}
}
