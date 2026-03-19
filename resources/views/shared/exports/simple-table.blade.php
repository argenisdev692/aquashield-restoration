<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
        }
        h1 {
            margin: 0 0 8px;
            font-size: 18px;
        }
        p {
            margin: 0 0 12px;
            color: #4b5563;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th,
        td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #f3f4f6;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>Generated at: {{ $generatedAt }}</p>

    <table>
        <thead>
            <tr>
                @foreach ($headings as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $value)
                        <td>{{ $value }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headings) }}">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
