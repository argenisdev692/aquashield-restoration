<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Tests\Unit\Application;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Src\Modules\FilesEsx\Application\Commands\UpdateFileEsxHandler;
use Src\Modules\FilesEsx\Application\DTOs\UpdateFileEsxData;
use Src\Modules\FilesEsx\Domain\Entities\FileEsx;
use Src\Modules\FilesEsx\Domain\Exceptions\FileEsxNotFoundException;
use Src\Modules\FilesEsx\Domain\Ports\FileEsxRepositoryPort;
use Src\Modules\FilesEsx\Domain\ValueObjects\FileEsxFileName;
use Src\Modules\FilesEsx\Domain\ValueObjects\FileEsxId;
use Src\Modules\FilesEsx\Domain\ValueObjects\FileEsxPath;
use Tests\TestCase;

final class UpdateFileEsxHandlerTest extends TestCase
{
    private FileEsxRepositoryPort&MockObject $repository;
    private UpdateFileEsxHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(FileEsxRepositoryPort::class);
        $this->handler    = new UpdateFileEsxHandler($this->repository);
    }

    #[Test]
    public function it_updates_file_name_and_saves(): void
    {
        $uuid    = '550e8400-e29b-41d4-a716-446655440000';
        $fileEsx = new FileEsx(
            id: FileEsxId::fromString($uuid),
            fileName: FileEsxFileName::fromNullable('old.pdf'),
            filePath: FileEsxPath::fromString('files/old.pdf'),
            uploadedBy: 1,
        );

        $this->repository
            ->method('find')
            ->willReturn($fileEsx);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(
                static fn (FileEsx $saved): bool => $saved->fileName->toNullableString() === 'new.pdf',
            ));

        $this->handler->handle($uuid, new UpdateFileEsxData(fileName: 'new.pdf'));
    }

    #[Test]
    public function it_throws_when_file_esx_not_found(): void
    {
        $this->repository->method('find')->willReturn(null);

        $this->expectException(FileEsxNotFoundException::class);

        $this->handler->handle('non-existent-uuid', new UpdateFileEsxData(fileName: 'x.pdf'));
    }
}
