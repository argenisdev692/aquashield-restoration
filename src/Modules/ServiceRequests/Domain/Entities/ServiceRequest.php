<?php

declare(strict_types=1);

namespace Src\Modules\ServiceRequests\Domain\Entities;

use InvalidArgumentException;
use Shared\Domain\Entities\AggregateRoot;
use Src\Modules\ServiceRequests\Domain\ValueObjects\ServiceRequestId;

class ServiceRequest extends AggregateRoot
{
    private function __construct(
        private ServiceRequestId $id,
        private string $requestedService,
        private string $createdAt,
        private string $updatedAt,
        private ?string $deletedAt = null,
    ) {
        $this->requestedService = self::normalizeRequestedService($requestedService);
    }

    public static function create(
        ServiceRequestId $id,
        string $requestedService,
        string $createdAt,
    ): self {
        return new self(
            id: $id,
            requestedService: $requestedService,
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );
    }

    public static function reconstitute(
        ServiceRequestId $id,
        string $requestedService,
        string $createdAt,
        string $updatedAt,
        ?string $deletedAt,
    ): self {
        return new self(
            id: $id,
            requestedService: $requestedService,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            deletedAt: $deletedAt,
        );
    }

    public function update(string $requestedService, string $updatedAt): void
    {
        $this->requestedService = self::normalizeRequestedService($requestedService);
        $this->updatedAt = $updatedAt;
    }

    public function id(): ServiceRequestId
    {
        return $this->id;
    }

    public function requestedService(): string
    {
        return $this->requestedService;
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

    private static function normalizeRequestedService(string $requestedService): string
    {
        $normalized = $requestedService |> trim(...);

        if ($normalized === '') {
            throw new InvalidArgumentException('Requested service is required.');
        }

        return $normalized;
    }
}
