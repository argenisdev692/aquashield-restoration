<?php

declare(strict_types=1);

namespace Src\Contexts\Auth\Domain\Ports;

use Src\Contexts\Auth\Domain\Entities\User;
use Src\Contexts\Auth\Domain\ValueObjects\UserEmail;

/**
 * UserRepositoryPort — Port for user persistence operations.
 *
 * Implementations live in Infrastructure/Persistence/Repositories/.
 */
interface UserRepositoryPort
{
    public function findByEmail(UserEmail $email): ?User;

    public function findByEmailOrPhone(string $identifier): ?User;

    public function findById(int $id): ?User;

    public function create(array $data): User;

    public function update(User $user, array $data): User;
}
