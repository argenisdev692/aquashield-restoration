<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .container { width: 100%; max-width: 800px; margin: 0 auto; }

        /* ── Header ── */
        .header { width: 100%; display: table; margin-bottom: 20px; }
        .header-left  { display: table-cell; width: 50%; vertical-align: top; }
        .header-right { display: table-cell; width: 50%; vertical-align: top; padding-left: 80px; padding-top: 20px; }
        .logo { max-width: 160px; height: auto; margin-top: 8px; }
        .company-name { font-weight: bold; font-size: 13px; color: #1e3a5f; margin-bottom: 4px; }
        .company-info { font-size: 10px; line-height: 1.3; color: #555; margin-top: 4px; }

        /* ── Invoice title ── */
        .invoice-title {
            color: #1e3a5f;
            font-size: 20px;
            font-weight: bold;
            margin: 16px 0 10px;
            letter-spacing: 1px;
        }

        /* ── Bill to / Invoice info block ── */
        .invoice-details { width: 100%; display: table; margin-bottom: 18px; }
        .bill-to    { display: table-cell; width: 55%; vertical-align: top; }
        .invoice-info { display: table-cell; width: 45%; vertical-align: top; }
        .section-label { font-size: 9px; color: #888; font-weight: bold; letter-spacing: 1px; margin-bottom: 4px; text-transform: uppercase; }
        .info-table { border-collapse: collapse; margin-left: auto; }
        .info-table td { padding: 3px 6px; font-size: 11px; }
        .info-table .label { color: #888; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; padding-right: 16px; }
        .info-table .value { font-weight: bold; }

        /* ── Items table ── */
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 16px; margin-top: 12px; }
        table.items th {
            background-color: #1e3a5f;
            color: #fff;
            font-weight: bold;
            text-align: center;
            padding: 8px;
            border: 1px solid #14294a;
            font-size: 10px;
        }
        table.items td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
            vertical-align: middle;
        }
        table.items td.amount { text-align: right; }
        table.items td.qty    { text-align: center; }
        table.items td.rate   { text-align: right; }
        table.items tr:nth-child(even) { background-color: #f9fafb; }

        /* ── Totals ── */
        .totals-wrap { text-align: right; margin-top: 10px; }
        .totals-table { display: inline-table; border-collapse: collapse; min-width: 220px; }
        .totals-table td { padding: 5px 8px; font-size: 12px; }
        .totals-table .t-label { text-align: right; color: #666; }
        .totals-table .t-value { text-align: right; font-weight: bold; min-width: 90px; }
        .totals-table .balance-row td { border-top: 2px solid #1e3a5f; font-size: 14px; color: #1e3a5f; }

        /* ── Claim info ── */
        .claim-info { margin-top: 22px; font-size: 10px; border-top: 1px solid #e5e7eb; padding-top: 14px; }
        .claim-info table { border-collapse: collapse; }
        .claim-info td { padding: 3px 8px 3px 0; color: #555; }
        .claim-info .c-label { color: #888; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; padding-right: 12px; }
        .claim-info .c-value { font-weight: 600; color: #333; }

        /* ── Footer ── */
        .footer { margin-top: 30px; font-size: 9px; color: #999; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 10px; }
    </style>
</head>
<body>
<div class="container">

    {{-- ── Header ── --}}
    <div class="header">
        <div class="header-left">
            @if(file_exists(public_path('assets/logo/logo-png.png')))
                <img src="{{ public_path('assets/logo/logo-png.png') }}" alt="AquaShield" class="logo">
            @else
                <div style="font-size:18px;font-weight:bold;color:#1e3a5f;">AQUASHIELD</div>
            @endif
        </div>
        <div class="header-right">
            <div class="company-name">AQUASHIELD CRM</div>
            <div class="company-info">
                1522 Waugh Dr # 510, Houston, TX 77019<br>
                +1 (713) 364-6240<br>
                info@aquashieldrestoration.com
            </div>
        </div>
    </div>

    {{-- ── Invoice Title ── --}}
    <div class="invoice-title">INVOICE</div>

    {{-- ── Bill To / Invoice Info ── --}}
    <div class="invoice-details">
        <div class="bill-to">
            <div class="section-label">Bill To</div>
            <strong>{{ $invoice->bill_to_name }}</strong><br>
            @if($invoice->bill_to_address)
                {{ $invoice->bill_to_address }}<br>
            @endif
            @if($invoice->bill_to_phone)
                {{ $invoice->bill_to_phone }}<br>
            @endif
            @if($invoice->bill_to_email)
                {{ $invoice->bill_to_email }}
            @endif
        </div>
        <div class="invoice-info">
            <table class="info-table">
                <tr>
                    <td class="label">Invoice #</td>
                    <td class="value">{{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td class="label">Date</td>
                    <td class="value">{{ $invoice->invoice_date?->format('m/d/Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Status</td>
                    <td class="value">{{ ucfirst(str_replace('_', ' ', $invoice->status)) }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ── Items Table ── --}}
    <table class="items">
        <thead>
            <tr>
                <th style="text-align:left;">Service</th>
                <th style="text-align:left;">Description</th>
                <th style="text-align:center;width:60px;">Qty</th>
                <th style="text-align:right;width:90px;">Rate</th>
                <th style="text-align:right;width:100px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $item)
            <tr>
                <td>{{ $item->service_name }}</td>
                <td>{{ $item->description }}</td>
                <td class="qty">{{ number_format($item->quantity, 0) }}</td>
                <td class="rate">${{ number_format((float)$item->rate, 2) }}</td>
                <td class="amount">${{ number_format((float)$item->amount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center;color:#999;padding:14px;">No items on this invoice.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── Totals ── --}}
    <div class="totals-wrap">
        <table class="totals-table">
            @if((float)$invoice->subtotal !== (float)$invoice->balance_due)
            <tr>
                <td class="t-label">Subtotal</td>
                <td class="t-value">${{ number_format((float)$invoice->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td class="t-label">Tax</td>
                <td class="t-value">${{ number_format((float)$invoice->tax_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="balance-row">
                <td class="t-label"><strong>Balance Due</strong></td>
                <td class="t-value"><strong>${{ number_format((float)$invoice->balance_due, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    {{-- ── Claim / Insurance Info ── --}}
    @if($invoice->claim_number || $invoice->policy_number || $invoice->insurance_company)
    <div class="claim-info">
        <table>
            @if($invoice->claim_number)
            <tr>
                <td class="c-label">Claim #</td>
                <td class="c-value">{{ $invoice->claim_number }}</td>
            </tr>
            @endif
            @if($invoice->insurance_company)
            <tr>
                <td class="c-label">Insurance Co.</td>
                <td class="c-value">{{ $invoice->insurance_company }}</td>
            </tr>
            @endif
            @if($invoice->policy_number)
            <tr>
                <td class="c-label">Policy #</td>
                <td class="c-value">{{ $invoice->policy_number }}</td>
            </tr>
            @endif
            @if($invoice->date_of_loss)
            <tr>
                <td class="c-label">Date of Loss</td>
                <td class="c-value">{{ $invoice->date_of_loss?->format('m/d/Y') }}</td>
            </tr>
            @endif
            @if($invoice->type_of_loss)
            <tr>
                <td class="c-label">Type of Loss</td>
                <td class="c-value">{{ $invoice->type_of_loss }}</td>
            </tr>
            @endif
        </table>
    </div>
    @endif

    @if($invoice->notes)
    <div style="margin-top:16px;font-size:10px;color:#555;">
        <strong>Notes:</strong><br>{{ $invoice->notes }}
    </div>
    @endif

    {{-- ── Footer ── --}}
    <div class="footer">
        Payment is due within 30 days of the invoice date. Thank you for your business!<br>
        AquaShield CRM — Confidential &middot; Generated {{ $generatedAt }}
    </div>

</div>
</body>
</html>
