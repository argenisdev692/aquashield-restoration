@php
    $isDeclined = ($reason ?? 'cancelled') === 'declined';

    $heading = $isDeclined
        ? 'Your Appointment Has Been Declined'
        : 'Your Appointment Has Been Cancelled';

    $badgeLabel = $isDeclined ? 'Declined' : 'Cancelled';
    $badgeClass = $isDeclined ? 'declined' : 'cancelled';

    $previousDate = !empty($appointment['inspection_date'])
        ? \Carbon\Carbon::parse($appointment['inspection_date'])->format('F j, Y')
        : null;

    $previousTime = !empty($appointment['inspection_time'])
        ? \Carbon\Carbon::parse($appointment['inspection_time'])->format('g:i A')
        : null;
@endphp

@component('emails.appointments.layout', [
    'title' => $heading,
    'heading' => $heading,
    'variant' => 'cancelled',
    'company' => $company,
])
    <div class="greeting">Hi {{ $appointment['first_name'] ?? 'there' }},</div>
    <p class="lead">
        We are reaching out to let you know that your inspection appointment with
        <strong>{{ $company['name'] ?? config('app.name') }}</strong> has been
        <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>.
    </p>

    @if($previousDate || $previousTime)
        <div class="details">
            <div class="details-row">
                <span class="details-label">Originally Scheduled</span>
                <span class="details-value strike">
                    {{ $previousDate ?? '—' }} @if($previousTime) at {{ $previousTime }} @endif
                </span>
            </div>
        </div>
    @endif

    <p class="lead">
        @if($isDeclined)
            We regret that we could not move forward with this request. If you'd like to discuss alternatives,
            please reply to this email or contact us directly.
        @else
            We apologize for any inconvenience. If you'd like to reschedule, please reply to this email
            or contact us and we'll be happy to find a new time.
        @endif
    </p>

    <p class="lead">
        Thank you for understanding.
    </p>
@endcomponent
