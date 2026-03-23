<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1a1a2e; background: #fff; }

        .header { display: flex; justify-content: space-between; align-items: center; padding: 16px 24px; background: #0f3460; color: #fff; border-bottom: 3px solid #e94560; }
        .header .logo-wrap { display: flex; align-items: center; gap: 10px; }
        .header .logo-wrap img { height: 36px; }
        .header .brand { font-size: 18px; font-weight: 700; letter-spacing: 1px; }
        .header .meta { text-align: right; font-size: 10px; opacity: .85; }
        .header .meta p { margin-bottom: 2px; }

        .report-title { padding: 14px 24px 8px; font-size: 15px; font-weight: 700; color: #0f3460; border-bottom: 1px solid #dde2f0; }

        table { width: 100%; border-collapse: collapse; margin-top: 0; }
        thead tr { background: #0f3460; color: #fff; }
        thead th { padding: 8px 10px; text-align: left; font-size: 10px; font-weight: 600; letter-spacing: .5px; white-space: nowrap; }
        tbody tr:nth-child(even) { background: #f4f6fb; }
        tbody tr:hover { background: #e8edf8; }
        tbody td { padding: 7px 10px; border-bottom: 1px solid #e6e9f0; font-size: 10px; vertical-align: top; }

        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: 700; letter-spacing: .4px; text-transform: uppercase; }
        .badge-active    { background: #d1fae5; color: #065f46; }
        .badge-suspended { background: #fee2e2; color: #991b1b; }

        .zone-type { display: inline-block; padding: 2px 7px; border-radius: 6px; font-size: 9px; font-weight: 600; background: #e0e7ff; color: #3730a3; }

        .empty { text-align: center; padding: 32px; color: #9ca3af; font-style: italic; }

        .footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 8px 24px; background: #0f3460; color: rgba(255,255,255,.75); font-size: 9px; display: flex; justify-content: space-between; border-top: 2px solid #e94560; }
    </style>
</head>
<body>

    <div class="header">
        <div class="logo-wrap">
            <div class="brand">AquaShield CRM</div>
        </div>
        <div class="meta">
            <p><strong>{{ $title }}</strong></p>
            <p>Generated: {{ $generatedAt }}</p>
        </div>
    </div>

    <div class="report-title">{{ $title }}</div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Zone Name</th>
                <th>Zone Type</th>
                <th>Code</th>
                <th>Description</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Deleted At</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->zone_name }}</td>
                    <td><span class="zone-type">{{ $row->zone_type }}</span></td>
                    <td>{{ $row->code }}</td>
                    <td>{{ $row->description }}</td>
                    <td>
                        @if ($row->status === 'Active')
                            <span class="badge badge-active">Active</span>
                        @else
                            <span class="badge badge-suspended">Suspended</span>
                        @endif
                    </td>
                    <td>{{ $row->created_at }}</td>
                    <td>{{ $row->deleted_at }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="empty">No zones found for the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <span>AquaShield CRM &mdash; Confidential</span>
        <span>Total records: {{ count($rows) }}</span>
    </div>

</body>
</html>
