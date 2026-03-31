<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Tests\Integration;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;
use Src\Modules\ScopeSheets\Infrastructure\Persistence\Eloquent\Models\ScopeSheetEloquentModel;
use Src\Modules\ScopeSheets\Infrastructure\Persistence\Mappers\ScopeSheetMapper;

final class ScopeSheetMapperTest extends TestCase
{
    use RefreshDatabase;

    private ScopeSheetMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new ScopeSheetMapper();
    }

    public function test_to_domain_maps_eloquent_model_correctly(): void
    {
        $uuid = Uuid::uuid4()->toString();

        $model                          = new ScopeSheetEloquentModel();
        $model->uuid                    = $uuid;
        $model->claim_id                = 1;
        $model->generated_by            = 2;
        $model->scope_sheet_description = 'Integration test description';

        $model->setRawAttributes(array_merge($model->getAttributes(), [
            'created_at' => '2026-03-31 00:00:00',
            'updated_at' => '2026-03-31 00:00:00',
            'deleted_at' => null,
        ]));

        $model->exists = true;

        $domain = $this->mapper->toDomain($model);

        $this->assertSame($uuid, $domain->id()->toString());
        $this->assertSame(1, $domain->claimId());
        $this->assertSame(2, $domain->generatedBy());
        $this->assertSame('Integration test description', $domain->scopeSheetDescription());
        $this->assertNull($domain->deletedAt());
    }

    public function test_to_eloquent_maps_domain_to_model_correctly(): void
    {
        $uuid = Uuid::uuid4()->toString();

        $model                          = new ScopeSheetEloquentModel();
        $model->uuid                    = $uuid;
        $model->claim_id                = 3;
        $model->generated_by            = 4;
        $model->scope_sheet_description = 'Round-trip test';
        $model->setRawAttributes(array_merge($model->getAttributes(), [
            'created_at' => '2026-03-31 00:00:00',
            'updated_at' => '2026-03-31 00:00:00',
            'deleted_at' => null,
        ]));
        $model->exists = true;

        $domain   = $this->mapper->toDomain($model);
        $reModel  = $this->mapper->toEloquent($domain);

        $this->assertSame($uuid, $reModel->uuid);
        $this->assertSame(3, (int) $reModel->claim_id);
        $this->assertSame(4, (int) $reModel->generated_by);
        $this->assertSame('Round-trip test', $reModel->scope_sheet_description);
    }
}
