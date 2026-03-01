<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Products Export</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #0ea5e9;
        }
        .header h1 {
            font-size: 24px;
            color: #0ea5e9;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 11px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        thead {
            background-color: #0ea5e9;
            color: white;
        }
        th {
            padding: 10px 8px;
            text-align: left;
            font-weight: 600;
            font-size: 10px;
            text-transform: uppercase;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9px;
        }
        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        tbody tr:hover {
            background-color: #f3f4f6;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: 600;
        }
        .status-active {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-deleted {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #999;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }
        .price {
            text-align: right;
            font-weight: 600;
            color: #059669;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Products Catalog</h1>
        <p>Generated on {{ $generatedAt }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 25%;">Name</th>
                <th style="width: 30%;">Description</th>
                <th style="width: 15%;">Category</th>
                <th style="width: 10%;">Price</th>
                <th style="width: 10%;">Unit</th>
                <th style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td><strong>{{ $product->product_name }}</strong></td>
                    <td>{{ Str::limit($product->product_description ?? 'N/A', 80) }}</td>
                    <td>{{ $product->category?->category_name ?? 'N/A' }}</td>
                    <td class="price">${{ number_format($product->price, 2) }}</td>
                    <td>{{ $product->unit }}</td>
                    <td>
                        <span class="status-badge {{ $product->deleted_at ? 'status-deleted' : 'status-active' }}">
                            {{ $product->deleted_at ? 'Deleted' : 'Active' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: #999;">
                        No products found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>AquaShield CRM - Products Export | Total: {{ $products->count() }} products</p>
    </div>
</body>
</html>
