<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a1a1a; background: #fff; }
        .header { background: #1e3a5f; color: #fff; padding: 16px 20px; margin-bottom: 20px; border-radius: 4px; }
        .header h1 { font-size: 16px; font-weight: 700; letter-spacing: 0.5px; }
        .header p { font-size: 9px; opacity: 0.8; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        thead tr { background: #1e3a5f; color: #fff; }
        thead th { padding: 8px 10px; text-align: left; font-size: 9px; font-weight: 700; letter-spacing: 0.3px; }
        tbody tr:nth-child(even) { background: #f5f8fc; }
        tbody tr:hover { background: #e8f0fb; }
        tbody td { padding: 7px 10px; border-bottom: 1px solid #e2e8f0; font-size: 9px; vertical-align: top; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 8px; font-weight: 700; }
        .badge-active { background: #dcfce7; color: #166534; }
        .badge-suspended { background: #fee2e2; color: #991b1b; }
        .footer { text-align: right; font-size: 8px; color: #6b7280; margin-top: 12px; border-top: 1px solid #e2e8f0; padding-top: 8px; }
        .empty { text-align: center; padding: 24px; color: #6b7280; }
        .path-cell { max-width: 180px; word-break: break-all; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Generated: {{ $generatedAt }}</p>
    </div>

    @if(empty($rows))
        <div class="empty">No records found for the selected filters.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th>UUID</th>
                    <th>File Name</th>
                    <th>File Path</th>
                    <th>Uploaded By</th>
                    <th>Assigned Adjusters</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Deleted At</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        <td style="font-size:8px; opacity:0.7;">{{ $row['uuid'] }}</td>
                        <td>{{ $row['file_name'] }}</td>
                        <td class="path-cell">{{ $row['file_path'] }}</td>
                        <td>{{ $row['uploader'] }}</td>
                        <td>{{ $row['adjusters'] }}</td>
                        <td>
                            <span class="badge {{ $row['status'] === 'Active' ? 'badge-active' : 'badge-suspended' }}">
                                {{ $row['status'] }}
                            </span>
                        </td>
                        <td>{{ $row['created_at'] }}</td>
                        <td>{{ $row['deleted_at'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Total records: {{ count($rows) }} &nbsp;|&nbsp; AquaShield CRM &nbsp;|&nbsp; {{ $generatedAt }}
    </div>
</body>
</html>
