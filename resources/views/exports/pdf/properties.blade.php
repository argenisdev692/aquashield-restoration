<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1a1a1a; background: #fff; }
        .header { padding: 16px 20px 10px; border-bottom: 2px solid #1e3a5f; margin-bottom: 14px; }
        .header h1 { font-size: 16px; font-weight: 700; color: #1e3a5f; letter-spacing: 0.3px; }
        .header p { font-size: 9px; color: #6b7280; margin-top: 3px; }
        table { width: 100%; border-collapse: collapse; font-size: 8px; }
        thead tr { background-color: #1e3a5f; color: #fff; }
        thead th { padding: 6px 7px; text-align: left; font-weight: 600; letter-spacing: 0.2px; white-space: nowrap; }
        tbody tr:nth-child(even) { background-color: #f3f6fb; }
        tbody tr:nth-child(odd)  { background-color: #ffffff; }
        tbody td { padding: 5px 7px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        .badge { display: inline-block; padding: 2px 7px; border-radius: 9px; font-size: 7px; font-weight: 600; }
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
                <th>Address</th>
                <th>Address 2</th>
                <th>City</th>
                <th>State</th>
                <th>Postal Code</th>
                <th>Country</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Deleted At</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['property_address'] }}</td>
                    <td>{{ $row['property_address_2'] }}</td>
                    <td>{{ $row['property_city'] }}</td>
                    <td>{{ $row['property_state'] }}</td>
                    <td>{{ $row['property_postal_code'] }}</td>
                    <td>{{ $row['property_country'] }}</td>
                    <td>{{ $row['property_latitude'] }}</td>
                    <td>{{ $row['property_longitude'] }}</td>
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
                    <td colspan="11" style="text-align:center; padding:16px; color:#6b7280;">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Total records: {{ count($rows) }} &nbsp;·&nbsp; AquaShield CRM
    </div>
</body>
</html>
