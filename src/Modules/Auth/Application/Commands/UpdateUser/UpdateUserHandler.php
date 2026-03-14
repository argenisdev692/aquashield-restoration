<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Commands\UpdateUser;

use Modules\Auth\Application\Support\AuthCacheKeys;
use Modules\Auth\Domain\Entities\User;
use Modules\Auth\Domain\Exceptions\UserNotFoundException;
use Modules\Auth\Domain\Ports\AuthCachePort;
use Modules\Auth\Domain\Ports\UserRepositoryPort;
use Shared\Infrastructure\Audit\AuditInterface;

/**
 * UpdateUserHandler — Handles user profile updates with PHP 8.5 pipe operator.
 */
final readonly class UpdateUserHandler
{
    public function __construct(
        private UserRepositoryPort $userRepository,
        private AuthCachePort $cache,
        private AuditInterface $audit,
    ) {
    }

    #[\NoDiscard]
    public function handle(UpdateUserCommand $command): User
    {
        return $command
            |> $this->findUser(...)
            |> $this->updateUser(...)
            |> $this->persistUser(...)
            |> $this->dispatchDomainEvents(...)
            |> $this->clearCache(...)
            |> $this->logAudit(...);
    }

    private function findUser(UpdateUserCommand $command): array
    {
        $user = $this->userRepository->findById($command->userId);

        if ($user === null) {
            throw UserNotFoundException::withIdentifier((string) $command->userId);
        }

        return ['user' => $user, 'command' => $command];
    }

    private function updateUser(array $data): array
    {
        $user = $data['user'];
        $command = $data['command'];

        $updatedUser = $user->updateProfile(
            name: $command->name,
            lastName: $command->lastName,
            phone: $command->phone,
            username: $command->username,
        );

        return ['user' => $updatedUser, 'originalUser' => $user];
    }

    private function persistUser(array $data): array
    {
        $user = $data['user'];

        $updateData = [
            'name' => $user->name,
            'last_name' => $user->lastName,
            'phone' => $user->phone,
            'username' => $user->username,
        ];

        return [
            'user' => $this->userRepository->update($user, $updateData),
            'events' => $user->pullDomainEvents(),
        ];
    }

    private function dispatchDomainEvents(array $data): User
    {
        foreach ($data['events'] as $event) {
            event($event);
        }

        return $data['user'];
    }

    private function clearCache(User $user): User
    {
        $this->cache->forget(AuthCacheKeys::userByUuid($user->uuid));
        $this->cache->forget(AuthCacheKeys::userById($user->id));
        $this->cache->flushTag(AuthCacheKeys::LIST_TAG);

        return $user;
    }

    private function logAudit(User $user): User
    {
        $this->audit->log(
            logName: 'auth.user_updated',
            description: "User updated profile interactively",
            properties: ['uuid' => $user->uuid],
        );

        return $user;
    }
}
