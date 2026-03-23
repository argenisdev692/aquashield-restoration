<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Tests\Unit\Domain;

use PHPUnit\Framework\Attributes\Test;
use Src\Modules\FilesEsx\Domain\Entities\FileEsx;
use Src\Modules\FilesEsx\Domain\ValueObjects\FileEsxFileName;
use Src\Modules\FilesEsx\Domain\ValueObjects\FileEsxId;
use Src\Modules\FilesEsx\Domain\ValueObjects\FileEsxPath;
use Tests\TestCase;

final class FileEsxEntityTest extends TestCase
{
    #[Test]
    public function it_creates_a_file_esx_entity(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';

        $fileEsx = new FileEsx(
            id: FileEsxId::fromString($uuid),
            fileName: FileEsxFileName::fromNullable('document.pdf'),
            filePath: FileEsxPath::fromString('files/document.pdf'),
            uploadedBy: 1,
        );

        $this->assertSame($uuid, $fileEsx->id->toString());
        $this->assertSame('document.pdf', $fileEsx->fileName->toNullableString());
        $this->assertSame('files/document.pdf', $fileEsx->filePath->toString());
        $this->assertSame(1, $fileEsx->uploadedBy);
        $this->assertNull($fileEsx->deletedAt);
    }

    #[Test]
    public function it_reports_active_when_not_deleted(): void
    {
        $fileEsx = new FileEsx(
            id: FileEsxId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            fileName: FileEsxFileName::fromNullable(null),
            filePath: FileEsxPath::fromString('files/doc.pdf'),
            uploadedBy: 1,
            deletedAt: null,
        );

        $this->assertTrue($fileEsx->isActive());
        $this->assertSame('Active', $fileEsx->status());
    }

    #[Test]
    public function it_reports_suspended_when_deleted(): void
    {
        $fileEsx = new FileEsx(
            id: FileEsxId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            fileName: FileEsxFileName::fromNullable(null),
            filePath: FileEsxPath::fromString('files/doc.pdf'),
            uploadedBy: 1,
            deletedAt: \Carbon\CarbonImmutable::now(),
        );

        $this->assertFalse($fileEsx->isActive());
        $this->assertSame('Suspended', $fileEsx->status());
    }

    #[Test]
    public function it_creates_immutable_clone_with_new_file_name(): void
    {
        $fileEsx = new FileEsx(
            id: FileEsxId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            fileName: FileEsxFileName::fromNullable('old.pdf'),
            filePath: FileEsxPath::fromString('files/old.pdf'),
            uploadedBy: 1,
        );

        $updated = $fileEsx->withFileName(FileEsxFileName::fromNullable('new.pdf'));

        $this->assertSame('old.pdf', $fileEsx->fileName->toNullableString());
        $this->assertSame('new.pdf', $updated->fileName->toNullableString());
        $this->assertNotSame($fileEsx, $updated);
    }

    #[Test]
    public function it_throws_when_file_path_is_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('FileEsxPath cannot be empty');

        FileEsxPath::fromString('   ');
    }

    #[Test]
    public function it_throws_when_file_esx_id_is_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('FileEsxId cannot be empty');

        FileEsxId::fromString('');
    }

    #[Test]
    public function it_accepts_nullable_file_name(): void
    {
        $vo = FileEsxFileName::fromNullable(null);
        $this->assertNull($vo->toNullableString());
    }

    #[Test]
    public function it_trims_file_name_on_construction(): void
    {
        $vo = FileEsxFileName::fromNullable('  my file.pdf  ');
        $this->assertSame('my file.pdf', $vo->toNullableString());
    }
}
