<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Tests\Integration;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Src\Modules\FilesEsx\Domain\Entities\FileEsx;
use Src\Modules\FilesEsx\Domain\ValueObjects\FileEsxFileName;
use Src\Modules\FilesEsx\Domain\ValueObjects\FileEsxId;
use Src\Modules\FilesEsx\Domain\ValueObjects\FileEsxPath;
use Src\Modules\FilesEsx\Infrastructure\Persistence\Mappers\FileEsxMapper;
use Src\Modules\FilesEsx\Infrastructure\Persistence\Repositories\EloquentFileEsxRepository;
use Tests\TestCase;

final class FileEsxRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentFileEsxRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new EloquentFileEsxRepository(new FileEsxMapper());
    }

    #[Test]
    public function it_saves_and_retrieves_a_file_esx(): void
    {
        $user = \Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel::factory()->create();

        $uuid    = (string) \Illuminate\Support\Str::uuid();
        $fileEsx = new FileEsx(
            id: FileEsxId::fromString($uuid),
            fileName: FileEsxFileName::fromNullable('integration-test.pdf'),
            filePath: FileEsxPath::fromString('uploads/integration-test.pdf'),
            uploadedBy: (int) $user->id,
        );

        $this->repository->save($fileEsx);

        $found = $this->repository->find(FileEsxId::fromString($uuid));

        $this->assertNotNull($found);
        $this->assertSame($uuid, $found->id->toString());
        $this->assertSame('integration-test.pdf', $found->fileName->toNullableString());
        $this->assertSame('uploads/integration-test.pdf', $found->filePath->toString());
        $this->assertSame((int) $user->id, $found->uploadedBy);
    }

    #[Test]
    public function it_soft_deletes_a_file_esx(): void
    {
        $user = \Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel::factory()->create();

        $uuid    = (string) \Illuminate\Support\Str::uuid();
        $fileEsx = new FileEsx(
            id: FileEsxId::fromString($uuid),
            fileName: FileEsxFileName::fromNullable('to-delete.pdf'),
            filePath: FileEsxPath::fromString('uploads/to-delete.pdf'),
            uploadedBy: (int) $user->id,
        );

        $this->repository->save($fileEsx);
        $this->repository->softDelete(FileEsxId::fromString($uuid));

        $found = $this->repository->find(FileEsxId::fromString($uuid));

        $this->assertNotNull($found);
        $this->assertNotNull($found->deletedAt);
        $this->assertFalse($found->isActive());
    }

    #[Test]
    public function it_restores_a_soft_deleted_file_esx(): void
    {
        $user = \Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel::factory()->create();

        $uuid    = (string) \Illuminate\Support\Str::uuid();
        $fileEsx = new FileEsx(
            id: FileEsxId::fromString($uuid),
            fileName: FileEsxFileName::fromNullable('to-restore.pdf'),
            filePath: FileEsxPath::fromString('uploads/to-restore.pdf'),
            uploadedBy: (int) $user->id,
        );

        $this->repository->save($fileEsx);
        $this->repository->softDelete(FileEsxId::fromString($uuid));
        $this->repository->restore(FileEsxId::fromString($uuid));

        $found = $this->repository->find(FileEsxId::fromString($uuid));

        $this->assertNotNull($found);
        $this->assertNull($found->deletedAt);
        $this->assertTrue($found->isActive());
    }

    #[Test]
    public function it_returns_null_for_nonexistent_uuid(): void
    {
        $result = $this->repository->find(FileEsxId::fromString('00000000-0000-0000-0000-000000000000'));

        $this->assertNull($result);
    }

    #[Test]
    public function it_bulk_soft_deletes_multiple_files(): void
    {
        $user = \Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel::factory()->create();

        $uuids = [];
        foreach (range(1, 3) as $i) {
            $uuid    = (string) \Illuminate\Support\Str::uuid();
            $uuids[] = $uuid;
            $this->repository->save(new FileEsx(
                id: FileEsxId::fromString($uuid),
                fileName: FileEsxFileName::fromNullable("bulk-{$i}.pdf"),
                filePath: FileEsxPath::fromString("uploads/bulk-{$i}.pdf"),
                uploadedBy: (int) $user->id,
            ));
        }

        $ids     = array_map(static fn (string $u): FileEsxId => FileEsxId::fromString($u), $uuids);
        $deleted = $this->repository->bulkSoftDelete($ids);

        $this->assertSame(3, $deleted);
    }
}
