@extends('layouts.app')

@section('title', 'Edit Purchase Invoice')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h1 class="h3 page-heading mb-1">Edit Purchase Invoice</h1>
            <p class="text-muted mb-0">Header & payment only. Line items are fixed after save.</p>
        </div>
        <div class="text-muted small">Invoice No: <strong>{{ $invoice->invoice_no }}</strong></div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-success shadow-sm" role="alert">
            <strong><i class="bi bi-check-circle"></i> Saved:</strong> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-danger shadow-sm" role="alert">
            <strong><i class="bi bi-x-circle"></i> Not saved:</strong> {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger border-danger shadow-sm" role="alert">
            <strong>Fix these issues:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('purchase-invoices.update', $invoice->uid) }}">
        @csrf
        @method('PUT')

        <div class="bg-white border rounded p-3 mb-3">
            <div class="invoice-meta-grid invoice-meta-form">
                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="invoice_date">Invoice Date *</label>
                    <div class="invoice-meta-field">
                        <input id="invoice_date" type="date" name="invoice_date"
                               class="form-control form-control-sm invoice-meta-input"
                               value="{{ old('invoice_date', optional($invoice->invoice_date)->format('Y-m-d')) }}" required>
                    </div>
                    <label class="invoice-meta-title" for="received_date">Received Date</label>
                    <div class="invoice-meta-field">
                        <input id="received_date" type="date" name="received_date"
                               class="form-control form-control-sm invoice-meta-input"
                               value="{{ old('received_date', optional($invoice->received_date)->format('Y-m-d')) }}">
                    </div>
                </div>

                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="supplier_name">Supplier *</label>
                    <div class="invoice-meta-field">
                        <input id="supplier_name" type="text" name="supplier_name"
                               class="form-control form-control-sm invoice-meta-input"
                               value="{{ old('supplier_name', $invoice->supplier_name) }}" required>
                    </div>
                    <label class="invoice-meta-title" for="supplier_invoice_no">Supplier Invoice No</label>
                    <div class="invoice-meta-field">
                        <input id="supplier_invoice_no" type="text" name="supplier_invoice_no"
                               class="form-control form-control-sm invoice-meta-input"
                               value="{{ old('supplier_invoice_no', $invoice->supplier_invoice_no) }}">
                    </div>
                </div>

                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="invoice_status">Invoice Status</label>
                    <div class="invoice-meta-field">
                        <select id="invoice_status" name="invoice_status" class="form-select form-select-sm invoice-meta-input">
                            @foreach(['Draft','Received','Cancelled'] as $st)
                                <option value="{{ $st }}" @selected(old('invoice_status', $invoice->invoice_status) === $st)>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label class="invoice-meta-title" for="payment_status">Payment Status</label>
                    <div class="invoice-meta-field">
                        <select id="payment_status" name="payment_status" class="form-select form-select-sm invoice-meta-input">
                            @foreach(['Unpaid','Partial','Paid'] as $ps)
                                <option value="{{ $ps }}" @selected(old('payment_status', $invoice->payment_status) === $ps)>{{ $ps }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="discount_amount">Discount</label>
                    <div class="invoice-meta-field">
                        <input id="discount_amount" type="number" step="0.01" min="0" name="discount_amount"
                               class="form-control form-control-sm invoice-meta-input"
                               value="{{ old('discount_amount', number_format((float) $invoice->discount_amount, 2, '.', '')) }}">
                    </div>
                    <label class="invoice-meta-title" for="tax_amount">Tax</label>
                    <div class="invoice-meta-field">
                        <input id="tax_amount" type="number" step="0.01" min="0" name="tax_amount"
                               class="form-control form-control-sm invoice-meta-input"
                               value="{{ old('tax_amount', number_format((float) $invoice->tax_amount, 2, '.', '')) }}">
                    </div>
                </div>

                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="other_charges">Other Charges</label>
                    <div class="invoice-meta-field">
                        <input id="other_charges" type="number" step="0.01" min="0" name="other_charges"
                               class="form-control form-control-sm invoice-meta-input"
                               value="{{ old('other_charges', number_format((float) $invoice->other_charges, 2, '.', '')) }}">
                    </div>
                    <label class="invoice-meta-title" for="paid_amount">Paid Amount</label>
                    <div class="invoice-meta-field">
                        <input id="paid_amount" type="number" step="0.01" min="0" name="paid_amount"
                               class="form-control form-control-sm invoice-meta-input"
                               value="{{ old('paid_amount', number_format((float) $invoice->paid_amount, 2, '.', '')) }}">
                    </div>
                </div>

                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="payment_method">Payment Method</label>
                    <div class="invoice-meta-field">
                        <input id="payment_method" type="text" name="payment_method"
                               class="form-control form-control-sm invoice-meta-input"
                               value="{{ old('payment_method', $invoice->payment_method) }}">
                    </div>
                    <label class="invoice-meta-title" for="remarks">Remarks</label>
                    <div class="invoice-meta-field">
                        <input id="remarks" type="text" name="remarks"
                               class="form-control form-control-sm invoice-meta-input"
                               value="{{ old('remarks', $invoice->remarks) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><strong>Line items (read-only)</strong></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Batch</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->details as $i => $line)
                            @php $inv = $line->inventory; @endphp
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ optional(optional($inv)->product)->product_name ?: '—' }}</td>
                                <td>{{ optional($inv)->batch_no ?: '—' }}</td>
                                <td class="text-end">{{ number_format((float) $line->purchase_quantity, 3) }}</td>
                                <td class="text-end">{{ number_format((float) $line->purchase_price, 2) }}</td>
                                <td class="text-end">{{ number_format((float) $line->line_total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Update Purchase</button>
            <a href="{{ route('purchase-invoices.show', $invoice->uid) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
