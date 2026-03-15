<?php

declare(strict_types=1);

namespace Modules\Roles\Domain\Ports;

interface RoleRepositoryPort
{
    /**
     * @param array<string, mixed> $filters
     * @return array{data: list<array<string, mixed>>, total: int, perPage: int, currentPage: int, lastPage: int}
     */
    public function paginate(array $filters = []): array;

    /**
     * @return array<string, mixed>|null
     */
    public function findByUuid(string $uuid): ?array;

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function create(array $data): array;

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function update(string $uuid, array $data): array;

    public function delete(string $uuid): void;

    public function restore(string $uuid): void;
}
