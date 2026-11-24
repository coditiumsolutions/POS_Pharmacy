<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Purchase {{ $invoice->invoice_no }}</title>
    <style>
        body { font-family: "Segoe UI", Arial, sans-serif; margin: 24px; color: #0f172a; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 0.9rem; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        th { background: #f8fafc; }
        .text-end { text-align: right; }
        .toolbar { margin-bottom: 12px; }
        @@media print { .toolbar { display: none; } }
    </style>
</head>
<body>
    <div class="toolbar">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()">Close</button>
    </div>
    <h1>Purchase Invoice {{ $invoice->invoice_no }}</h1>
    <p>Supplier: {{ $invoice->supplier_name ?: '—' }}
        @if($invoice->supplier_invoice_no) (Ref: {{ $invoice->supplier_invoice_no }})@endif
    </p>
    <p>Date: {{ optional($invoice->invoice_date)->format('d-M-Y') }}
        · Status: {{ $invoice->invoice_status }}
        · Payment: {{ $invoice->payment_status }}
    </p>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Batch</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Bonus</th>
                <th class="text-end">Base</th>
                <th class="text-end">Price</th>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->details as $i => $line)
            @php $inv = $line->inventory; @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ optional(optional($inv)->product)->product_name ?: ('Inv #'.$line->inventory_uid) }}</td>
                <td>{{ optional($inv)->batch_no ?: '—' }}</td>
                <td class="text-end">{{ number_format((float) $line->purchase_quantity, 3) }}</td>
                <td class="text-end">{{ number_format((float) $line->bonus_quantity, 3) }}</td>
                <td class="text-end">{{ number_format((float) $line->received_base_quantity, 3) }}</td>
                <td class="text-end">{{ number_format((float) $line->purchase_price, 2) }}</td>
                <td class="text-end">{{ number_format((float) $line->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <p><strong>Net Amount:</strong> {{ number_format((float) $invoice->net_amount, 2) }}
        · Paid: {{ number_format((float) $invoice->paid_amount, 2) }}
        · Balance: {{ number_format((float) $invoice->balance_amount, 2) }}
    </p>
</body>
</html>
