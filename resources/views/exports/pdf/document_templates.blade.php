<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
            color: #333;
        }

        .header {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 12px;
        }

        .header img {
            height: 40px;
            margin-right: 16px;
        }

        .header-info h1 {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 4px 0;
            color: #111827;
        }

        .header-info p {
            font-size: 10px;
            color: #6b7280;
            margin: 0;
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
            margin-top: 24px;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
        }

        .empty-row td {
            text-align: center;
            color: #6b7280;
            font-style: italic;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-info">
            <h1>{{ $title }}</h1>
            <p>AquaShield CRM &mdash; Generated at: {{ $generatedAt }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Template Name</th>
                <th>Description</th>
                <th>Type</th>
                <th>Uploaded By</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['template_name'] }}</td>
                    <td>{{ $row['template_description'] }}</td>
                    <td>{{ $row['template_type'] }}</td>
                    <td>{{ $row['uploaded_by_name'] }}</td>
                    <td>{{ $row['created_at'] }}</td>
                </tr>
            @empty
                <tr class="empty-row">
                    <td colspan="6">No document templates found for the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        AquaShield CRM &mdash; Confidential &mdash; Total records: {{ count($rows) }}
    </div>
</body>
</html>
