<?php

declare(strict_types=1);

use Mockery\MockInterface;
use Modules\Users\Application\Commands\CreateUser\CreateUserCommand;
use Modules\Users\Application\Commands\CreateUser\CreateUserHandler;
use Modules\Users\Application\DTOs\CreateUserDTO;
use Modules\Users\Domain\Entities\User;
use Modules\Users\Domain\Enums\UserStatus;
use Modules\Users\Domain\Ports\UserRepositoryPort;
use Modules\Users\Domain\ValueObjects\UserId;
use Shared\Infrastructure\Audit\AuditInterface;
use Tests\TestCase;

uses(TestCase::class);

it('creates a user and records the audit entry', function (): void {
    $dto = new CreateUserDTO(
        name: 'Jane',
        email: 'jane@example.com',
        lastName: 'Doe',
    );

    $expectedUser = new User(
        id: new UserId(1),
        uuid: 'generated-uuid',
        name: 'Jane',
        lastName: 'Doe',
        email: 'jane@example.com',
        status: UserStatus::PendingSetup,
    );

    /** @var UserRepositoryPort&MockInterface $repository */
    $repository = Mockery::mock(UserRepositoryPort::class);
    $repository->shouldReceive('create')
        ->once()
        ->with(Mockery::on(static function (array $payload): bool {
            return isset($payload['uuid'], $payload['setup_token'], $payload['setup_token_expires_at'])
                && $payload['name'] === 'Jane'
                && $payload['last_name'] === 'Doe'
                && $payload['email'] === 'jane@example.com'
                && $payload['status'] === UserStatus::PendingSetup->value;
        }))
        ->andReturn($expectedUser);

    /** @var AuditInterface&MockInterface $audit */
    $audit = Mockery::mock(AuditInterface::class);
    $audit->shouldReceive('log')
        ->once()
        ->with('users.created', 'user.created', Mockery::type('array'));

    $handler = new CreateUserHandler($repository, $audit);

    $user = $handler->handle(new CreateUserCommand($dto));

    expect($user->email)->toBe('jane@example.com')
        ->and($user->status)->toBe(UserStatus::PendingSetup);
});
