<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Shared\Infrastructure\Resilience\CircuitBreaker\CircuitBreakerInterface;

final class LaravelExportAdapter implements ExportInterface
{
    private const EXPORTS_DISK = 'r2';

    public function __construct(
        private readonly CircuitBreakerInterface $circuitBreaker,
    ) {
    }

    public function excel(array $data, string $filename): string
    {
        // Simple implementation using a collection
        $path = "exports/{$filename}.xlsx";

        $this->circuitBreaker->execute(
            'r2.shared.export.excel.write',
            static function () use ($data, $path): void {
                $stored = Excel::store(collect($data), $path, self::EXPORTS_DISK);

                if ($stored !== true) {
                    throw new \RuntimeException('Failed to store Excel export.');
                }
            },
            static function (): never {
                throw new \RuntimeException('Excel export storage is temporarily unavailable.');
            },
        );

        return $path;
    }

    public function pdf(string $view, array $data, string $filename): string
    {
        $path = "exports/{$filename}.pdf";

        $this->circuitBreaker->execute(
            'r2.shared.export.pdf.write',
            static function () use ($view, $data, $path): void {
                $stored = Storage::disk(self::EXPORTS_DISK)->put($path, Pdf::loadView($view, $data)->output());

                if ($stored !== true) {
                    throw new \RuntimeException('Failed to store PDF export.');
                }
            },
            static function (): never {
                throw new \RuntimeException('PDF export storage is temporarily unavailable.');
            },
        );

        return $path;
    }
}
