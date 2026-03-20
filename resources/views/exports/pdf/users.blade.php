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
            width: 100%;
            border-bottom: 2px solid #22d3ee;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .brand-table {
            width: 100%;
            border: none;
            margin: 0;
        }

        .brand-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }

        .logo {
            height: 46px;
        }

        .title {
            font-size: 18px;
            font-weight: 700;
            color: #0891b2;
            text-align: right;
        }

        .meta {
            margin-top: 4px;
            text-align: right;
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
            font-weight: 700;
            font-size: 10px;
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

        .footer {
            margin-top: 18px;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="header">
        <table class="brand-table">
            <tr>
                <td>
                    <img src="{{ public_path('img/Logo PNG.png') }}" class="logo" alt="Logo">
                </td>
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
                <th>UUID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>City</th>
                <th>Status</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $user)
                @php
                    $isSuspended = $user->deleted_at !== null;
                @endphp
                <tr>
                    <td>{{ $user->uuid }}</td>
                    <td>{{ $user->name }} {{ $user->last_name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ \Shared\Infrastructure\Utils\PhoneHelper::format($user->phone) ?: '—' }}</td>
                    <td>{{ $user->city ?: '—' }}</td>
                    <td>
                        <span class="status-badge {{ $isSuspended ? 'status-suspended' : 'status-active' }}">
                            {{ $isSuspended ? 'Suspended' : 'Active' }}
                        </span>
                    </td>
                    <td>{{ $user->created_at?->format('F j, Y') ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">AquaShield CRM</div>
</body>
</html>
