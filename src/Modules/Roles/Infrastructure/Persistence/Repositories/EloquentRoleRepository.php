<?php

declare(strict_types=1);

namespace Modules\Roles\Infrastructure\Persistence\Repositories;

use Illuminate\Database\Eloquent\Model;
use Modules\Roles\Domain\Ports\RoleRepositoryPort;

final class EloquentRoleRepository implements RoleRepositoryPort
{
    private const DEFAULT_GUARD = 'web';

    public function paginate(array $filters = []): array
    {
        $query = $this->newRoleQuery()
            ->withTrashed()
            ->withCount('permissions')
            ->with('permissions:id,name')
            ->where('guard_name', self::DEFAULT_GUARD)
            ->when(
                $filters['search'] ?? null,
                static fn ($builder, $search) => $builder->where('name', 'like', '%' . trim((string) $search) . '%'),
            )
            ->orderBy($filters['sortBy'] ?? 'name', $filters['sortDir'] ?? 'asc');

        $paginator = $query->paginate(
            perPage: (int) ($filters['perPage'] ?? 15),
            page: (int) ($filters['page'] ?? 1),
        );

        return [
            'data' => array_map($this->mapRole(...), $paginator->items()),
            'total' => $paginator->total(),
            'perPage' => $paginator->perPage(),
            'currentPage' => $paginator->currentPage(),
            'lastPage' => $paginator->lastPage(),
        ];
    }

    public function findByUuid(string $uuid): ?array
    {
        $role = $this->newRoleQuery()
            ->withTrashed()
            ->withCount('permissions')
            ->with('permissions:id,name')
            ->where('guard_name', self::DEFAULT_GUARD)
            ->where('uuid', $uuid)
            ->first();

        return $role instanceof Model ? $this->mapRole($role) : null;
    }

    public function create(array $data): array
    {
        $role = $this->newRoleQuery()->create([
            'name' => (string) $data['name'],
            'guard_name' => self::DEFAULT_GUARD,
        ]);

        $role->loadCount('permissions')->load('permissions:id,name');

        return $this->mapRole($role);
    }

    public function update(string $uuid, array $data): array
    {
        $role = $this->findRoleModelOrFail($uuid);
        $role->update([
            'name' => (string) $data['name'],
        ]);
        $role->refresh()->loadCount('permissions')->load('permissions:id,name');

        return $this->mapRole($role);
    }

    public function delete(string $uuid): void
    {
        $this->findRoleModelOrFail($uuid)->delete();
    }

    public function restore(string $uuid): void
    {
        $role = $this->findRoleModelOrFail($uuid, true);
        $role->restore();
    }

    private function findRoleModelOrFail(string $uuid, bool $withTrashed = false): Model
    {
        $query = $this->newRoleQuery()->where('guard_name', self::DEFAULT_GUARD)->where('uuid', $uuid);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->firstOrFail();
    }

    private function newRoleQuery()
    {
        $roleModelClass = (string) config('permission.models.role');

        return $roleModelClass::query();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapRole(Model $role): array
    {
        /** @var list<string> $permissionNames */
        $permissionNames = $role->relationLoaded('permissions')
            ? $role->permissions->pluck('name')->values()->all()
            : [];

        return [
            'id' => $role->getAttribute('id'),
            'uuid' => $role->getAttribute('uuid'),
            'name' => $role->getAttribute('name'),
            'guard_name' => $role->getAttribute('guard_name'),
            'permissions_count' => (int) ($role->getAttribute('permissions_count') ?? count($permissionNames)),
            'permission_names' => $permissionNames,
            'created_at' => $role->getAttribute('created_at')?->toIso8601String(),
            'updated_at' => $role->getAttribute('updated_at')?->toIso8601String(),
            'deleted_at' => $role->getAttribute('deleted_at')?->toIso8601String(),
        ];
    }
}
