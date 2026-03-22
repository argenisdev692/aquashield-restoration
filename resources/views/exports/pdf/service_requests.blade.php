<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
            margin: 0;
            padding: 0;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid #d1d5db;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand img {
            height: 40px;
        }
        .meta {
            text-align: right;
            color: #4b5563;
            font-size: 11px;
        }
        h1 {
            margin: 0;
            font-size: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 10px 12px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #f3f4f6;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .active {
            color: #166534;
            background: #dcfce7;
        }
        .suspended {
            color: #991b1b;
            background: #fee2e2;
        }
        .empty {
            text-align: center;
            color: #6b7280;
            padding: 18px;
        }
        .footer {
            margin-top: 18px;
            padding-top: 10px;
            border-top: 1px solid #d1d5db;
            text-align: center;
            color: #6b7280;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">
            <img src="{{ public_path('img/Logo PNG-WHITE.png') }}" alt="AquaShield CRM">
            <div>
                <h1>{{ $title }}</h1>
                <div style="color: #4b5563; font-size: 11px;">Service Requests export report</div>
            </div>
        </div>
        <div class="meta">
            <div>Generated at</div>
            <div>{{ $generatedAt }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Requested Service</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Deleted At</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['requested_service'] }}</td>
                    <td>
                        <span class="badge {{ $row['status'] === 'Active' ? 'active' : 'suspended' }}">{{ $row['status'] }}</span>
                    </td>
                    <td>{{ $row['created_at'] }}</td>
                    <td>{{ $row['deleted_at'] }}</td>
                </tr>
            @empty
                <tr>
                    <td class="empty" colspan="4">No service requests found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">AquaShield CRM — Confidential</div>
</body>
</html>
