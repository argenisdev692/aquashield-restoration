<?php

declare(strict_types=1);

use Modules\Users\Domain\Entities\User;
use Modules\Users\Domain\Enums\UserStatus;
use Modules\Users\Domain\ValueObjects\UserId;

it('updates the user status through clone-based domain transitions', function (): void {
    $user = new User(
        id: new UserId(1),
        uuid: 'user-uuid',
        name: 'Jane',
        lastName: 'Doe',
        email: 'jane@example.com',
        status: UserStatus::Active,
    );

    $suspendedUser = $user->suspend();
    $deletedUser = $user->softDelete();
    $reactivatedUser = $deletedUser->activate();

    expect($suspendedUser->status)->toBe(UserStatus::Suspended)
        ->and($deletedUser->status)->toBe(UserStatus::Deleted)
        ->and($deletedUser->deletedAt)->not->toBeNull()
        ->and($reactivatedUser->status)->toBe(UserStatus::Active)
        ->and($reactivatedUser->deletedAt)->toBeNull()
        ->and($user->status)->toBe(UserStatus::Active);
});

it('returns the full name as a single string', function (): void {
    $user = new User(
        id: new UserId(2),
        uuid: 'user-full-name',
        name: 'John',
        lastName: 'Smith',
        email: 'john@example.com',
    );

    expect($user->fullName())->toBe('John Smith');
});
