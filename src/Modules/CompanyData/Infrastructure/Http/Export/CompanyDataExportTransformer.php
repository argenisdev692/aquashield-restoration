<?php

declare(strict_types=1);

namespace Modules\CompanyData\Infrastructure\Http\Export;

use Carbon\CarbonImmutable;
use Modules\CompanyData\Infrastructure\Persistence\Eloquent\Models\CompanyDataEloquentModel;

final class CompanyDataExportTransformer
{
    #[\NoDiscard]
    public static function transformForExcel(CompanyDataEloquentModel $company): array
    {
        return $company
            |> self::extractPayload(...)
            |> self::formatDates(...)
            |> self::sanitizePayload(...)
            |> self::toExcelRow(...);
    }

    #[\NoDiscard]
    public static function transformForPdf(CompanyDataEloquentModel $company): object
    {
        return $company
            |> self::extractPayload(...)
            |> self::formatDates(...)
            |> self::sanitizePayload(...)
            |> (fn(array $payload): object => (object) $payload);
    }

    private static function extractPayload(CompanyDataEloquentModel $company): array
    {
        return [
            'id' => $company->id,
            'uuid' => $company->uuid,
            'company_name' => $company->company_name,
            'name' => $company->name,
            'email' => $company->email,
            'phone' => $company->phone,
            'address' => $company->address,
            'website' => $company->website,
            'created_at' => $company->created_at?->toIso8601String(),
        ];
    }

    private static function formatDates(array $payload): array
    {
        $payload['created_at'] = $payload['created_at'] !== null
            ? CarbonImmutable::parse($payload['created_at'])->format('F j, Y')
            : '—';

        return $payload;
    }

    private static function sanitizePayload(array $payload): array
    {
        foreach (['company_name', 'name', 'email', 'phone', 'address', 'website'] as $field) {
            $payload[$field] = $payload[$field] ?? '—';
        }

        return $payload;
    }

    private static function toExcelRow(array $payload): array
    {
        return [
            $payload['id'],
            $payload['uuid'],
            $payload['company_name'],
            $payload['name'],
            $payload['email'],
            $payload['phone'],
            $payload['address'],
            $payload['website'],
            $payload['created_at'],
        ];
    }
}
