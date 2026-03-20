<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; margin: 0; padding: 24px; }
        .header { width: 100%; border-bottom: 2px solid #22d3ee; padding-bottom: 12px; margin-bottom: 18px; }
        .brand-table { width: 100%; border: none; margin: 0; }
        .brand-table td { border: none; padding: 0; vertical-align: middle; }
        .logo { height: 46px; }
        .title { font-size: 18px; font-weight: 700; color: #0891b2; text-align: right; }
        .meta { margin-top: 4px; text-align: right; color: #6b7280; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f3f4f6; color: #111827; border: 1px solid #d1d5db; padding: 8px; font-size: 10px; text-align: left; }
        td { border: 1px solid #e5e7eb; padding: 8px; font-size: 10px; vertical-align: top; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        .status-badge { display: inline-block; padding: 3px 8px; border-radius: 999px; font-size: 9px; font-weight: 700; }
        .status-active { background-color: #dcfce7; color: #166534; }
        .status-suspended { background-color: #fee2e2; color: #991b1b; }
        .footer { margin-top: 18px; text-align: center; font-size: 9px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="header">
        <table class="brand-table">
            <tr>
                <td><img src="{{ public_path('img/Logo PNG.png') }}" class="logo" alt="AquaShield CRM"></td>
                <td><div class="title">{{ $title }}</div><div class="meta">Generated on: {{ $generatedAt }}</div></td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>Email</th>
                <th>Type</th>
                <th>Phone</th>
                <th>Description</th>
                <th>User ID</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Deleted At</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                @php $isSuspended = !in_array($row[6] ?? null, [null, '', '-', '—'], true); @endphp
                <tr>
                    <td>{{ $row[0] ?? '—' }}</td>
                    <td>{{ $row[1] ?? '—' }}</td>
                    <td>{{ $row[2] ?? '—' }}</td>
                    <td>{{ $row[3] ?? '—' }}</td>
                    <td>{{ $row[4] ?? '—' }}</td>
                    <td><span class="status-badge {{ $isSuspended ? 'status-suspended' : 'status-active' }}">{{ $isSuspended ? 'Suspended' : 'Active' }}</span></td>
                    <td>{{ $row[5] ?? '—' }}</td>
                    <td>{{ $row[6] ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="8">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">AquaShield CRM</div>
</body>
</html>
