<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\AccessControl\Infrastructure\Persistence\Eloquent\Models\PermissionEloquentModel;
use Modules\AccessControl\Infrastructure\Persistence\Eloquent\Models\RoleEloquentModel;
use Modules\EmailData\Infrastructure\Persistence\Eloquent\Models\EmailDataEloquentModel;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;

uses(RefreshDatabase::class);

function createEmailDataAdmin(): UserEloquentModel
{
    $permissions = [
        'CREATE_EMAIL_DATA',
        'READ_EMAIL_DATA',
        'UPDATE_EMAIL_DATA',
        'DELETE_EMAIL_DATA',
        'RESTORE_EMAIL_DATA',
    ];

    foreach ($permissions as $permissionName) {
        PermissionEloquentModel::query()->firstOrCreate([
            'name' => $permissionName,
            'guard_name' => 'web',
        ]);
    }

    $role = RoleEloquentModel::query()->firstOrCreate([
        'name' => 'SUPER_ADMIN',
        'guard_name' => 'web',
    ]);

    $role->syncPermissions($permissions);

    $user = UserEloquentModel::factory()->create();
    $user->assignRole($role);

    return $user;
}

it('lists, creates, shows, updates, deletes and restores email data records', function (): void {
    $admin = createEmailDataAdmin();

    $this->actingAs($admin)
        ->getJson('/email-data/data/admin')
        ->assertOk()
        ->assertJsonStructure([
            'data',
            'meta' => ['total', 'per_page', 'current_page', 'last_page'],
        ]);

    $storeResponse = $this->actingAs($admin)
        ->postJson('/email-data/data/admin', [
            'description' => 'Main intake inbox',
            'email' => 'intake@aquashield.test',
            'phone' => '+15551234567',
            'type' => 'Intake',
        ])
        ->assertCreated()
        ->assertJsonStructure(['uuid', 'message']);

    $uuid = (string) $storeResponse->json('uuid');

    $this->actingAs($admin)
        ->getJson("/email-data/data/admin/{$uuid}")
        ->assertOk()
        ->assertJsonPath('email', 'intake@aquashield.test')
        ->assertJsonPath('type', 'Intake')
        ->assertJsonPath('user_id', $admin->id);

    $this->actingAs($admin)
        ->putJson("/email-data/data/admin/{$uuid}", [
            'description' => 'Updated support inbox',
            'email' => 'support@aquashield.test',
            'phone' => '+15557654321',
            'type' => 'Support',
        ])
        ->assertOk()
        ->assertJson(['message' => 'Email data updated successfully.']);

    expect(EmailDataEloquentModel::query()->where('uuid', $uuid)->value('email'))->toBe('support@aquashield.test');

    $this->actingAs($admin)
        ->deleteJson("/email-data/data/admin/{$uuid}")
        ->assertOk()
        ->assertJson(['message' => 'Email data deleted successfully.']);

    expect(EmailDataEloquentModel::withTrashed()->where('uuid', $uuid)->first()?->deleted_at)->not->toBeNull();

    $this->actingAs($admin)
        ->patchJson("/email-data/data/admin/{$uuid}/restore")
        ->assertOk()
        ->assertJson(['message' => 'Email data restored successfully.']);

    expect(EmailDataEloquentModel::query()->where('uuid', $uuid)->value('deleted_at'))->toBeNull();
});

it('exports email data records to excel and pdf', function (): void {
    $admin = createEmailDataAdmin();

    EmailDataEloquentModel::query()->create([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'description' => 'Export email',
        'email' => 'export@aquashield.test',
        'phone' => '+15550001111',
        'type' => 'Export',
        'user_id' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->get('/email-data/data/admin/export?format=excel')
        ->assertOk()
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $this->actingAs($admin)
        ->get('/email-data/data/admin/export?format=pdf')
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('bulk deletes selected email data records', function (): void {
    $admin = createEmailDataAdmin();

    $records = collect(range(1, 2))->map(function (int $index) use ($admin): EmailDataEloquentModel {
        return EmailDataEloquentModel::query()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'description' => "Email {$index}",
            'email' => "bulk{$index}@aquashield.test",
            'phone' => null,
            'type' => 'Bulk',
            'user_id' => $admin->id,
        ]);
    });

    $this->actingAs($admin)
        ->postJson('/email-data/data/admin/bulk-delete', [
            'uuids' => $records->pluck('uuid')->all(),
        ])
        ->assertOk()
        ->assertJsonPath('deleted_count', 2);

    expect(EmailDataEloquentModel::withTrashed()->whereIn('uuid', $records->pluck('uuid')->all())->whereNotNull('deleted_at')->count())->toBe(2);
});
