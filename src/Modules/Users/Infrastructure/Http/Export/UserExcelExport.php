<?php

declare(strict_types=1);

namespace Modules\Users\Infrastructure\Http\Export;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Modules\Users\Application\DTOs\UserFilterDTO;
use Illuminate\Database\Eloquent\Builder;
use Shared\Infrastructure\Utils\PhoneHelper;

final class UserExcelExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithTitle,
    WithStyles
{
    use Exportable;

    public function __construct(
        private readonly UserFilterDTO $filters
    ) {
    }

    public function query(): Builder
    {
        $status = $this->filters->status?->value;

        /** @var Builder $query */
        $query = UserEloquentModel::query()
            ->withTrashed()
            ->select([
                'id',
                'uuid',
                'name',
                'last_name',
                'email',
                'username',
                'phone',
                'city',
                'state',
                'country',
                'status',
                'created_at',
                'deleted_at',
            ])
            ->when($this->filters->search, fn($q, $s) => $q->where(
                fn($bq) =>
                $bq->where('name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
            ))
            ->when($status === 'deleted', fn($q) => $q->onlyTrashed())
            ->when(
                $status !== null && $status !== 'deleted',
                fn($q) => $q->whereNull('deleted_at')->where('status', $status),
            )
            ->when(
                $this->filters->dateFrom || $this->filters->dateTo,
                fn($q) => $q->inDateRange($this->filters->dateFrom, $this->filters->dateTo)
            )
            ->orderBy($this->filters->sortBy ?? 'created_at', $this->filters->sortDir ?? 'desc');

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'UUID',
            'First Name',
            'Last Name',
            'Email',
            'Username',
            'Phone',
            'City',
            'State',
            'Country',
            'Status',
            'Created At',
        ];
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->uuid,
            $user->name,
            $user->last_name,
            $user->email,
            $user->username,
            PhoneHelper::format($user->phone) ?: '—',
            $user->city,
            $user->state,
            $user->country,
            self::formatStatus($user->status, $user->deleted_at),
            $user->created_at?->format('F j, Y') ?? '—',
        ];
    }

    private static function formatStatus(?string $status, mixed $deletedAt): string
    {
        if ($deletedAt !== null) {
            return 'Inactive';
        }

        return match ($status) {
            'suspended' => 'Suspended',
            'banned' => 'Banned',
            'pending_setup' => 'Pending Setup',
            default => 'Active',
        };
    }

    public function title(): string
    {
        return 'Users Export';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
