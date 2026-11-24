@extends('layouts.app')

@section('title', 'Create Purchase Invoice')

@section('content')
@php
    $maxLines = 15;
@endphp
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h1 class="h3 page-heading mb-1">Create Purchase Invoice</h1>
            <p class="text-muted mb-0">Creates inventory batches and stock when status is Received.</p>
        </div>
        <div class="text-muted small">Invoice No: <strong>{{ $invoiceNo }}</strong></div>
    </div>

    <div id="purchaseAlert" class="alert d-none" role="alert"></div>

    @if(session('success'))
        <div class="alert alert-success border-success shadow-sm" id="purchaseServerSuccess" role="alert">
            <strong><i class="bi bi-check-circle"></i> Saved:</strong> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-danger shadow-sm" id="purchaseServerError" role="alert">
            <strong><i class="bi bi-x-circle"></i> Not saved:</strong> {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-danger shadow-sm" id="purchaseValidationErrors" role="alert">
            <strong><i class="bi bi-x-circle"></i> Purchase was not saved. Fix these issues:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('purchase-invoices.store') }}" id="purchaseForm" novalidate>
        @csrf

        <div class="bg-white border rounded p-3 mb-3">
            <div class="invoice-meta-grid invoice-meta-form">
                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="invoice_date">Invoice Date *</label>
                    <div class="invoice-meta-field">
                        <input id="invoice_date" type="date" name="invoice_date"
                               class="form-control form-control-sm invoice-meta-input"
                               value="{{ old('invoice_date', now()->format('Y-m-d')) }}" required>
                    </div>
                    <label class="invoice-meta-title" for="received_date">Received Date</label>
                    <div class="invoice-meta-field">
                        <input id="received_date" type="date" name="received_date"
                               class="form-control form-control-sm invoice-meta-input"
                               value="{{ old('received_date', now()->format('Y-m-d')) }}">
                    </div>
                </div>

                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="supplier_name">Supplier *</label>
                    <div class="invoice-meta-field">
                        <input id="supplier_name" type="text" name="supplier_name"
                               class="form-control form-control-sm invoice-meta-input {{ $errors->has('supplier_name') ? 'is-invalid' : '' }}"
                               value="{{ old('supplier_name') }}" required>
                        @error('supplier_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <label class="invoice-meta-title" for="supplier_invoice_no">Supplier Invoice No</label>
                    <div class="invoice-meta-field">
                        <input id="supplier_invoice_no" type="text" name="supplier_invoice_no"
                               class="form-control form-control-sm invoice-meta-input"
                               value="{{ old('supplier_invoice_no') }}">
                    </div>
                </div>

                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="invoice_status">Invoice Status</label>
                    <div class="invoice-meta-field">
                        <select id="invoice_status" name="invoice_status" class="form-select form-select-sm invoice-meta-input">
                            @foreach(['Received','Draft'] as $st)
                                <option value="{{ $st }}" @selected(old('invoice_status', 'Received') === $st)>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label class="invoice-meta-title" for="payment_method">Payment Method</label>
                    <div class="invoice-meta-field">
                        <input id="payment_method" type="text" name="payment_method"
                               class="form-control form-control-sm invoice-meta-input"
                               value="{{ old('payment_method') }}" placeholder="Cash / Bank / Credit">
                    </div>
                </div>

                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="discount_amount">Header Discount</label>
                    <div class="invoice-meta-field">
                        <input id="discount_amount" type="number" step="0.01" min="0" name="discount_amount"
                               class="form-control form-control-sm invoice-meta-input header-money"
                               value="{{ old('discount_amount', '0') }}">
                    </div>
                    <label class="invoice-meta-title" for="tax_amount">Header Tax</label>
                    <div class="invoice-meta-field">
                        <input id="tax_amount" type="number" step="0.01" min="0" name="tax_amount"
                               class="form-control form-control-sm invoice-meta-input header-money"
                               value="{{ old('tax_amount', '0') }}">
                    </div>
                </div>

                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="other_charges">Other Charges</label>
                    <div class="invoice-meta-field">
                        <input id="other_charges" type="number" step="0.01" min="0" name="other_charges"
                               class="form-control form-control-sm invoice-meta-input header-money"
                               value="{{ old('other_charges', '0') }}">
                    </div>
                    <label class="invoice-meta-title" for="paid_amount">Paid Amount</label>
                    <div class="invoice-meta-field">
                        <input id="paid_amount" type="number" step="0.01" min="0" name="paid_amount"
                               class="form-control form-control-sm invoice-meta-input"
                               value="{{ old('paid_amount', '0') }}">
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
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <strong>Items (max {{ $maxLines }})</strong>
                <span class="badge text-bg-primary" id="itemsAddedBadge">0 / {{ $maxLines }} items</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0" id="purchaseItemsTable">
                        <thead>
                            <tr>
                                <th style="width:32px">#</th>
                                <th>Product *</th>
                                <th style="width:100px">Batch *</th>
                                <th style="width:110px">Expiry</th>
                                <th style="width:80px" class="text-end">Qty *</th>
                                <th style="width:70px" class="text-end">Bonus</th>
                                <th style="width:70px" class="text-end">Loose</th>
                                <th style="width:70px" class="text-end">U/P</th>
                                <th style="width:90px" class="text-end">Price *</th>
                                <th style="width:90px" class="text-end">Sale</th>
                                <th style="width:90px" class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for($i = 0; $i < $maxLines; $i++)
                                <tr class="purchase-line" data-index="{{ $i }}">
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <select name="items[{{ $i }}][product_uid]"
                                                class="form-select form-select-sm item-product"
                                                data-index="{{ $i }}">
                                            <option value="">Select</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->uid }}"
                                                        data-name="{{ $product->product_name }}"
                                                        data-sale="{{ $product->sale_price }}"
                                                        data-upp="{{ $product->units_per_pack ?? 1 }}"
                                                        @selected(old("items.$i.product_uid") == $product->uid)>
                                                    {{ $product->product_name }}
                                                    @if($product->strength) — {{ $product->strength }}@endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $i }}][batch_no]"
                                               class="form-control form-control-sm item-batch"
                                               value="{{ old("items.$i.batch_no") }}" placeholder="Batch">
                                    </td>
                                    <td>
                                        <input type="date" name="items[{{ $i }}][expiry_date]"
                                               class="form-control form-control-sm item-expiry"
                                               value="{{ old("items.$i.expiry_date") }}">
                                    </td>
                                    <td>
                                        <input type="number" step="0.001" min="0" name="items[{{ $i }}][purchase_quantity]"
                                               class="form-control form-control-sm text-end item-qty"
                                               value="{{ old("items.$i.purchase_quantity") }}" placeholder="0">
                                    </td>
                                    <td>
                                        <input type="number" step="0.001" min="0" name="items[{{ $i }}][bonus_quantity]"
                                               class="form-control form-control-sm text-end item-bonus"
                                               value="{{ old("items.$i.bonus_quantity", '0') }}">
                                    </td>
                                    <td>
                                        <input type="number" step="0.001" min="0" name="items[{{ $i }}][loose_quantity]"
                                               class="form-control form-control-sm text-end item-loose"
                                               value="{{ old("items.$i.loose_quantity", '0') }}">
                                    </td>
                                    <td>
                                        <input type="number" step="0.001" min="0.001" name="items[{{ $i }}][units_per_pack]"
                                               class="form-control form-control-sm text-end item-upp"
                                               value="{{ old("items.$i.units_per_pack", '1') }}">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="items[{{ $i }}][purchase_price]"
                                               class="form-control form-control-sm text-end item-price"
                                               value="{{ old("items.$i.purchase_price") }}" placeholder="0.00">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="items[{{ $i }}][sale_price]"
                                               class="form-control form-control-sm text-end item-sale"
                                               value="{{ old("items.$i.sale_price") }}" placeholder="0.00">
                                    </td>
                                    <td class="text-end fw-semibold item-line-total">0.00</td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-white border rounded p-3 mb-3">
            <div class="row justify-content-end">
                <div class="col-md-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Sub Total</span>
                        <strong id="displaySubTotal">0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Net Amount</span>
                        <strong id="displayNetAmount">0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Items Count</span>
                        <strong id="displayItemsCount">0</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-lg" id="savePurchaseBtn">
                <i class="bi bi-check-circle"></i> Save Purchase
            </button>
            <a href="{{ route('purchase-invoices.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var maxLines = {{ $maxLines }};

    function toNumber(value) {
        var n = parseFloat(value);
        return isNaN(n) ? 0 : n;
    }

    function formatMoney(n) {
        return toNumber(n).toFixed(2);
    }

    function showAlert(type, message) {
        var el = document.getElementById('purchaseAlert');
        if (!el) return;
        el.className = 'alert alert-' + type + ' shadow-sm';
        el.textContent = message;
        el.classList.remove('d-none');
    }

    function getLineState(row) {
        var product = row.querySelector('.item-product');
        var batch = (row.querySelector('.item-batch').value || '').trim();
        var qty = toNumber(row.querySelector('.item-qty').value);
        var bonus = toNumber(row.querySelector('.item-bonus').value);
        var loose = toNumber(row.querySelector('.item-loose').value);
        var price = toNumber(row.querySelector('.item-price').value);
        var option = product.options[product.selectedIndex];
        var name = option && product.value ? (option.getAttribute('data-name') || option.textContent.trim()) : '';
        var hasProduct = !!product.value;
        var isComplete = hasProduct && batch !== '' && (qty > 0 || bonus > 0 || loose > 0) && price >= 0 && row.querySelector('.item-price').value !== '';

        return {
            hasProduct: hasProduct,
            isComplete: isComplete,
            name: name,
            qty: qty,
            price: price,
            lineTotal: isComplete ? (qty * price) : 0
        };
    }

    function recalc() {
        var subTotal = 0;
        var completeCount = 0;

        document.querySelectorAll('.purchase-line').forEach(function (row) {
            var state = getLineState(row);
            row.querySelector('.item-line-total').textContent = formatMoney(state.lineTotal);
            subTotal += state.lineTotal;
            if (state.isComplete) completeCount++;
        });

        var headerDiscount = toNumber(document.getElementById('discount_amount').value);
        var headerTax = toNumber(document.getElementById('tax_amount').value);
        var other = toNumber(document.getElementById('other_charges').value);
        var net = subTotal - headerDiscount + headerTax + other;

        document.getElementById('displaySubTotal').textContent = formatMoney(subTotal);
        document.getElementById('displayNetAmount').textContent = formatMoney(net);
        document.getElementById('displayItemsCount').textContent = String(completeCount);
        document.getElementById('itemsAddedBadge').textContent = completeCount + ' / ' + maxLines + ' items';
        document.getElementById('itemsAddedBadge').className = completeCount > 0 ? 'badge text-bg-success' : 'badge text-bg-primary';
        return completeCount;
    }

    document.querySelectorAll('.item-product').forEach(function (select) {
        select.addEventListener('change', function () {
            var row = select.closest('.purchase-line');
            var option = select.options[select.selectedIndex];
            if (select.value) {
                var sale = option.getAttribute('data-sale');
                var upp = option.getAttribute('data-upp') || '1';
                var saleInput = row.querySelector('.item-sale');
                var uppInput = row.querySelector('.item-upp');
                if (sale && (!saleInput.value || toNumber(saleInput.value) === 0)) {
                    saleInput.value = formatMoney(sale);
                }
                if (!uppInput.value || toNumber(uppInput.value) === 1) {
                    uppInput.value = upp;
                }
                if (!row.querySelector('.item-qty').value) {
                    row.querySelector('.item-qty').value = '1';
                }
            }
            recalc();
        });
    });

    document.querySelectorAll('.item-qty, .item-bonus, .item-loose, .item-price, .item-batch, .item-sale, .item-upp, .header-money').forEach(function (input) {
        input.addEventListener('input', recalc);
    });

    document.getElementById('purchaseForm').addEventListener('submit', function (e) {
        var reasons = [];
        var supplier = document.getElementById('supplier_name');
        var dateInput = document.getElementById('invoice_date');

        if (!supplier.value.trim()) {
            reasons.push('Supplier is required.');
            supplier.classList.add('is-invalid');
        }
        if (!dateInput.value) {
            reasons.push('Invoice date is required.');
            dateInput.classList.add('is-invalid');
        }

        var filled = recalc();
        if (filled < 1) {
            reasons.push('Add at least one product with batch, quantity and price.');
        }

        document.querySelectorAll('.purchase-line').forEach(function (row, index) {
            var product = row.querySelector('.item-product');
            if (!product.value) return;
            var batch = (row.querySelector('.item-batch').value || '').trim();
            var qty = toNumber(row.querySelector('.item-qty').value);
            var bonus = toNumber(row.querySelector('.item-bonus').value);
            var loose = toNumber(row.querySelector('.item-loose').value);
            var priceVal = row.querySelector('.item-price').value;
            if (batch === '') reasons.push('Line ' + (index + 1) + ': enter batch.');
            if (qty <= 0 && bonus <= 0 && loose <= 0) reasons.push('Line ' + (index + 1) + ': enter quantity.');
            if (priceVal === '' || toNumber(priceVal) < 0) reasons.push('Line ' + (index + 1) + ': enter purchase price.');
        });

        if (reasons.length) {
            e.preventDefault();
            showAlert('danger', 'Purchase was not saved: ' + reasons.join(' '));
            return;
        }

        var saveBtn = document.getElementById('savePurchaseBtn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
        showAlert('info', 'Saving purchase with ' + filled + ' item(s)...');
    });

    @if($errors->any() || session('error') || session('success'))
    window.setTimeout(function () {
        var target = document.getElementById('purchaseServerError')
            || document.getElementById('purchaseValidationErrors')
            || document.getElementById('purchaseServerSuccess');
        if (target) target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, 100);
    @endif

    recalc();
})();
</script>
@endpush
