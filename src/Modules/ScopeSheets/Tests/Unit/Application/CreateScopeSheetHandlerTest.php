<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Tests\Unit\Application;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Src\Modules\ScopeSheets\Application\Commands\CreateScopeSheetHandler;
use Src\Modules\ScopeSheets\Application\DTOs\StoreScopeSheetData;
use Src\Modules\ScopeSheets\Domain\Entities\ScopeSheet;
use Src\Modules\ScopeSheets\Domain\Ports\ScopeSheetRepositoryPort;

final class CreateScopeSheetHandlerTest extends TestCase
{
    private ScopeSheetRepositoryPort&MockObject $repository;
    private CreateScopeSheetHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ScopeSheetRepositoryPort::class);
        $this->handler    = new CreateScopeSheetHandler($this->repository);
    }

    public function test_handle_saves_scope_sheet_and_returns_uuid(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(ScopeSheet::class));

        $this->repository
            ->expects($this->once())
            ->method('syncRelations')
            ->with(
                $this->isType('string'),
                $this->equalTo([]),
                $this->equalTo([]),
            );

        $data = StoreScopeSheetData::from([
            'claimId'               => 1,
            'generatedBy'           => 2,
            'scopeSheetDescription' => 'Test sheet',
            'presentations'         => [],
            'zones'                 => [],
        ]);

        $uuid = $this->handler->handle($data);

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid,
        );
    }

    public function test_handle_passes_presentations_and_zones_to_sync(): void
    {
        $presentations = [
            ['photo_type' => 'front', 'photo_path' => 'front.jpg', 'photo_order' => 0],
        ];

        $zones = [
            ['zone_id' => 3, 'zone_order' => 0, 'zone_notes' => null, 'photos' => []],
        ];

        $this->repository->expects($this->once())->method('save');

        $this->repository
            ->expects($this->once())
            ->method('syncRelations')
            ->with(
                $this->isType('string'),
                $this->equalTo($presentations),
                $this->equalTo($zones),
            );

        $data = StoreScopeSheetData::from([
            'claimId'               => 1,
            'generatedBy'           => 2,
            'scopeSheetDescription' => null,
            'presentations'         => $presentations,
            'zones'                 => $zones,
        ]);

        $this->handler->handle($data);
    }
}
