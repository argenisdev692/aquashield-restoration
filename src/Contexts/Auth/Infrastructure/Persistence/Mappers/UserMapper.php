<?php

declare(strict_types=1);

namespace Src\Contexts\Auth\Infrastructure\Persistence\Mappers;

use Src\Contexts\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Src\Contexts\Auth\Domain\Entities\User;

final class UserMapper
{
    public static function toDomain(UserEloquentModel $eloquent): User
    {
        return new User(
            id: $eloquent->id,
            uuid: $eloquent->uuid,
            name: $eloquent->name,
            lastName: $eloquent->last_name,
            email: $eloquent->email,
            username: $eloquent->username,
            profilePhotoPath: $eloquent->profile_photo_path,
            phone: $eloquent->phone,
            isEmailVerified: $eloquent->email_verified_at !== null,
        );
    }
}
