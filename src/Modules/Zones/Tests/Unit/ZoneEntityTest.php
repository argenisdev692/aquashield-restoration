<?php

declare(strict_types=1);

use Src\Modules\Zones\Domain\Entities\Zone;
use Src\Modules\Zones\Domain\ValueObjects\ZoneId;

it('creates a zone with valid data', function (): void {
    $id   = ZoneId::generate();
    $zone = Zone::create(
        id: $id,
        zoneName: '  Living Room  ',
        zoneType: 'interior',
        code: ' lr-01 ',
        description: 'Main area',
        userId: 1,
        createdAt: '2026-01-01T00:00:00+00:00',
    );

    expect($zone->zoneName())->toBe('Living Room')
        ->and($zone->zoneType())->toBe('interior')
        ->and($zone->code())->toBe('LR-01')
        ->and($zone->description())->toBe('Main area')
        ->and($zone->userId())->toBe(1)
        ->and($zone->deletedAt())->toBeNull();
});

it('normalizes zone type to lowercase', function (): void {
    $zone = Zone::create(
        id: ZoneId::generate(),
        zoneName: 'Garage Zone',
        zoneType: 'GARAGE',
        code: null,
        description: null,
        userId: 1,
        createdAt: '2026-01-01T00:00:00+00:00',
    );

    expect($zone->zoneType())->toBe('garage');
});

it('normalizes code to uppercase and trims whitespace', function (): void {
    $zone = Zone::create(
        id: ZoneId::generate(),
        zoneName: 'Attic',
        zoneType: 'attic',
        code: '  at-99  ',
        description: null,
        userId: 1,
        createdAt: '2026-01-01T00:00:00+00:00',
    );

    expect($zone->code())->toBe('AT-99');
});

it('stores null when code is empty string', function (): void {
    $zone = Zone::create(
        id: ZoneId::generate(),
        zoneName: 'Basement',
        zoneType: 'basement',
        code: '   ',
        description: null,
        userId: 1,
        createdAt: '2026-01-01T00:00:00+00:00',
    );

    expect($zone->code())->toBeNull();
});

it('throws when zone name is empty', function (): void {
    Zone::create(
        id: ZoneId::generate(),
        zoneName: '   ',
        zoneType: 'interior',
        code: null,
        description: null,
        userId: 1,
        createdAt: '2026-01-01T00:00:00+00:00',
    );
})->throws(InvalidArgumentException::class, 'Zone name is required.');

it('throws when zone type is invalid', function (): void {
    Zone::create(
        id: ZoneId::generate(),
        zoneName: 'Kitchen',
        zoneType: 'rooftop',
        code: null,
        description: null,
        userId: 1,
        createdAt: '2026-01-01T00:00:00+00:00',
    );
})->throws(InvalidArgumentException::class, 'Invalid zone type.');

it('reconstitutes a zone with deleted_at set', function (): void {
    $id   = ZoneId::generate();
    $zone = Zone::reconstitute(
        id: $id,
        zoneName: 'Crawlspace',
        zoneType: 'crawlspace',
        code: null,
        description: null,
        userId: 2,
        createdAt: '2026-01-01T00:00:00+00:00',
        updatedAt: '2026-01-10T00:00:00+00:00',
        deletedAt: '2026-02-01T00:00:00+00:00',
    );

    expect($zone->deletedAt())->toBe('2026-02-01T00:00:00+00:00');
});

it('updates zone fields correctly', function (): void {
    $zone = Zone::create(
        id: ZoneId::generate(),
        zoneName: 'Old Name',
        zoneType: 'interior',
        code: null,
        description: null,
        userId: 1,
        createdAt: '2026-01-01T00:00:00+00:00',
    );

    $zone->update(
        zoneName: 'New Name',
        zoneType: 'exterior',
        code: 'EX-01',
        description: 'Updated desc',
        userId: 2,
        updatedAt: '2026-03-01T00:00:00+00:00',
    );

    expect($zone->zoneName())->toBe('New Name')
        ->and($zone->zoneType())->toBe('exterior')
        ->and($zone->code())->toBe('EX-01')
        ->and($zone->description())->toBe('Updated desc')
        ->and($zone->userId())->toBe(2);
});
