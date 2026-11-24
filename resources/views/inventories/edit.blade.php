@extends('layouts.app')

@section('title', 'Edit Inventory')

@section('content')
<div class="container-fluid">
    <h1 class="h3 page-heading invoice-details-heading mb-3">Edit Inventory</h1>

    <div class="bg-white border rounded p-3">
        <form method="POST" action="{{ route('inventories.update', $inventory->uid) }}">
            @csrf
            @method('PUT')

            <div class="invoice-meta-grid invoice-meta-form">
                <div class="invoice-meta-row">
                    <label class="invoice-meta-title">Item Name</label>
                    <div class="invoice-meta-field" style="grid-column: 2 / -1;">
                        <input type="text" class="form-control form-control-sm invoice-meta-input"
                               value="{{ $inventory->product->product_name ?? ('#'.$inventory->product_uid) }}" readonly>
                        @if($inventory->product?->barcode)
                            <div class="invoice-meta-sub text-muted">Barcode: {{ $inventory->product->barcode }}</div>
                        @endif
                    </div>
                </div>

                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="batch_no">Batch *</label>
                    <div class="invoice-meta-field">
                        <input id="batch_no" type="text" name="batch_no" class="form-control form-control-sm invoice-meta-input" value="{{ old('batch_no', $inventory->batch_no) }}" required>
                        @error('batch_no')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <label class="invoice-meta-title" for="expiry_date">Expiry</label>
                    <div class="invoice-meta-field">
                        <input id="expiry_date" type="date" name="expiry_date" class="form-control form-control-sm invoice-meta-input" value="{{ old('expiry_date', optional($inventory->expiry_date)->format('Y-m-d')) }}">
                        @error('expiry_date')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="quantity_in_stock">Quantity *</label>
                    <div class="invoice-meta-field">
                        <input id="quantity_in_stock" type="number" step="0.001" name="quantity_in_stock" class="form-control form-control-sm invoice-meta-input" value="{{ old('quantity_in_stock', $inventory->quantity_in_stock) }}" required>
                        @error('quantity_in_stock')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <label class="invoice-meta-title" for="sale_price">Sale Price</label>
                    <div class="invoice-meta-field">
                        <input id="sale_price" type="number" step="0.01" min="0" name="sale_price" class="form-control form-control-sm invoice-meta-input" value="{{ old('sale_price', $inventory->sale_price) }}">
                        @error('sale_price')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="remarks">Remarks</label>
                    <div class="invoice-meta-field" style="grid-column: 2 / -1;">
                        <textarea id="remarks" name="remarks" rows="2" class="form-control form-control-sm invoice-meta-input">{{ old('remarks', $inventory->remarks) }}</textarea>
                        @error('remarks')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('inventories.index') }}" class="btn btn-outline-secondary">Back to list</a>
            </div>
        </form>
    </div>
</div>
@endsection
