<?php

declare(strict_types=1);

namespace Src\Contexts\Users\Application\Commands\CreateUser;

use Illuminate\Support\Str;
use Src\Contexts\Users\Domain\Entities\User;
use Src\Contexts\Users\Domain\Ports\UserRepositoryPort;
use Illuminate\Support\Facades\Cache;

final readonly class CreateUserHandler
{
    public function __construct(
        private UserRepositoryPort $userRepository,
    ) {
    }

    public function handle(CreateUserCommand $command): User
    {
        $dto = $command->dto;

        $user = $this->userRepository->create([
            'uuid' => Str::uuid()->toString(),
            'name' => $dto->name,
            'last_name' => $dto->lastName,
            'email' => $dto->email,
            'username' => $dto->username,
            'phone' => $dto->phone,
            'address' => $dto->address,
            'city' => $dto->city,
            'state' => $dto->state,
            'country' => $dto->country,
            'zip_code' => $dto->zipCode,
            'password' => $dto->password ? bcrypt($dto->password) : null,
        ]);

        // Since we don't know the exact pagination caches to clear,
        // ideally we would use Cache tags, but standard Cache::forget works if we know keys.
        // For lists, it's safer to clear the known related caches or rely on TTL.
        // Cache::forget("users_list_*"); // Needs redis tags, skipping pattern clear for standard driver

        return $user;
    }
}
