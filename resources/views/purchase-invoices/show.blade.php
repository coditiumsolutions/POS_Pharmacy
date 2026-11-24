@extends('layouts.app')

@section('title', 'Purchase Invoice')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success border-success shadow-sm" role="alert">
            <strong><i class="bi bi-check-circle"></i> Saved:</strong> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-danger shadow-sm" role="alert">
            <strong><i class="bi bi-x-circle"></i> Error:</strong> {{ session('error') }}
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h1 class="h3 page-heading mb-1">Purchase Invoice</h1>
            <p class="text-muted mb-0">{{ $invoice->invoice_no }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('purchase-invoices.edit', $invoice->uid) }}" class="btn btn-primary">Edit</a>
            @if(Route::has('purchase-invoices.print'))
                <a href="{{ route('purchase-invoices.print', $invoice->uid) }}" class="btn btn-outline-dark" target="_blank">Print</a>
            @endif
            <a href="{{ route('purchase-invoices.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>

    <div class="bg-white border rounded p-3 mb-3">
        <div class="invoice-meta-grid invoice-meta-form invoice-meta-readonly">
            <div class="invoice-meta-row">
                <div class="invoice-meta-title">Invoice No</div>
                <div class="invoice-meta-field">{{ $invoice->invoice_no }}</div>
                <div class="invoice-meta-title">Invoice Date</div>
                <div class="invoice-meta-field">{{ optional($invoice->invoice_date)->format('d-M-Y') }}</div>
            </div>
            <div class="invoice-meta-row">
                <div class="invoice-meta-title">Supplier</div>
                <div class="invoice-meta-field">{{ $invoice->supplier_name ?: '—' }}</div>
                <div class="invoice-meta-title">Supplier Invoice</div>
                <div class="invoice-meta-field">{{ $invoice->supplier_invoice_no ?: '—' }}</div>
            </div>
            <div class="invoice-meta-row">
                <div class="invoice-meta-title">Received Date</div>
                <div class="invoice-meta-field">{{ optional($invoice->received_date)->format('d-M-Y') ?? '—' }}</div>
                <div class="invoice-meta-title">Invoice Status</div>
                <div class="invoice-meta-field">{{ $invoice->invoice_status }}</div>
            </div>
            <div class="invoice-meta-row">
                <div class="invoice-meta-title">Subtotal</div>
                <div class="invoice-meta-field">{{ number_format((float) $invoice->subtotal, 2) }}</div>
                <div class="invoice-meta-title">Discount</div>
                <div class="invoice-meta-field">{{ number_format((float) $invoice->discount_amount, 2) }}</div>
            </div>
            <div class="invoice-meta-row">
                <div class="invoice-meta-title">Tax</div>
                <div class="invoice-meta-field">{{ number_format((float) $invoice->tax_amount, 2) }}</div>
                <div class="invoice-meta-title">Other Charges</div>
                <div class="invoice-meta-field">{{ number_format((float) $invoice->other_charges, 2) }}</div>
            </div>
            <div class="invoice-meta-row">
                <div class="invoice-meta-title">Net Amount</div>
                <div class="invoice-meta-field">{{ number_format((float) $invoice->net_amount, 2) }}</div>
                <div class="invoice-meta-title">Payment Status</div>
                <div class="invoice-meta-field">{{ $invoice->payment_status }} (Paid {{ number_format((float) $invoice->paid_amount, 2) }} / Bal {{ number_format((float) $invoice->balance_amount, 2) }})</div>
            </div>
            @if($invoice->remarks)
            <div class="invoice-meta-row">
                <div class="invoice-meta-title">Remarks</div>
                <div class="invoice-meta-field" style="grid-column: 2 / -1;">{{ $invoice->remarks }}</div>
            </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Batch</th>
                            <th>Expiry</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Bonus</th>
                            <th class="text-end">Loose</th>
                            <th class="text-end">Base Qty</th>
                            <th class="text-end">Price</th>
                            <th class="text-end">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->details as $i => $line)
                        @php $inv = $line->inventory; @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ optional(optional($inv)->product)->product_name ?: ('Inv #'.($line->inventory_uid)) }}</td>
                            <td>{{ optional($inv)->batch_no ?: '—' }}</td>
                            <td>{{ optional(optional($inv)->expiry_date)->format('d-M-Y') ?? '—' }}</td>
                            <td class="text-end">{{ number_format((float) $line->purchase_quantity, 3) }}</td>
                            <td class="text-end">{{ number_format((float) $line->bonus_quantity, 3) }}</td>
                            <td class="text-end">{{ number_format((float) $line->loose_quantity, 3) }}</td>
                            <td class="text-end">{{ number_format((float) $line->received_base_quantity, 3) }}</td>
                            <td class="text-end">{{ number_format((float) $line->purchase_price, 2) }}</td>
                            <td class="text-end">{{ number_format((float) $line->line_total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
