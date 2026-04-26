<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Application\Queries;

use Carbon\Carbon;
use Src\Modules\Appointments\Infrastructure\Persistence\Eloquent\Models\AppointmentEloquentModel;

final class ListCalendarEventsHandler
{
    private const STATUS_COLORS = [
        'Confirmed' => '#10b981',
        'Pending' => '#f59e0b',
        'Completed' => '#059669',
        'Declined' => '#ef4444',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function handle(?string $start, ?string $end): array
    {
        $startDate = $start !== null
            ? Carbon::parse($start)->startOfDay()
            : now()->subMonth()->startOfDay();

        $endDate = $end !== null
            ? Carbon::parse($end)->endOfDay()
            : now()->addMonth()->endOfDay();

        return AppointmentEloquentModel::query()
            ->whereNotNull('inspection_date')
            ->whereBetween('inspection_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('inspection_date')
            ->orderBy('inspection_time')
            ->get()
            ->map(static function (AppointmentEloquentModel $appointment): array {
                $status = (string) $appointment->inspection_status;
                $color = self::STATUS_COLORS[$status] ?? '#3b82f6';

                $date = $appointment->inspection_date instanceof Carbon
                    ? $appointment->inspection_date
                    : Carbon::parse((string) $appointment->inspection_date);

                $time = $appointment->inspection_time !== null
                    ? Carbon::parse((string) $appointment->inspection_time)
                    : null;

                if ($time !== null) {
                    $startAt = $date->copy()
                        ->setHour($time->hour)
                        ->setMinute($time->minute)
                        ->setSecond(0);
                    $endAt = $startAt->copy()->addHours(2);
                    $allDay = false;
                } else {
                    $startAt = $date->copy()->startOfDay();
                    $endAt = $date->copy()->endOfDay();
                    $allDay = true;
                }

                $address = collect([
                    $appointment->address,
                    $appointment->address_2,
                    $appointment->city,
                    $appointment->state,
                    $appointment->zipcode,
                ])->filter()->implode(', ');

                return [
                    'id' => (string) $appointment->uuid,
                    'title' => trim(((string) $appointment->first_name) . ' ' . ((string) $appointment->last_name)),
                    'start' => $startAt->toIso8601String(),
                    'end' => $endAt->toIso8601String(),
                    'allDay' => $allDay,
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'extendedProps' => [
                        'uuid' => (string) $appointment->uuid,
                        'first_name' => (string) $appointment->first_name,
                        'last_name' => (string) $appointment->last_name,
                        'full_name' => trim(((string) $appointment->first_name) . ' ' . ((string) $appointment->last_name)),
                        'email' => $appointment->email,
                        'phone' => $appointment->phone,
                        'address' => $address,
                        'inspection_date' => $appointment->inspection_date?->format('Y-m-d'),
                        'inspection_time' => $time?->format('H:i'),
                        'inspection_status' => $status,
                        'status_lead' => (string) $appointment->status_lead,
                        'notes' => $appointment->notes,
                        'damage_detail' => $appointment->damage_detail,
                        'message' => $appointment->message,
                        'insurance_property' => (bool) $appointment->insurance_property,
                        'latitude' => $appointment->latitude !== null ? (float) $appointment->latitude : null,
                        'longitude' => $appointment->longitude !== null ? (float) $appointment->longitude : null,
                    ],
                ];
            })
            ->values()
            ->all();
    }
}
