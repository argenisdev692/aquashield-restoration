<?php

declare(strict_types=1);

namespace Modules\Users\Application\Commands\CreateUser;

use Modules\Users\Application\Support\UserCacheKeys;
use Modules\Users\Domain\Entities\User;
use Modules\Users\Domain\Enums\UserStatus;
use Modules\Users\Domain\Events\UserCreatedByAdmin;
use Modules\Users\Domain\Ports\UserAuditPort;
use Modules\Users\Domain\Ports\UserCachePort;
use Modules\Users\Domain\Ports\UserPhoneNormalizerPort;
use Modules\Users\Domain\Ports\UserRepositoryPort;
use Ramsey\Uuid\Uuid;
use Shared\Domain\Events\DomainEventPublisher;

final readonly class CreateUserHandler
{
    public function __construct(
        private UserRepositoryPort $userRepository,
        private UserAuditPort $audit,
        private UserCachePort $cache,
        private UserPhoneNormalizerPort $phoneNormalizer,
    ) {
    }

    public function handle(CreateUserCommand $command): User
    {
        $dto = $command->dto;
        $uuid = Uuid::uuid7()->toString();
        $setupToken = bin2hex(random_bytes(30));

        $user = $this->userRepository->create([
            'uuid' => $uuid,
            'name' => $dto->name,
            'last_name' => $dto->lastName,
            'email' => $dto->email,
            'username' => $dto->username,
            'phone' => $this->phoneNormalizer->normalize($dto->phone),
            'address' => $dto->address,
            'address_2' => $dto->address2,
            'city' => $dto->city,
            'state' => $dto->state,
            'country' => $dto->country,
            'zip_code' => $dto->zipCode,
            'status' => UserStatus::PendingSetup->value,
            'setup_token' => $setupToken,
            'setup_token_expires_at' => now()->addDays(7)->toDateTimeString(),
            'role' => $dto->role,
        ]);

        // Dispatch domain event
        DomainEventPublisher::instance()->publish(
            new UserCreatedByAdmin(
                aggregateId: $uuid,
                email: $dto->email,
                setupToken: $setupToken,
                occurredOn: now()->toDateTimeString()
            )
        );

        $this->cache->flushTag(UserCacheKeys::LIST_TAG);

        $this->audit->log(
            logName: 'users.created',
            description: 'user.created',
            properties: [
                'uuid' => $uuid,
            ],
        );

        return $user;
    }
}
