@php
    $address = collect([
        $appointment['address'] ?? null,
        $appointment['address_2'] ?? null,
        $appointment['city'] ?? null,
        $appointment['state'] ?? null,
        $appointment['zipcode'] ?? null,
    ])->filter()->implode(', ');

    $newDate = !empty($appointment['inspection_date'])
        ? \Carbon\Carbon::parse($appointment['inspection_date'])->format('F j, Y')
        : '—';

    $newTime = !empty($appointment['inspection_time'])
        ? \Carbon\Carbon::parse($appointment['inspection_time'])->format('g:i A')
        : '—';

    $oldDate = !empty($previousDate)
        ? \Carbon\Carbon::parse($previousDate)->format('F j, Y')
        : null;

    $oldTime = !empty($previousTime)
        ? \Carbon\Carbon::parse($previousTime)->format('g:i A')
        : null;
@endphp

@component('emails.appointments.layout', [
    'title' => 'Appointment Updated',
    'heading' => 'Your Appointment Has Been Updated',
    'variant' => 'rescheduled',
    'company' => $company,
])
    <div class="greeting">Hi {{ $appointment['first_name'] ?? 'there' }},</div>
    <p class="lead">
        Your inspection appointment with <strong>{{ $company['name'] ?? config('app.name') }}</strong>
        has been <span class="badge rescheduled">Rescheduled</span>. Please review the new details below.
    </p>

    <div class="details">
        @if($oldDate || $oldTime)
            <div class="details-row">
                <span class="details-label">Previous</span>
                <span class="details-value strike">
                    {{ $oldDate ?? '—' }} @if($oldTime) at {{ $oldTime }} @endif
                </span>
            </div>
        @endif
        <div class="details-row">
            <span class="details-label">New Date</span>
            <span class="details-value"><strong>{{ $newDate }}</strong></span>
        </div>
        <div class="details-row">
            <span class="details-label">New Time</span>
            <span class="details-value"><strong>{{ $newTime }}</strong></span>
        </div>
        @if($address !== '')
            <div class="details-row">
                <span class="details-label">Address</span>
                <span class="details-value">{{ $address }}</span>
            </div>
        @endif
        <div class="details-row">
            <span class="details-label">Status</span>
            <span class="details-value">
                <span class="badge {{ strtolower($appointment['inspection_status'] ?? 'rescheduled') }}">
                    {{ $appointment['inspection_status'] ?? 'Rescheduled' }}
                </span>
            </span>
        </div>
    </div>

    <p class="lead">
        If this new schedule doesn't work for you, please contact us as soon as possible so we can find another time.
    </p>
    <p class="lead">
        Thank you for your patience.
    </p>
@endcomponent
