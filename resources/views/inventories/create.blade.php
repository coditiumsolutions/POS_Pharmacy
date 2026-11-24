@extends('layouts.app')

@section('title', 'Add Inventory')

@section('content')
<div class="container-fluid">
    <h1 class="h3 page-heading invoice-details-heading mb-3">Add Inventory</h1>
    <p class="text-muted mb-3">One inventory row per product batch.</p>

    <div class="bg-white border rounded p-3">
        <form method="POST" action="{{ route('inventories.store') }}">
            @csrf

            <div class="invoice-meta-grid invoice-meta-form">
                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="product_uid">Item Name *</label>
                    <div class="invoice-meta-field" style="grid-column: 2 / -1;">
                        <select id="product_uid" name="product_uid" class="form-select form-select-sm invoice-meta-input" required>
                            <option value="">Select product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->uid }}" @selected(old('product_uid') == $product->uid)>
                                    {{ $product->product_name }}
                                    @if($product->strength) — {{ $product->strength }}@endif
                                    @if($product->barcode) ({{ $product->barcode }})@endif
                                </option>
                            @endforeach
                        </select>
                        @error('product_uid')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="batch_no">Batch *</label>
                    <div class="invoice-meta-field">
                        <input id="batch_no" type="text" name="batch_no" class="form-control form-control-sm invoice-meta-input" value="{{ old('batch_no') }}" required>
                        @error('batch_no')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <label class="invoice-meta-title" for="expiry_date">Expiry</label>
                    <div class="invoice-meta-field">
                        <input id="expiry_date" type="date" name="expiry_date" class="form-control form-control-sm invoice-meta-input" value="{{ old('expiry_date') }}">
                        @error('expiry_date')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="quantity_in_stock">Quantity</label>
                    <div class="invoice-meta-field">
                        <input id="quantity_in_stock" type="number" step="0.001" name="quantity_in_stock" class="form-control form-control-sm invoice-meta-input" value="{{ old('quantity_in_stock', '0') }}">
                        @error('quantity_in_stock')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <label class="invoice-meta-title" for="sale_price">Sale Price</label>
                    <div class="invoice-meta-field">
                        <input id="sale_price" type="number" step="0.01" min="0" name="sale_price" class="form-control form-control-sm invoice-meta-input" value="{{ old('sale_price') }}">
                        @error('sale_price')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="remarks">Remarks</label>
                    <div class="invoice-meta-field" style="grid-column: 2 / -1;">
                        <textarea id="remarks" name="remarks" rows="2" class="form-control form-control-sm invoice-meta-input">{{ old('remarks') }}</textarea>
                        @error('remarks')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('inventories.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
