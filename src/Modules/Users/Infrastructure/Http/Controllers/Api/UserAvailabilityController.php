<?php

declare(strict_types=1);

namespace Modules\Users\Infrastructure\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Users\Domain\Ports\UserRepositoryPort;
use Modules\Users\Infrastructure\Http\Requests\CheckUserAvailabilityRequest;

final readonly class UserAvailabilityController
{
    public function __construct(
        private UserRepositoryPort $repository,
    ) {
    }

    public function admin(CheckUserAvailabilityRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->respond(
            field: (string) $validated['field'],
            value: (string) $validated['value'],
            ignoreUuid: isset($validated['ignore_uuid']) ? (string) $validated['ignore_uuid'] : null,
        );
    }

    public function profile(CheckUserAvailabilityRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->respond(
            field: (string) $validated['field'],
            value: (string) $validated['value'],
            ignoreUuid: $request->user()?->uuid,
        );
    }

    private function respond(string $field, string $value, ?string $ignoreUuid): JsonResponse
    {
        $exists = match ($field) {
            'email' => $this->repository->existsByEmail($value, $ignoreUuid),
            'username' => $this->repository->existsByUsername($value, $ignoreUuid),
            'phone' => $this->repository->existsByPhone($value, $ignoreUuid),
        };

        return response()->json([
            'data' => [
                'field' => $field,
                'available' => ! $exists,
            ],
        ]);
    }
}
