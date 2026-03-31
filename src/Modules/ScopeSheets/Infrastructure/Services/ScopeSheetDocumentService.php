<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Infrastructure\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Ramsey\Uuid\Uuid;
use Shared\Domain\Ports\StoragePort;
use Src\Modules\ScopeSheets\Application\Queries\GetScopeSheetHandler;
use Src\Modules\ScopeSheets\Application\Queries\ReadModels\ScopeSheetReadModel;
use Src\Modules\ScopeSheets\Infrastructure\Persistence\Eloquent\Models\ScopeSheetEloquentModel;
use Src\Modules\ScopeSheets\Infrastructure\Persistence\Eloquent\Models\ScopeSheetExportEloquentModel;

final class ScopeSheetDocumentService
{
    public function __construct(
        private readonly GetScopeSheetHandler $getHandler,
        private readonly StoragePort $storage,
    ) {}

    public function generate(string $uuid): Response|JsonResponse
    {
        $scopeSheet = $this->getHandler->handle($uuid);

        if ($scopeSheet === null) {
            return response()->json(['message' => 'Scope sheet not found.'], 404);
        }

        $pdfContent = $this->renderPdf($uuid, $scopeSheet);
        $storagePath = 'scope-sheet-documents/' . $uuid . '-' . now()->format('YmdHis') . '.pdf';

        $this->storage->put($storagePath, $pdfContent);
        $this->storeExportRecord($uuid, $scopeSheet, $storagePath);

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="scope-sheet-' . $uuid . '.pdf"',
        ]);
    }

    private function renderPdf(string $uuid, ScopeSheetReadModel $scopeSheet): string
    {
        $pdf = Pdf::loadView('exports.pdf.scope_sheet_document', [
            'scopeSheet'         => $scopeSheet,
            'presentationImages' => $this->buildPresentationImages($scopeSheet),
            'zoneImages'         => $this->buildZoneImages($scopeSheet),
            'logoBase64'         => $this->logoUrl(),
            'generatedAt'        => now()->format('F j, Y H:i'),
            'date'               => now()->format('m/d/Y'),
        ])->setPaper('a4', 'portrait');

        return $pdf->output();
    }

    /**
     * @return array<int, array{type: string, path: string}>
     */
    private function buildPresentationImages(ScopeSheetReadModel $scopeSheet): array
    {
        return array_map(
            fn (array $presentation): array => [
                'type' => $presentation['photo_type'],
                'path' => $this->storage->temporaryUrl($presentation['photo_path'], now()->addMinutes(30)),
            ],
            $scopeSheet->presentations,
        );
    }

    /**
     * @return array<int, array{title: string, notes: string, images: array<int, array{path: string, type: string}>}>
     */
    private function buildZoneImages(ScopeSheetReadModel $scopeSheet): array
    {
        return array_map(
            fn (array $zone): array => [
                'title'  => $zone['zone_name'] ?? 'Zone',
                'notes'  => $zone['zone_notes'] ?? '',
                'images' => array_map(
                    fn (array $photo): array => [
                        'path' => $this->storage->temporaryUrl($photo['photo_path'], now()->addMinutes(30)),
                        'type' => 'zone_photo',
                    ],
                    $zone['photos'],
                ),
            ],
            $scopeSheet->zones,
        );
    }

    private function logoUrl(): string
    {
        return asset('img/Logo PNG.png');
    }

    private function storeExportRecord(string $uuid, ScopeSheetReadModel $scopeSheet, string $storagePath): void
    {
        $scopeSheetModel = ScopeSheetEloquentModel::where('uuid', $uuid)->first();

        if ($scopeSheetModel === null) {
            return;
        }

        ScopeSheetExportEloquentModel::create([
            'uuid'           => Uuid::uuid4()->toString(),
            'scope_sheet_id' => $scopeSheetModel->id,
            'full_pdf_path' => $storagePath,
            'generated_by'   => auth()->id() ?? $scopeSheet->generatedBy,
        ]);
    }
}
