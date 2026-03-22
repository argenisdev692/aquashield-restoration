<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; margin: 0; padding: 20px; }
        .header { width: 100%; border-bottom: 2px solid #22d3ee; padding-bottom: 12px; margin-bottom: 18px; }
        .brand-table { width: 100%; border: none; margin: 0; }
        .brand-table td { border: none; padding: 0; vertical-align: middle; }
        .logo { height: 40px; }
        .title { font-size: 16px; font-weight: 700; color: #0891b2; text-align: right; }
        .meta { margin-top: 4px; text-align: right; color: #6b7280; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f3f4f6; color: #111827; border: 1px solid #d1d5db; padding: 6px; font-size: 9px; text-align: left; font-weight: 600; }
        td { border: 1px solid #e5e7eb; padding: 6px; font-size: 9px; vertical-align: top; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        .direction-badge, .status-badge, .type-badge { display: inline-block; padding: 2px 6px; border-radius: 999px; font-size: 8px; font-weight: 600; }
        .direction-inbound { background-color: #dbeafe; color: #1e40af; }
        .direction-outbound { background-color: #fef3c7; color: #92400e; }
        .status-completed { background-color: #dcfce7; color: #166534; }
        .status-failed { background-color: #fee2e2; color: #991b1b; }
        .status-in_progress { background-color: #fef3c7; color: #92400e; }
        .status-registered { background-color: #f3f4f6; color: #374151; }
        .footer { margin-top: 18px; text-align: center; font-size: 8px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 10px; }
        .duration { font-family: monospace; color: #6b7280; }
        .call-id { font-family: monospace; font-size: 8px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <table class="brand-table">
            <tr>
                <td><img src="{{ public_path('img/Logo PNG.png') }}" class="logo" alt="AquaShield CRM"></td>
                <td>
                    <div class="title">{{ $title }}</div>
                    <div class="meta">Generated on: {{ $generatedAt }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>Call ID</th>
                <th>Agent</th>
                <th>From</th>
                <th>To</th>
                <th>Direction</th>
                <th>Status</th>
                <th>Type</th>
                <th>Start Time</th>
                <th>Duration</th>
                <th>Reason</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                @php
                    $directionClass = ($row['direction'] ?? '') === 'inbound' ? 'direction-inbound' : 'direction-outbound';
                    $status = $row['call_status'] ?? 'registered';
                    $statusClass = 'status-' . $status;
                @endphp
                <tr>
                    <td><span class="call-id">{{ Str::limit($row['call_id'] ?? '—', 20) }}</span></td>
                    <td>{{ $row['agent_name'] ?? '—' }}</td>
                    <td>{{ $row['from_number'] ?? '—' }}</td>
                    <td>{{ $row['to_number'] ?? '—' }}</td>
                    <td><span class="direction-badge {{ $directionClass }}">{{ ucfirst($row['direction'] ?? '—') }}</span></td>
                    <td><span class="status-badge {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span></td>
                    <td><span class="type-badge">{{ ucfirst(str_replace('_', ' ', $row['call_type'] ?? 'other')) }}</span></td>
                    <td>{{ $row['start_timestamp'] ?? '—' }}</td>
                    <td class="duration">{{ $row['duration_formatted'] ?? '—' }}</td>
                    <td>{{ $row['disconnection_reason'] ? Str::limit($row['disconnection_reason'], 25) : '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center; padding: 20px;">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        AquaShield CRM - Call History Report<br>
        Confidential - For authorized use only
    </div>
</body>
</html>
