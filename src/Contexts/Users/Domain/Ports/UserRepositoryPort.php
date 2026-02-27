<?php

declare(strict_types=1);

namespace Src\Contexts\Users\Domain\Ports;

use Src\Contexts\Users\Domain\Entities\User;

/**
 * UserRepositoryPort — Domain interface for User persistence.
 *
 * Infrastructure implements this via EloquentUserRepository.
 * All public-facing lookups use UUID, never internal integer ID.
 */
interface UserRepositoryPort
{
    public function findByUuid(string $uuid): ?User;

    /**
     * @param array<string, mixed> $filters
     * @return array{data: list<User>, total: int, perPage: int, currentPage: int, lastPage: int}
     */
    public function findAllPaginated(array $filters = [], int $page = 1, int $perPage = 15): array;

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): User;

    /**
     * @param array<string, mixed> $data
     */
    public function update(string $uuid, array $data): User;

    public function softDelete(string $uuid): void;

    public function restore(string $uuid): void;
}
