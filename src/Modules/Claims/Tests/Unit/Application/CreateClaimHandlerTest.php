<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Tests\Unit\Application;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Src\Modules\Claims\Application\Commands\CreateClaimHandler;
use Src\Modules\Claims\Application\DTOs\StoreClaimData;
use Src\Modules\Claims\Domain\Ports\ClaimInternalIdGeneratorPort;
use Src\Modules\Claims\Domain\Ports\ClaimRepositoryPort;

final class CreateClaimHandlerTest extends TestCase
{
    private ClaimRepositoryPort&MockObject $repository;
    private ClaimInternalIdGeneratorPort&MockObject $idGenerator;
    private CreateClaimHandler $handler;

    protected function setUp(): void
    {
        $this->repository   = $this->createMock(ClaimRepositoryPort::class);
        $this->idGenerator  = $this->createMock(ClaimInternalIdGeneratorPort::class);
        $this->handler      = new CreateClaimHandler($this->repository, $this->idGenerator);
    }

    public function test_handle_saves_claim_and_returns_uuid(): void
    {
        $this->idGenerator->expects($this->once())
            ->method('nextId')
            ->willReturn('AQ-000001');

        $this->repository
            ->expects($this->once())
            ->method('save');

        $data = new StoreClaimData(
            propertyId: 1,
            signaturePathId: 1,
            typeDamageId: 2,
            userIdRefBy: 3,
            claimStatus: 1,
            policyNumber: 'POL-TEST-001',
        );

        $uuid = $this->handler->handle($data);

        $this->assertNotEmpty($uuid);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid,
        );
    }

    public function test_handle_uses_generated_internal_id(): void
    {
        $this->idGenerator->expects($this->once())
            ->method('nextId')
            ->willReturn('AQ-000042');

        $this->repository->expects($this->once())->method('save');

        $data = new StoreClaimData(
            propertyId: 1,
            signaturePathId: 2,
            typeDamageId: 3,
            userIdRefBy: 4,
            claimStatus: 1,
            policyNumber: 'POL-OPT-001',
            claimNumber: 'CLM-0042',
            dateOfLoss: '2024-08-01',
            numberOfFloors: 2,
            damageDescription: 'Roof damage due to hurricane.',
            customerReviewed: false,
        );

        $uuid = $this->handler->handle($data);

        $this->assertNotEmpty($uuid);
    }
}
