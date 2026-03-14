<?php

declare(strict_types=1);

use Modules\CompanyData\Domain\ValueObjects\Coordinates;

it('accepts valid coordinate values', function (): void {
    $coordinates = new Coordinates(25.7617, -80.1918);

    expect($coordinates->latitude)->toBe(25.7617)
        ->and($coordinates->longitude)->toBe(-80.1918)
        ->and($coordinates->hasValues())->toBeTrue()
        ->and($coordinates->toArray())->toBe([
            'latitude' => 25.7617,
            'longitude' => -80.1918,
        ]);
});

it('rejects invalid coordinate boundaries', function (): void {
    expect(fn() => new Coordinates(90.1, 0.0))->toThrow(\InvalidArgumentException::class)
        ->and(fn() => new Coordinates(0.0, 180.1))->toThrow(\InvalidArgumentException::class);
});
