<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoices Export</title>
    <style>
        @page { margin: 12mm; size: A4 landscape; }
        body {
            font-family: sans-serif;
            font-size: 10px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            width: 100%;
            display: table;
            margin-bottom: 16px;
            border-bottom: 2px solid #1e3a5f;
            padding-bottom: 10px;
        }
        .header-left { display: table-cell; width: 50%; vertical-align: middle; }
        .header-right { display: table-cell; width: 50%; text-align: right; vertical-align: middle; }
        .company-name { font-size: 14px; font-weight: bold; color: #1e3a5f; margin-bottom: 3px; }
        .report-title {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            color: #1e3a5f;
            margin: 12px 0 4px;
        }
        .report-meta {
            text-align: center;
            font-size: 8px;
            color: #666;
            margin-bottom: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        th {
            background-color: #1e3a5f;
            color: #fff;
            text-align: center;
            padding: 6px 4px;
            font-weight: bold;
            border: 1px solid #14294a;
            font-size: 8px;
        }
        td {
            padding: 5px 4px;
            border: 1px solid #dde1e7;
            text-align: center;
            vertical-align: middle;
            font-size: 8px;
        }
        tr:nth-child(even) { background-color: #f5f7fa; }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-active    { background-color: #d1fae5; color: #065f46; }
        .badge-suspended { background-color: #fee2e2; color: #991b1b; }
        .badge-draft     { background-color: #e5e7eb; color: #374151; }
        .badge-sent      { background-color: #dbeafe; color: #1e40af; }
        .badge-paid      { background-color: #d1fae5; color: #065f46; }
        .badge-cancelled { background-color: #fee2e2; color: #991b1b; }
        .badge-print_pdf { background-color: #ede9fe; color: #5b21b6; }
        tfoot td {
            font-weight: bold;
            background-color: #eef2f7;
        }
        .footer {
            margin-top: 18px;
            font-size: 8px;
            color: #999;
            text-align: center;
            border-top: 1px solid #dde1e7;
            padding-top: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <div class="company-name">AQUASHIELD CRM</div>
            <div style="font-size:8px;color:#666;">Invoice Management System</div>
        </div>
        <div class="header-right">
            <div style="font-size:9px;color:#666;">Generated: {{ $generatedAt }}</div>
            <div style="font-size:9px;color:#666;">Total records: {{ count($rows) }}</div>
        </div>
    </div>

    <div class="report-title">INVOICES REPORT</div>
    <div class="report-meta">AquaShield CRM — Confidential</div>

    <table>
        <thead>
            <tr>
                <th>Invoice #</th>
                <th>Bill To</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Subtotal</th>
                <th>Tax</th>
                <th>Balance Due</th>
                <th>Claim #</th>
                <th>Insurance Co.</th>
                <th>Invoice Date</th>
                <th>Record</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
            <tr>
                <td><strong>{{ $row['invoice_number'] }}</strong></td>
                <td style="text-align:left;">{{ $row['bill_to_name'] }}</td>
                <td style="text-align:left;">{{ $row['bill_to_email'] }}</td>
                <td>{{ $row['bill_to_phone'] }}</td>
                <td>
                    <span class="badge badge-{{ str_replace('_','-', $row['status']) }}">
                        {{ ucfirst(str_replace('_',' ', $row['status'])) }}
                    </span>
                </td>
                <td>{{ $row['subtotal'] }}</td>
                <td>{{ $row['tax_amount'] }}</td>
                <td><strong>{{ $row['balance_due'] }}</strong></td>
                <td>{{ $row['claim_number'] }}</td>
                <td style="text-align:left;">{{ $row['insurance_company'] }}</td>
                <td>{{ $row['invoice_date'] }}</td>
                <td>
                    <span class="badge {{ $row['record_status'] === 'Active' ? 'badge-active' : 'badge-suspended' }}">
                        {{ $row['record_status'] }}
                    </span>
                </td>
                <td>{{ $row['created_at'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="13" style="text-align:center;padding:16px;color:#999;">No invoices found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">AquaShield CRM — Confidential &middot; Generated on {{ $generatedAt }}</div>
</body>
</html>
