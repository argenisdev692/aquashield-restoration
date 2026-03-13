<?php

declare(strict_types=1);

namespace Modules\Users\Application\Commands\UpdateUser;

use Modules\Users\Application\Support\UserCacheKeys;
use Modules\Users\Domain\Entities\User;
use Modules\Users\Domain\Exceptions\UserNotFoundException;
use Modules\Users\Domain\Ports\UserAuditPort;
use Modules\Users\Domain\Ports\UserCachePort;
use Modules\Users\Domain\Ports\UserPhoneNormalizerPort;
use Modules\Users\Domain\Ports\UserRepositoryPort;

/**
 * UpdateUserHandler — Validates user existence, then delegates update to the repository.
 */
final readonly class UpdateUserHandler
{
    public function __construct(
        private UserRepositoryPort $repository,
        private UserAuditPort $audit,
        private UserCachePort $cache,
        private UserPhoneNormalizerPort $phoneNormalizer,
    ) {
    }

    public function handle(UpdateUserCommand $command): User
    {
        $existing = $this->repository->findByUuid($command->uuid);

        if ($existing === null) {
            throw UserNotFoundException::forUuid($command->uuid);
        }

        $payload = array_filter([
            'name' => $command->dto->name,
            'last_name' => $command->dto->lastName,
            'email' => $command->dto->email,
            'username' => $command->dto->username,
            'phone' => $this->phoneNormalizer->normalize($command->dto->phone),
            'address' => $command->dto->address,
            'address_2' => $command->dto->address2,
            'city' => $command->dto->city,
            'state' => $command->dto->state,
            'country' => $command->dto->country,
            'zip_code' => $command->dto->zipCode,
            'status' => $command->dto->status?->value,
            'role' => $command->dto->role,
        ], static fn (mixed $value): bool => $value !== null);

        $user = $this->repository->update($command->uuid, $payload);

        $this->cache->forget(UserCacheKeys::user($command->uuid));
        $this->cache->flushTag(UserCacheKeys::LIST_TAG);

        $this->audit->log(
            logName: 'users.updated',
            description: 'user.updated',
            properties: [
                'uuid' => $command->uuid,
                'changed_fields' => array_keys($payload),
            ],
        );

        return $user;
    }
}
