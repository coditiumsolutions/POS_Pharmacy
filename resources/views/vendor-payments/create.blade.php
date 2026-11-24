@extends('layouts.app')

@section('title', 'Pay Vendor')

@section('content')
@php
    $selectedVendorUid = old('vendor_uid', optional($vendor)->uid);
    $focusInvoice = $focusInvoice ?? null;
    $suggestedAmount = $suggestedAmount ?? null;
    $returnTo = $returnTo ?? request('return_to', 'vendor');
    $invoices = $invoices ?? collect();
    $pendingInvoices = $pendingInvoices ?? collect();
    $selectedInvoiceUid = old('selected_invoice_uid', optional($focusInvoice)->uid);
    $pendingTotal = (float) $pendingInvoices->sum('balance_amount');
@endphp
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h1 class="h3 page-heading mb-1">Pay Vendor</h1>
            <p class="text-muted mb-0">
                Use <strong>Vendor bill no</strong> to match the paper invoice from the vendor.
            </p>
        </div>
        <div class="text-muted small">Payment No: <strong>{{ $paymentNo }}</strong></div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @unless($vendor)
        <div class="bg-white border rounded p-3 mb-3">
            <form method="GET" action="{{ route('vendor-payments.create') }}" class="row g-2 align-items-end" id="vendorSelectForm">
                <input type="hidden" name="return_to" value="{{ $returnTo }}">
                <div class="col-md-6 col-lg-5">
                    <label class="form-label small mb-0" for="vendor_uid_select">Vendor *</label>
                    <select id="vendor_uid_select" name="vendor_uid" class="form-select" required>
                        <option value="">Select vendor</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->uid }}">{{ $v->vendor_name }} ({{ $v->vendor_code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Continue</button>
                </div>
            </form>
        </div>
    @endunless

    @if($vendor)
        <div class="bg-white border rounded p-3 mb-3">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                <div>
                    <div class="text-muted small">Vendor</div>
                    <div class="h5 mb-0">{{ $vendor->vendor_name }}</div>
                    <div class="small text-muted">{{ $vendor->vendor_code }}@if($vendor->mobile_no) · {{ $vendor->mobile_no }}@endif</div>
                </div>
                <div class="text-end">
                    <div class="text-muted small">Outstanding</div>
                    <div class="h5 mb-0 text-danger">Rs. {{ number_format($pendingTotal, 2) }}</div>
                    <a href="{{ route('vendor-payments.create', ['return_to' => $returnTo]) }}" class="small">Change vendor</a>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('vendor-payments.store') }}" id="vendorPaymentForm">
            @csrf
            <input type="hidden" name="vendor_uid" value="{{ $vendor->uid }}">
            <input type="hidden" name="branch_uid" value="{{ old('branch_uid', $defaultBranchUid) }}">
            <input type="hidden" name="return_to" value="{{ $returnTo }}">
            <input type="hidden" name="auto_apply" value="0">

            <div class="card mb-3">
                <div class="card-header">
                    <strong>1. Select purchase invoice</strong>
                    <div class="small text-muted fw-normal">
                        Match the vendor’s paper bill using <em>Vendor bill no</em>, then choose which invoice to pay.
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($invoices->isEmpty())
                        <div class="p-4 text-muted">
                            No purchase invoices for this vendor. Create a purchase under
                            <a href="{{ route('purchase-invoices.create') }}">Purchases</a>, then return here to pay.
                        </div>
                    @elseif($pendingInvoices->isEmpty())
                        <div class="p-4 text-muted">
                            No unpaid purchase invoices for this vendor. Create a purchase under
                            <a href="{{ route('purchase-invoices.create') }}">Purchases</a>, then return here to pay.
                        </div>
                        <div class="table-responsive border-top">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Vendor bill no</th>
                                        <th>Our purchase no</th>
                                        <th class="text-end">Bill amount</th>
                                        <th class="text-end">Already paid</th>
                                        <th class="text-end">Due now</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoices as $invoice)
                                        <tr class="table-light">
                                            <td>{{ optional($invoice->invoice_date)->format('d-M-Y') }}</td>
                                            <td>{{ $invoice->supplier_invoice_no ?: '—' }}</td>
                                            <td>{{ $invoice->invoice_no }}</td>
                                            <td class="text-end">{{ number_format((float) $invoice->net_amount, 2) }}</td>
                                            <td class="text-end">{{ number_format((float) $invoice->paid_amount, 2) }}</td>
                                            <td class="text-end">{{ number_format((float) $invoice->balance_amount, 2) }}</td>
                                            <td>{{ $invoice->payment_status ?: 'Paid' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="invoiceSelectTable">
                                <thead>
                                    <tr>
                                        <th style="width:42px"></th>
                                        <th>Date</th>
                                        <th>Vendor bill no</th>
                                        <th>Our purchase no</th>
                                        <th class="text-end">Bill amount</th>
                                        <th class="text-end">Already paid</th>
                                        <th class="text-end">Due now</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoices as $invoice)
                                        @php
                                            $due = (float) $invoice->balance_amount;
                                            $payable = $due > 0;
                                            $isSelected = $payable && (string) $selectedInvoiceUid === (string) $invoice->uid;
                                        @endphp
                                        <tr class="{{ $isSelected ? 'table-success' : ($payable ? '' : 'table-light') }}"
                                            data-payable="{{ $payable ? '1' : '0' }}"
                                            data-due="{{ number_format($due, 2, '.', '') }}"
                                            data-invoice="{{ $invoice->uid }}"
                                            data-bill="{{ $invoice->supplier_invoice_no }}"
                                            data-purchase="{{ $invoice->invoice_no }}">
                                            <td>
                                                @if($payable)
                                                    <input type="radio"
                                                           class="form-check-input invoice-radio"
                                                           name="selected_invoice_uid"
                                                           value="{{ $invoice->uid }}"
                                                           data-due="{{ number_format($due, 2, '.', '') }}"
                                                           data-bill="{{ $invoice->supplier_invoice_no }}"
                                                           data-purchase="{{ $invoice->invoice_no }}"
                                                           @checked($isSelected)
                                                           required>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>{{ optional($invoice->invoice_date)->format('d-M-Y') }}</td>
                                            <td class="fw-semibold">{{ $invoice->supplier_invoice_no ?: '—' }}</td>
                                            <td>
                                                <a href="{{ route('purchase-invoices.show', $invoice->uid) }}">
                                                    {{ $invoice->invoice_no }}
                                                </a>
                                            </td>
                                            <td class="text-end">{{ number_format((float) $invoice->net_amount, 2) }}</td>
                                            <td class="text-end">{{ number_format((float) $invoice->paid_amount, 2) }}</td>
                                            <td class="text-end {{ $payable ? 'text-danger fw-semibold' : '' }}">
                                                {{ number_format($due, 2) }}
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $invoice->payment_status === 'Paid' ? 'success' : ($invoice->payment_status === 'Partial' ? 'warning' : 'secondary') }}">
                                                    {{ $invoice->payment_status ?: 'Unpaid' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            @if($pendingInvoices->isNotEmpty())
                <div class="card mb-3">
                    <div class="card-header">
                        <strong>2. Enter payment details</strong>
                        <div class="small text-muted fw-normal">
                            Amount defaults to the full due on the selected invoice. Change it for a partial payment.
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-light border py-2 mb-3" id="selectedInvoiceSummary">
                            Selected:
                            <strong id="summaryPurchase">—</strong>
                            <span class="text-muted">· Vendor bill</span>
                            <strong id="summaryBill">—</strong>
                            <span class="text-muted">· Due now</span>
                            <strong class="text-danger" id="summaryDue">—</strong>
                        </div>

                        <div class="invoice-meta-grid invoice-meta-form">
                            <div class="invoice-meta-row">
                                <label class="invoice-meta-title" for="payment_date">Payment Date *</label>
                                <div class="invoice-meta-field">
                                    <input id="payment_date" type="text" name="payment_date"
                                           class="form-control form-control-sm invoice-meta-input report-date-input"
                                           value="{{ old('payment_date', now()->format('d-M-Y')) }}"
                                           placeholder="10-Nov-2026" required autocomplete="off">
                                </div>
                                <label class="invoice-meta-title" for="amount">Amount *</label>
                                <div class="invoice-meta-field">
                                    <input id="amount" type="number" step="0.01" min="0.01" name="amount"
                                           class="form-control form-control-sm invoice-meta-input"
                                           value="{{ old('amount', $suggestedAmount !== null ? number_format((float) $suggestedAmount, 2, '.', '') : '') }}"
                                           required>
                                    <div class="form-text">Max = due now on the selected invoice.</div>
                                </div>
                            </div>

                            <div class="invoice-meta-row">
                                <label class="invoice-meta-title" for="payment_mode">Payment Mode *</label>
                                <div class="invoice-meta-field">
                                    <select id="payment_mode" name="payment_mode" class="form-select form-select-sm invoice-meta-input" required>
                                        @forelse($paymentMethods as $method)
                                            <option value="{{ $method->payment_method_name }}"
                                                    data-uid="{{ $method->uid }}"
                                                    @selected(old('payment_mode', 'Cash') === $method->payment_method_name)>
                                                {{ $method->payment_method_name }}
                                            </option>
                                        @empty
                                            <option value="Cash" @selected(old('payment_mode', 'Cash') === 'Cash')>Cash</option>
                                            <option value="Bank Transfer" @selected(old('payment_mode') === 'Bank Transfer')>Bank Transfer</option>
                                            <option value="Cheque" @selected(old('payment_mode') === 'Cheque')>Cheque</option>
                                        @endforelse
                                    </select>
                                    <input type="hidden" name="payment_method_uid" id="payment_method_uid" value="{{ old('payment_method_uid') }}">
                                </div>
                                <label class="invoice-meta-title" for="reference_no">Reference No</label>
                                <div class="invoice-meta-field">
                                    <input id="reference_no" type="text" name="reference_no"
                                           class="form-control form-control-sm invoice-meta-input"
                                           value="{{ old('reference_no') }}"
                                           placeholder="Cheque / transfer ref">
                                </div>
                            </div>

                            <div class="invoice-meta-row">
                                <label class="invoice-meta-title" for="remarks">Remarks</label>
                                <div class="invoice-meta-field" style="grid-column: 2 / -1;">
                                    <input id="remarks" type="text" name="remarks"
                                           class="form-control form-control-sm invoice-meta-input"
                                           value="{{ old('remarks') }}">
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2-circle"></i> Add Payment
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="btnUseFullDue">Use full due</button>
                            @if($returnTo === 'vendor')
                                <a href="{{ route('vendors.show', $vendor->uid) }}" class="btn btn-outline-secondary">Cancel</a>
                            @else
                                <a href="{{ route('vendors.index') }}" class="btn btn-outline-secondary">Back to Vendors</a>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="mb-3">
                    <a href="{{ route('vendors.index') }}" class="btn btn-outline-secondary">Back to Vendors</a>
                    <a href="{{ route('purchase-invoices.create') }}" class="btn btn-primary">Create Purchase</a>
                </div>
            @endif
        </form>
    @endif
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    #invoiceSelectTable tbody tr[data-payable="1"] { cursor: pointer; }
    #invoiceSelectTable tbody tr.table-success td { background-color: #d1e7dd !important; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    flatpickr('.report-date-input', {
        dateFormat: 'd-M-Y',
        allowInput: true,
        disableMobile: true
    });

    var vendorSelect = document.getElementById('vendor_uid_select');
    if (vendorSelect) {
        vendorSelect.addEventListener('change', function () {
            if (this.value) document.getElementById('vendorSelectForm').submit();
        });
    }

    var modeSelect = document.getElementById('payment_mode');
    var methodUid = document.getElementById('payment_method_uid');
    if (modeSelect && methodUid) {
        function syncMethodUid() {
            var opt = modeSelect.options[modeSelect.selectedIndex];
            methodUid.value = opt && opt.dataset.uid ? opt.dataset.uid : '';
        }
        modeSelect.addEventListener('change', syncMethodUid);
        syncMethodUid();
    }

    var amountInput = document.getElementById('amount');
    var summaryPurchase = document.getElementById('summaryPurchase');
    var summaryBill = document.getElementById('summaryBill');
    var summaryDue = document.getElementById('summaryDue');
    var remarksInput = document.getElementById('remarks');

    function selectInvoice(radio, syncAmount) {
        if (!radio) return;
        var due = radio.getAttribute('data-due') || '0';
        var bill = radio.getAttribute('data-bill') || '';
        var purchase = radio.getAttribute('data-purchase') || '';

        document.querySelectorAll('#invoiceSelectTable tbody tr').forEach(function (tr) {
            tr.classList.toggle('table-success', tr.contains(radio));
        });

        if (summaryPurchase) summaryPurchase.textContent = purchase || '—';
        if (summaryBill) summaryBill.textContent = bill || '—';
        if (summaryDue) summaryDue.textContent = 'Rs. ' + Number(due).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        if (amountInput) {
            amountInput.max = due;
            if (syncAmount) amountInput.value = Number(due).toFixed(2);
        }

        if (remarksInput && (!remarksInput.value || remarksInput.dataset.auto === '1')) {
            remarksInput.value = 'Payment for ' + (bill ? ('vendor bill ' + bill + ' / ') : '') + purchase;
            remarksInput.dataset.auto = '1';
        }
    }

    document.querySelectorAll('.invoice-radio').forEach(function (radio) {
        radio.addEventListener('change', function () {
            selectInvoice(radio, true);
        });
    });

    document.querySelectorAll('#invoiceSelectTable tbody tr[data-payable="1"]').forEach(function (tr) {
        tr.addEventListener('click', function (e) {
            if (e.target && e.target.matches('input, a, button')) return;
            var radio = tr.querySelector('.invoice-radio');
            if (!radio) return;
            radio.checked = true;
            radio.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });

    var checked = document.querySelector('.invoice-radio:checked');
    if (checked) selectInvoice(checked, !amountInput || !amountInput.value);

    var btnFull = document.getElementById('btnUseFullDue');
    if (btnFull) {
        btnFull.addEventListener('click', function () {
            var radio = document.querySelector('.invoice-radio:checked');
            if (radio && amountInput) {
                amountInput.value = Number(radio.getAttribute('data-due') || '0').toFixed(2);
            }
        });
    }

    if (remarksInput) {
        remarksInput.addEventListener('input', function () {
            remarksInput.dataset.auto = '0';
        });
    }
});
</script>
@endpush
