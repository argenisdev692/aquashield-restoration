<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Tests\Integration;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Src\Modules\Claims\Domain\Entities\Claim;
use Src\Modules\Claims\Domain\ValueObjects\ClaimId;
use Src\Modules\Claims\Infrastructure\Persistence\Eloquent\Models\ClaimEloquentModel;
use Src\Modules\Claims\Infrastructure\Persistence\Mappers\ClaimMapper;

final class ClaimMapperTest extends TestCase
{
    use RefreshDatabase;

    private ClaimMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new ClaimMapper();
    }

    public function test_to_eloquent_and_back_preserves_fields(): void
    {
        $claim = Claim::create(
            id: ClaimId::generate(),
            propertyId: 1,
            signaturePathId: 1,
            typeDamageId: 1,
            userIdRefBy: 1,
            claimStatusId: 1,
            claimInternalId: 'CLM-INTG-001',
            policyNumber: 'POL-INTG-001',
            createdAt: now()->toIso8601String(),
            claimNumber: 'CLM-0099',
            dateOfLoss: '2024-09-15',
            numberOfFloors: 3,
            damageDescription: 'Integration test damage.',
            customerReviewed: true,
        );

        $eloquent = $this->mapper->toEloquent($claim);
        $restored = $this->mapper->toDomain($eloquent);

        $this->assertSame($claim->id()->toString(), $restored->id()->toString());
        $this->assertSame('CLM-INTG-001', $restored->claimInternalId());
        $this->assertSame('POL-INTG-001', $restored->policyNumber());
        $this->assertSame('CLM-0099', $restored->claimNumber());
        $this->assertSame(3, $restored->numberOfFloors());
        $this->assertTrue($restored->customerReviewed());
    }
}
