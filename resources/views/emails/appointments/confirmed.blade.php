@php
    $address = collect([
        $appointment['address'] ?? null,
        $appointment['address_2'] ?? null,
        $appointment['city'] ?? null,
        $appointment['state'] ?? null,
        $appointment['zipcode'] ?? null,
    ])->filter()->implode(', ');

    $inspectionDate = !empty($appointment['inspection_date'])
        ? \Carbon\Carbon::parse($appointment['inspection_date'])->format('F j, Y')
        : '—';

    $inspectionTime = !empty($appointment['inspection_time'])
        ? \Carbon\Carbon::parse($appointment['inspection_time'])->format('g:i A')
        : '—';
@endphp

@component('emails.appointments.layout', [
    'title' => 'Appointment Confirmed',
    'heading' => 'Your Appointment is Confirmed',
    'variant' => 'confirmed',
    'company' => $company,
])
    <div class="greeting">Hi {{ $appointment['first_name'] ?? 'there' }},</div>
    <p class="lead">
        Great news — your inspection appointment with <strong>{{ $company['name'] ?? config('app.name') }}</strong>
        has been <span class="badge confirmed">Confirmed</span>.
    </p>

    <div class="details">
        <div class="details-row">
            <span class="details-label">Date</span>
            <span class="details-value">{{ $inspectionDate }}</span>
        </div>
        <div class="details-row">
            <span class="details-label">Time</span>
            <span class="details-value">{{ $inspectionTime }}</span>
        </div>
        @if($address !== '')
            <div class="details-row">
                <span class="details-label">Address</span>
                <span class="details-value">{{ $address }}</span>
            </div>
        @endif
        @if(!empty($appointment['phone']))
            <div class="details-row">
                <span class="details-label">Phone</span>
                <span class="details-value">{{ $appointment['phone'] }}</span>
            </div>
        @endif
    </div>

    <p class="lead">
        Our inspector will arrive at the scheduled time. If you need to reschedule or cancel, please reply to this email
        or contact us directly.
    </p>
    <p class="lead">
        Thank you for choosing us.
    </p>
@endcomponent
