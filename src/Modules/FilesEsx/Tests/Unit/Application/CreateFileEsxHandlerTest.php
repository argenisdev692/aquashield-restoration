<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Tests\Unit\Application;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Src\Modules\FilesEsx\Application\Commands\CreateFileEsxHandler;
use Src\Modules\FilesEsx\Application\DTOs\StoreFileEsxData;
use Src\Modules\FilesEsx\Domain\Entities\FileEsx;
use Src\Modules\FilesEsx\Domain\Ports\FileEsxRepositoryPort;
use Tests\TestCase;

final class CreateFileEsxHandlerTest extends TestCase
{
    private FileEsxRepositoryPort&MockObject $repository;
    private CreateFileEsxHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(FileEsxRepositoryPort::class);
        $this->handler    = new CreateFileEsxHandler($this->repository);
    }

    #[Test]
    public function it_creates_a_file_esx_and_returns_uuid(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(FileEsx::class));

        $data = new StoreFileEsxData(
            fileName: 'test-document.pdf',
            filePath: 'uploads/test-document.pdf',
            uploadedBy: 42,
        );

        $uuid = $this->handler->handle($data);

        $this->assertNotEmpty($uuid);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $uuid,
        );
    }

    #[Test]
    public function it_creates_file_esx_with_null_file_name(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('save');

        $data = new StoreFileEsxData(
            fileName: null,
            filePath: 'uploads/unnamed.pdf',
            uploadedBy: 1,
        );

        $uuid = $this->handler->handle($data);

        $this->assertNotEmpty($uuid);
    }
}
