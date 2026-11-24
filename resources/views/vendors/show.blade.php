@extends('layouts.app')

@section('title', 'Vendor Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <h1 class="h3 page-heading mb-1">{{ $vendor->vendor_name }}</h1>
            <p class="text-muted mb-0">{{ $vendor->vendor_code }} · Pay purchase invoices for this vendor</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('vendor-payments.create', ['vendor_uid' => $vendor->uid, 'return_to' => 'vendor']) }}"
               class="btn btn-success">
                <i class="bi bi-cash-coin"></i>
                @if($balance > 0)
                    Pay Invoices (Rs. {{ number_format($balance, 2) }})
                @else
                    Pay Invoices
                @endif
            </a>
            <a href="{{ route('vendors.edit', $vendor->uid) }}" class="btn btn-primary">Edit</a>
            <a href="{{ route('vendors.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="bg-white border rounded p-3 h-100">
                <div class="text-muted small">Total Purchases</div>
                <div class="h4 mb-0">Rs. {{ number_format($totalPurchases, 2) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="bg-white border rounded p-3 h-100">
                <div class="text-muted small">Paid on Invoices</div>
                <div class="h4 mb-0">Rs. {{ number_format($totalPaid, 2) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="bg-white border rounded p-3 h-100" style="border-color:#fecaca !important; background:#fff7f7;">
                <div class="text-muted small">Outstanding Balance</div>
                <div class="h4 mb-0 text-danger">Rs. {{ number_format($balance, 2) }}</div>
                <div class="small text-muted">{{ $pendingInvoices->count() }} unpaid invoice(s)</div>
            </div>
        </div>
    </div>

    <div class="bg-white border rounded p-3 mb-3">
        <div class="invoice-meta-grid invoice-meta-form invoice-meta-readonly">
            <div class="invoice-meta-row">
                <div class="invoice-meta-title">Contact</div>
                <div class="invoice-meta-field">{{ $vendor->contact_person ?: '—' }}</div>
                <div class="invoice-meta-title">Mobile</div>
                <div class="invoice-meta-field">{{ $vendor->mobile_no ?: '—' }}</div>
            </div>
            <div class="invoice-meta-row">
                <div class="invoice-meta-title">City</div>
                <div class="invoice-meta-field">{{ $vendor->city ?: '—' }}</div>
                <div class="invoice-meta-title">Status</div>
                <div class="invoice-meta-field">{{ $vendor->is_active ? 'Active' : 'Inactive' }}</div>
            </div>
            <div class="invoice-meta-row">
                <div class="invoice-meta-title">Address</div>
                <div class="invoice-meta-field" style="grid-column: 2 / -1;">{{ $vendor->address ?: '—' }}</div>
            </div>
        </div>
    </div>

    @if($pendingInvoices->isNotEmpty())
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <strong>Unpaid / Partial Invoices</strong>
                <span class="text-muted small">Click Pay on a row to settle that invoice</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Date</th>
                                <th class="text-end">Net</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end">Balance</th>
                                <th>Status</th>
                                <th class="text-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingInvoices as $invoice)
                                <tr>
                                    <td>
                                        <a href="{{ route('purchase-invoices.show', $invoice->uid) }}">
                                            {{ $invoice->invoice_no }}
                                        </a>
                                        @if($invoice->supplier_invoice_no)
                                            <div class="small text-muted">Supplier #: {{ $invoice->supplier_invoice_no }}</div>
                                        @endif
                                    </td>
                                    <td>{{ optional($invoice->invoice_date)->format('d-M-Y') }}</td>
                                    <td class="text-end">{{ number_format((float) $invoice->net_amount, 2) }}</td>
                                    <td class="text-end">{{ number_format((float) $invoice->paid_amount, 2) }}</td>
                                    <td class="text-end fw-semibold text-danger">{{ number_format((float) $invoice->balance_amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $invoice->payment_status === 'Paid' ? 'success' : ($invoice->payment_status === 'Partial' ? 'warning' : 'secondary') }}">
                                            {{ $invoice->payment_status ?: 'Unpaid' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('vendor-payments.create', [
                                                'vendor_uid' => $vendor->uid,
                                                'invoice_uid' => $invoice->uid,
                                                'return_to' => 'vendor',
                                            ]) }}"
                                           class="btn btn-sm btn-success">
                                            Pay Rs. {{ number_format((float) $invoice->balance_amount, 2) }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-success">No outstanding purchase invoices for this vendor.</div>
    @endif

    <div class="card mb-3">
        <div class="card-header"><strong>All Purchase Invoices</strong></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Date</th>
                            <th class="text-end">Net</th>
                            <th class="text-end">Balance</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr>
                                <td>{{ $invoice->invoice_no }}</td>
                                <td>{{ optional($invoice->invoice_date)->format('d-M-Y') }}</td>
                                <td class="text-end">{{ number_format((float) $invoice->net_amount, 2) }}</td>
                                <td class="text-end">{{ number_format((float) $invoice->balance_amount, 2) }}</td>
                                <td>{{ $invoice->payment_status ?: '—' }}</td>
                                <td class="text-nowrap">
                                    <a href="{{ route('purchase-invoices.show', $invoice->uid) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                    @if((float) $invoice->balance_amount > 0 && ($invoice->invoice_status !== 'Cancelled'))
                                        <a href="{{ route('vendor-payments.create', [
                                                'vendor_uid' => $vendor->uid,
                                                'invoice_uid' => $invoice->uid,
                                                'return_to' => 'vendor',
                                            ]) }}" class="btn btn-sm btn-outline-success">Pay</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">No purchase invoices yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($invoices->hasPages())
                <div class="p-3">{{ $invoices->links() }}</div>
            @endif
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Recent Vendor Payments</strong>
            <a href="{{ route('vendor-payments.index', ['vendor_uid' => $vendor->uid]) }}" class="small">View all</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Payment No</th>
                            <th>Date</th>
                            <th>Mode</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td>{{ $payment->payment_no }}</td>
                                <td>{{ optional($payment->payment_date)->format('d-M-Y') }}</td>
                                <td>{{ $payment->payment_mode ?: '—' }}</td>
                                <td class="text-end">{{ number_format((float) $payment->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">No payments recorded yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
