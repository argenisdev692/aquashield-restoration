<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
            margin: 0;
            padding: 24px;
        }

        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #22d3ee;
            padding-bottom: 12px;
        }

        .title {
            font-size: 18px;
            font-weight: 700;
            color: #0891b2;
        }

        .meta {
            margin-top: 4px;
            color: #6b7280;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background-color: #f3f4f6;
            color: #111827;
            text-align: center;
            padding: 8px;
            border: 1px solid #d1d5db;
            font-size: 10px;
            font-weight: 700;
        }

        td {
            padding: 8px;
            border: 1px solid #e5e7eb;
            text-align: center;
            vertical-align: middle;
            font-size: 10px;
        }

        tr:nth-child(even) {
            background-color: #fafafa;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 9px;
            font-weight: 700;
        }

        .status-active {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-suspended {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ $title }}</div>
        <div class="meta">Generated on: {{ $generatedAt }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>UUID</th>
                <th>Title</th>
                <th>Slug</th>
                <th>Category</th>
                <th>Publication Status</th>
                <th>Status</th>
                <th>Published At</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                @php
                    $isSuspended = !in_array($row['deleted_at'] ?? null, [null, '', '—', '-'], true);
                @endphp
                <tr>
                    <td>{{ $row['uuid'] }}</td>
                    <td>{{ $row['title'] }}</td>
                    <td>{{ $row['slug'] }}</td>
                    <td>{{ $row['category'] }}</td>
                    <td>{{ $row['publication_status'] }}</td>
                    <td>
                        <span class="status-badge {{ $isSuspended ? 'status-suspended' : 'status-active' }}">
                            {{ $isSuspended ? 'Suspended' : 'Active' }}
                        </span>
                    </td>
                    <td>{{ $row['published_at'] }}</td>
                    <td>{{ $row['created_at'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
