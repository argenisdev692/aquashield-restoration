<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a1a1a; background: #fff; }
        .header { padding: 16px 20px 10px; border-bottom: 2px solid #1e3a5f; margin-bottom: 14px; }
        .header h1 { font-size: 16px; font-weight: 700; color: #1e3a5f; letter-spacing: 0.3px; }
        .header p { font-size: 9px; color: #6b7280; margin-top: 3px; }
        table { width: 100%; border-collapse: collapse; font-size: 9px; }
        thead tr { background-color: #1e3a5f; color: #fff; }
        thead th { padding: 7px 8px; text-align: left; font-weight: 600; letter-spacing: 0.2px; white-space: nowrap; }
        tbody tr:nth-child(even) { background-color: #f3f6fb; }
        tbody tr:nth-child(odd)  { background-color: #ffffff; }
        tbody td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        .badge { display: inline-block; padding: 2px 7px; border-radius: 9px; font-size: 8px; font-weight: 600; }
        .badge-active    { background: #d1fae5; color: #065f46; }
        .badge-suspended { background: #fee2e2; color: #991b1b; }
        .footer { margin-top: 14px; padding-top: 8px; border-top: 1px solid #e5e7eb; text-align: right; font-size: 8px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Generated: {{ $generatedAt }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Cell Phone</th>
                <th>Home Phone</th>
                <th>Occupation</th>
                <th>Assigned User</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Deleted At</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['last_name'] }}</td>
                    <td>{{ $row['email'] }}</td>
                    <td>{{ $row['cell_phone'] }}</td>
                    <td>{{ $row['home_phone'] }}</td>
                    <td>{{ $row['occupation'] }}</td>
                    <td>{{ $row['user_name'] }}</td>
                    <td>
                        @if ($row['status'] === 'Active')
                            <span class="badge badge-active">Active</span>
                        @else
                            <span class="badge badge-suspended">Suspended</span>
                        @endif
                    </td>
                    <td>{{ $row['created_at'] }}</td>
                    <td>{{ $row['deleted_at'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align:center; padding:16px; color:#6b7280;">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Total records: {{ count($rows) }} &nbsp;·&nbsp; AquaShield CRM
    </div>
</body>
</html>
