<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; margin: 0; padding: 24px; }
        .header { width: 100%; border-bottom: 2px solid #22d3ee; padding-bottom: 12px; margin-bottom: 18px; }
        .brand-table { width: 100%; border: none; margin: 0; }
        .brand-table td { border: none; padding: 0; vertical-align: middle; }
        .logo { height: 46px; }
        .title { font-size: 18px; font-weight: 700; color: #0891b2; text-align: right; }
        .meta { margin-top: 4px; text-align: right; color: #6b7280; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f3f4f6; color: #111827; border: 1px solid #d1d5db; padding: 8px; font-size: 10px; text-align: left; }
        td { border: 1px solid #e5e7eb; padding: 8px; font-size: 10px; vertical-align: top; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 999px; font-size: 9px; font-weight: 700; }
        .badge-active   { background-color: #dcfce7; color: #166534; }
        .badge-inactive { background-color: #fef9c3; color: #854d0e; }
        .badge-deleted  { background-color: #fee2e2; color: #991b1b; }
        .footer { margin-top: 18px; text-align: center; font-size: 9px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="header">
        <table class="brand-table">
            <tr>
                <td><img src="{{ public_path('img/Logo PNG.png') }}" class="logo" alt="AquaShield CRM"></td>
                <td>
                    <div class="title">{{ $title }}</div>
                    <div class="meta">Generated on: {{ $generatedAt }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Service Category</th>
                <th>Item Status</th>
                <th>Record</th>
                <th>Created At</th>
                <th>Deleted At</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                @php
                    $itemStatus   = $row->status ?? 'active';
                    $isDeleted    = isset($row->deleted_at) && $row->deleted_at !== null && $row->deleted_at !== '—';
                    $itemBadge    = $itemStatus === 'active' ? 'badge-active' : 'badge-inactive';
                    $recordBadge  = $isDeleted ? 'badge-deleted' : 'badge-active';
                @endphp
                <tr>
                    <td>{{ $row->title ?? '—' }}</td>
                    <td>{{ $row->description ?? '—' }}</td>
                    <td>{{ $row->service_category ?? '—' }}</td>
                    <td><span class="badge {{ $itemBadge }}">{{ ucfirst($itemStatus) }}</span></td>
                    <td><span class="badge {{ $recordBadge }}">{{ $isDeleted ? 'Deleted' : 'Active' }}</span></td>
                    <td>{{ $row->created_at ?? '—' }}</td>
                    <td>{{ $row->deleted_at ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center; color:#6b7280;">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">AquaShield CRM &mdash; Confidential</div>
</body>
</html>
