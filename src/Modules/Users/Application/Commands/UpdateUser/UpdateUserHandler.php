<?php

declare(strict_types=1);

namespace Modules\Users\Application\Commands\UpdateUser;

use Illuminate\Support\Facades\Cache;
use Modules\Users\Domain\Entities\User;
use Modules\Users\Domain\Exceptions\UserNotFoundException;
use Modules\Users\Domain\Ports\UserRepositoryPort;
use Shared\Infrastructure\Audit\AuditInterface;
use Shared\Infrastructure\Utils\PhoneHelper;

/**
 * UpdateUserHandler — Validates user existence, then delegates update to the repository.
 */
final readonly class UpdateUserHandler
{
    public function __construct(
        private UserRepositoryPort $repository,
        private AuditInterface $audit,
    ) {
    }

    public function handle(UpdateUserCommand $command): User
    {
        $existing = $this->repository->findByUuid($command->uuid);

        if ($existing === null) {
            throw UserNotFoundException::withUuid($command->uuid);
        }

        $payload = array_filter([
            'name' => $command->dto->name,
            'last_name' => $command->dto->lastName,
            'email' => $command->dto->email,
            'username' => $command->dto->username,
            'phone' => PhoneHelper::normalizeUs($command->dto->phone),
            'address' => $command->dto->address,
            'address_2' => $command->dto->address2,
            'city' => $command->dto->city,
            'state' => $command->dto->state,
            'country' => $command->dto->country,
            'zip_code' => $command->dto->zipCode,
            'status' => $command->dto->status?->value,
        ], static fn (mixed $value): bool => $value !== null);

        $user = $this->repository->update($command->uuid, $payload);

        Cache::forget("user_{$command->uuid}");

        try {
            Cache::tags(['users_list'])->flush();
        } catch (\Exception) {
        }

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
