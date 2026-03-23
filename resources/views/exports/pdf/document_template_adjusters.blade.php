<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a2e; background: #ffffff; }
        .header { display: flex; justify-content: space-between; align-items: center; padding: 18px 24px 14px; border-bottom: 2px solid #6366f1; margin-bottom: 18px; }
        .header-left h1 { font-size: 18px; font-weight: 700; color: #6366f1; letter-spacing: -0.5px; }
        .header-left p { font-size: 10px; color: #7a7a90; margin-top: 2px; }
        .header-right { text-align: right; font-size: 10px; color: #7a7a90; }
        .header-right strong { display: block; font-size: 12px; color: #1a1a2e; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        thead tr { background: #6366f1; color: #ffffff; }
        thead th { padding: 9px 10px; text-align: left; font-size: 10px; font-weight: 700; letter-spacing: 0.4px; text-transform: uppercase; }
        tbody tr { border-bottom: 1px solid #e8e8ed; }
        tbody tr:nth-child(even) { background: #f8f8fc; }
        tbody td { padding: 8px 10px; font-size: 10px; color: #1a1a2e; vertical-align: top; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-type { background: #ede9fe; color: #6366f1; border: 1px solid #c4b5fd; }
        .empty-state { text-align: center; padding: 40px; color: #7a7a90; font-style: italic; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 8px 24px; border-top: 1px solid #e8e8ed; background: #f8f8fc; display: flex; justify-content: space-between; align-items: center; font-size: 9px; color: #7a7a90; }
        .footer strong { color: #6366f1; font-weight: 700; }
    </style>
</head>
<body>

    <div class="header">
        <div class="header-left">
            <h1>AquaShield CRM</h1>
            <p>{{ $title }}</p>
        </div>
        <div class="header-right">
            <strong>Generated At</strong>
            {{ $generatedAt }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Type</th>
                <th>Public Adjuster</th>
                <th>Uploaded By</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['template_description_adjuster'] }}</td>
                    <td>
                        <span class="badge badge-type">{{ $row['template_type_adjuster'] }}</span>
                    </td>
                    <td>{{ $row['public_adjuster_name'] }}</td>
                    <td>{{ $row['uploaded_by_name'] }}</td>
                    <td>{{ $row['created_at'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty-state">No document template adjusters found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <span><strong>AquaShield CRM</strong> — Confidential</span>
        <span>Total records: {{ count($rows) }}</span>
    </div>

</body>
</html>
