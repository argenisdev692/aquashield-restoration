<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Modules\Users\Infrastructure\Persistence\Mappers\UserMapper;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('maps the user eloquent model into the domain entity', function (): void {
    $userModel = UserEloquentModel::factory()->create([
        'status' => 'active',
        'city' => 'Miami',
        'state' => 'Florida',
        'country' => 'USA',
        'phone' => '5551234',
        'terms_and_conditions' => true,
    ]);

    $entity = UserMapper::toDomain($userModel->fresh());

    expect($entity->uuid)->toBe($userModel->uuid)
        ->and($entity->email)->toBe($userModel->email)
        ->and($entity->status->value)->toBe('active')
        ->and($entity->city)->toBe('Miami');
});
