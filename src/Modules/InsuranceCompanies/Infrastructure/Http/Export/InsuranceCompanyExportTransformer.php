<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Infrastructure\Http\Export;

use Carbon\CarbonImmutable;
use Modules\InsuranceCompanies\Infrastructure\Persistence\Eloquent\Models\InsuranceCompanyEloquentModel;
use Shared\Infrastructure\Utils\PhoneHelper;

final class InsuranceCompanyExportTransformer
{
    #[\NoDiscard]
    public static function transformForExcel(InsuranceCompanyEloquentModel $company): array
    {
        return $company
            |> self::extractPayload(...)
            |> self::formatDates(...)
            |> self::sanitizePayload(...)
            |> self::toExcelRow(...);
    }

    #[\NoDiscard]
    public static function transformForPdf(InsuranceCompanyEloquentModel $company): array
    {
        return $company
            |> self::extractPayload(...)
            |> self::formatDates(...)
            |> self::sanitizePayload(...);
    }

    private static function extractPayload(InsuranceCompanyEloquentModel $company): array
    {
        return [
            'insurance_company_name' => $company->insurance_company_name,
            'email' => $company->email,
            'phone' => PhoneHelper::format($company->phone),
            'address' => trim(implode(', ', array_filter([$company->address, $company->address_2]))) ?: null,
            'website' => $company->website,
            'status' => $company->deleted_at !== null ? 'Suspended' : 'Active',
            'created_at' => $company->created_at?->toIso8601String(),
            'deleted_at' => $company->deleted_at?->toIso8601String(),
        ];
    }

    private static function formatDates(array $payload): array
    {
        foreach (['created_at', 'deleted_at'] as $field) {
            $payload[$field] = $payload[$field] !== null
                ? CarbonImmutable::parse($payload[$field])->format('F j, Y')
                : '—';
        }

        return $payload;
    }

    private static function sanitizePayload(array $payload): array
    {
        foreach (['insurance_company_name', 'email', 'phone', 'address', 'website'] as $field) {
            $payload[$field] = $payload[$field] ?? '—';

            if ($payload[$field] === '') {
                $payload[$field] = '—';
            }
        }

        return $payload;
    }

    private static function toExcelRow(array $payload): array
    {
        return [
            $payload['insurance_company_name'],
            $payload['email'],
            $payload['phone'],
            $payload['address'],
            $payload['website'],
            $payload['status'],
            $payload['created_at'],
            $payload['deleted_at'],
        ];
    }
}
