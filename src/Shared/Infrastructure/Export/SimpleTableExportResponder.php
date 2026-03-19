<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class SimpleTableExportResponder
{
    public function download(Request $request, string $title, string $filename, array $headings, array $rows): Response|BinaryFileResponse
    {
        $format = (string) $request->query('format', 'excel');

        if ($format === 'pdf') {
            return Pdf::loadView('shared.exports.simple-table', [
                'title' => $title,
                'headings' => $headings,
                'rows' => $rows,
                'generatedAt' => now()->format('Y-m-d H:i:s'),
            ])->download($filename . '.pdf');
        }

        return Excel::download(new SimpleTableExcelExport($headings, $rows), $filename . '.xlsx');
    }
}
