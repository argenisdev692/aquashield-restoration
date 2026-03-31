<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Infrastructure\Http\Controllers\Api;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use Src\Modules\ScopeSheets\Application\Queries\GetScopeSheetHandler;
use Src\Modules\ScopeSheets\Infrastructure\Persistence\Eloquent\Models\ScopeSheetExportEloquentModel;

/**
 * @OA\Get(
 *     path="/scope-sheets/data/admin/{uuid}/generate-pdf",
 *     tags={"Scope Sheets"},
 *     summary="Generate the scope sheet document PDF (stores to R2 and returns download)",
 *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *     @OA\Parameter(name="download", in="query", required=false, @OA\Schema(type="boolean", default=false)),
 *     @OA\Response(response=200, description="PDF document streamed or download link returned"),
 *     @OA\Response(response=404, description="Not found"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     security={{"sanctum": {}}}
 * )
 */
final class ScopeSheetDocumentController extends Controller
{
    public function __construct(
        private readonly GetScopeSheetHandler $getHandler,
    ) {}

    public function __invoke(string $uuid): Response|JsonResponse
    {
        $scopeSheet = $this->getHandler->handle($uuid);

        if ($scopeSheet === null) {
            return response()->json(['message' => 'Scope sheet not found.'], 404);
        }

        $presentationImages = array_map(
            static fn (array $p): array => [
                'type' => $p['photo_type'],
                'path' => Storage::disk('r2')->temporaryUrl($p['photo_path'], now()->addMinutes(30)),
            ],
            $scopeSheet->presentations,
        );

        $zoneImages = array_map(
            static function (array $z): array {
                return [
                    'title'  => $z['zone_name'] ?? 'Zone',
                    'notes'  => $z['zone_notes'] ?? '',
                    'images' => array_map(
                        static fn (array $ph): array => [
                            'path' => Storage::disk('r2')->temporaryUrl($ph['photo_path'], now()->addMinutes(30)),
                            'type' => 'zone_photo',
                        ],
                        $z['photos'],
                    ),
                ];
            },
            $scopeSheet->zones,
        );

        $logoPath   = public_path('img/Logo PNG.png');
        $logoBase64 = 'data:image/png;base64,' . base64_encode((string) file_get_contents($logoPath));

        $pdf = Pdf::loadView('exports.pdf.scope_sheet_document', [
            'scopeSheet'         => $scopeSheet,
            'presentationImages' => $presentationImages,
            'zoneImages'         => $zoneImages,
            'logoBase64'         => $logoBase64,
            'generatedAt'        => now()->format('F j, Y H:i'),
            'date'               => now()->format('m/d/Y'),
        ])->setPaper('a4', 'portrait');

        $pdfContent = $pdf->output();
        $storagePath = 'scope-sheet-documents/' . $uuid . '-' . now()->format('YmdHis') . '.pdf';

        Storage::disk('r2')->put($storagePath, $pdfContent);

        $scopeSheetModel = \Src\Modules\ScopeSheets\Infrastructure\Persistence\Eloquent\Models\ScopeSheetEloquentModel
            ::where('uuid', $uuid)->first();

        if ($scopeSheetModel !== null) {
            ScopeSheetExportEloquentModel::create([
                'uuid'           => Uuid::uuid4()->toString(),
                'scope_sheet_id' => $scopeSheetModel->id,
                'full_pdf_path'  => $storagePath,
                'generated_by'   => auth()->id() ?? $scopeSheet->generatedBy,
            ]);
        }

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="scope-sheet-' . $uuid . '.pdf"',
        ]);
    }
}
