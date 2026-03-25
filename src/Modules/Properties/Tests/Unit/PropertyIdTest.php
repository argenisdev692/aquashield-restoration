<?php

declare(strict_types=1);

namespace Src\Modules\Properties\Tests\Unit;

use InvalidArgumentException;
use Src\Modules\Properties\Domain\ValueObjects\PropertyId;
use Tests\TestCase;

uses(TestCase::class);

it('generates a valid UUID', function (): void {
    $id = PropertyId::generate();

    expect($id->toString())->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i');
});

it('creates from a valid UUID string', function (): void {
    $uuid = '550e8400-e29b-41d4-a716-446655440000';
    $id   = PropertyId::fromString($uuid);

    expect($id->toString())->toBe($uuid);
});

it('throws on an invalid UUID string', function (): void {
    expect(fn () => PropertyId::fromString('not-a-uuid'))
        ->toThrow(InvalidArgumentException::class, 'Invalid property UUID.');
});

it('two IDs from the same string are equal', function (): void {
    $uuid = '550e8400-e29b-41d4-a716-446655440000';

    expect(PropertyId::fromString($uuid)->toString())
        ->toBe(PropertyId::fromString($uuid)->toString());
});
