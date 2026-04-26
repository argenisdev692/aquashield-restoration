<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Appointment Notification' }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
            color: #1f2937;
            line-height: 1.6;
        }
        .wrapper {
            max-width: 640px;
            margin: 0 auto;
            padding: 24px;
        }
        .card {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
        }
        .header {
            padding: 28px 32px;
            color: #ffffff;
            text-align: center;
        }
        .header.confirmed { background: linear-gradient(135deg, #059669, #10b981); }
        .header.rescheduled { background: linear-gradient(135deg, #2563eb, #3b82f6); }
        .header.cancelled { background: linear-gradient(135deg, #dc2626, #ef4444); }
        .header h1 {
            font-size: 22px;
            margin: 0;
            font-weight: 700;
        }
        .body {
            padding: 28px 32px;
        }
        .greeting {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 16px;
            color: #111827;
        }
        .lead {
            font-size: 14px;
            color: #4b5563;
            margin-bottom: 20px;
        }
        .details {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 18px 20px;
            margin: 20px 0;
        }
        .details-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
        }
        .details-row:last-child { border-bottom: none; }
        .details-label {
            font-weight: 600;
            color: #6b7280;
            min-width: 130px;
        }
        .details-value {
            color: #111827;
            text-align: right;
            font-weight: 500;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge.confirmed { background: #d1fae5; color: #065f46; }
        .badge.rescheduled { background: #dbeafe; color: #1e40af; }
        .badge.cancelled { background: #fee2e2; color: #991b1b; }
        .badge.declined { background: #fef3c7; color: #92400e; }
        .footer {
            padding: 24px 32px;
            border-top: 1px solid #e5e7eb;
            background: #f9fafb;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
        .footer strong { color: #111827; }
        .footer a { color: #2563eb; text-decoration: none; }
        .strike {
            text-decoration: line-through;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header {{ $variant ?? 'confirmed' }}">
                <h1>{{ $heading ?? 'Appointment Notification' }}</h1>
            </div>
            <div class="body">
                {{ $slot }}
            </div>
            <div class="footer">
                <strong>{{ $company['name'] ?? config('app.name') }}</strong><br>
                @if(!empty($company['address']))
                    {{ $company['address'] }}<br>
                @endif
                @if(!empty($company['phone']))
                    {{ $company['phone'] }}
                @endif
                @if(!empty($company['email']))
                    &middot; <a href="mailto:{{ $company['email'] }}">{{ $company['email'] }}</a>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
