<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #22d3ee;
            padding-bottom: 10px;
        }

        .logo {
            height: 50px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            color: #0891b2;
        }

        .meta {
            margin-top: 5px;
            color: #666;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background-color: #f3f4f6;
            color: #333;
            text-align: center;
            padding: 8px;
            border: 1px solid #e5e7eb;
            font-weight: bold;
        }

        td {
            padding: 8px;
            border: 1px solid #e5e7eb;
            text-align: center;
            vertical-align: middle;
        }

        tr:nth-child(even) {
            background-color: #fafafa;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
            color: #999;
            padding: 10px 0;
        }
    </style>
</head>

<body>
    <div class="header">
        <table style="border: none; margin: 0;">
            <tr style="background: none;">
                <td style="border: none; padding: 0;">
                    <img src="{{ public_path('img/Logo PNG.png') }}" class="logo" alt="Logo">
                </td>
                <td style="border: none; text-align: right; vertical-align: middle;">
                    <div class="title">{{ $title }}</div>
                    <div class="meta">Generated on: {{ $generatedAt }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Address</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Website</th>
                <th>Status</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $item)
                <tr>
                    <td>{{ $item->insurance_company_name }}</td>
                    <td>{{ $item->address ?? '—' }}</td>
                    <td>{{ \Shared\Infrastructure\Utils\PhoneHelper::format($item->phone) ?: '—' }}</td>
                    <td>{{ $item->email ?? '—' }}</td>
                    <td>{{ $item->website ?? '—' }}</td>
                    <td>{{ $item->deleted_at ? 'Inactive' : 'Active' }}</td>
                    <td>{{ $item->created_at?->format('F j, Y') ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        AquaShield CRM - Page <span class="pagenum"></span>
    </div>
</body>

</html>