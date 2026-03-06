<?php

declare(strict_types=1);

namespace Modules\Users\Application\Commands\BulkDeleteUsers;

use Modules\Users\Application\Commands\DeleteUser\DeleteUserCommand;
use Modules\Users\Application\Commands\DeleteUser\DeleteUserHandler;

final readonly class BulkDeleteUsersHandler
{
    public function __construct(
        private DeleteUserHandler $deleteHandler,
    ) {
    }

    public function handle(BulkDeleteUsersCommand $command): int
    {
        $deletedCount = 0;

        foreach ($command->uuids as $uuid) {
            $this->deleteHandler->handle(new DeleteUserCommand($uuid));
            $deletedCount++;
        }

        return $deletedCount;
    }
}
