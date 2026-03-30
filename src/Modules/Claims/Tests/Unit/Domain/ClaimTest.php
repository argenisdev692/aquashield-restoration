<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Tests\Unit\Domain;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Src\Modules\Claims\Domain\Entities\Claim;
use Src\Modules\Claims\Domain\ValueObjects\ClaimId;

final class ClaimTest extends TestCase
{
    public function test_create_claim_with_valid_data(): void
    {
        $claim = Claim::create(
            id: ClaimId::generate(),
            propertyId: 1,
            signaturePathId: 1,
            typeDamageId: 2,
            userIdRefBy: 3,
            claimStatusId: 1,
            claimInternalId: 'AQ-000001',
            policyNumber: 'POL-999',
            createdAt: now()->toIso8601String(),
        );

        $this->assertSame('AQ-000001', $claim->claimInternalId());
        $this->assertSame('POL-999', $claim->policyNumber());
        $this->assertNull($claim->deletedAt());
    }

    public function test_create_fails_with_empty_claim_internal_id(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Claim::create(
            id: ClaimId::generate(),
            propertyId: 1,
            signaturePathId: 1,
            typeDamageId: 2,
            userIdRefBy: 3,
            claimStatusId: 1,
            claimInternalId: '   ',
            policyNumber: 'POL-999',
            createdAt: now()->toIso8601String(),
        );
    }

    public function test_create_fails_with_empty_policy_number(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Claim::create(
            id: ClaimId::generate(),
            propertyId: 1,
            signaturePathId: 1,
            typeDamageId: 2,
            userIdRefBy: 3,
            claimStatusId: 1,
            claimInternalId: 'AQ-000001',
            policyNumber: '',
            createdAt: now()->toIso8601String(),
        );
    }

    public function test_update_modifies_fields(): void
    {
        $claim = Claim::create(
            id: ClaimId::generate(),
            propertyId: 1,
            signaturePathId: 1,
            typeDamageId: 2,
            userIdRefBy: 3,
            claimStatusId: 1,
            claimInternalId: 'AQ-000001',
            policyNumber: 'POL-001',
            createdAt: now()->toIso8601String(),
        );

        $claim->update(
            propertyId: 2,
            signaturePathId: 1,
            typeDamageId: 3,
            userIdRefBy: 4,
            claimStatusId: 2,
            policyNumber: 'POL-001-UPDATED',
            updatedAt: now()->toIso8601String(),
            damageDescription: 'Water damage on roof',
        );

        $this->assertSame('AQ-000001', $claim->claimInternalId());
        $this->assertSame('Water damage on roof', $claim->damageDescription());
        $this->assertSame(2, $claim->propertyId());
    }

    public function test_claim_id_generates_unique_values(): void
    {
        $id1 = ClaimId::generate();
        $id2 = ClaimId::generate();

        $this->assertFalse($id1->equals($id2));
    }

    public function test_claim_id_from_string_round_trip(): void
    {
        $original = ClaimId::generate();
        $restored = ClaimId::fromString($original->toString());

        $this->assertTrue($original->equals($restored));
    }
}
