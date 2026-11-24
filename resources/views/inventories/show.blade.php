@extends('layouts.app')

@section('title', 'Inventory Details')

@section('content')
<div class="container-fluid">
    <h1 class="h3 page-heading invoice-details-heading mb-3">Inventory Details</h1>

    <div class="bg-white border rounded p-3">
        <div class="invoice-meta-grid invoice-meta-form invoice-meta-readonly">
            <div class="invoice-meta-row">
                <div class="invoice-meta-title">Item Name</div>
                <div class="invoice-meta-field">
                    {{ $inventory->product->product_name ?? ('#'.$inventory->product_uid) }}
                    @if($inventory->product?->barcode)
                        <span class="invoice-meta-sub">Barcode: {{ $inventory->product->barcode }}</span>
                    @endif
                </div>
                <div class="invoice-meta-title">Category</div>
                <div class="invoice-meta-field">{{ $inventory->product->category->category_name ?? '—' }}</div>
            </div>

            <div class="invoice-meta-row">
                <div class="invoice-meta-title">Batch</div>
                <div class="invoice-meta-field">{{ $inventory->batch_no ?: '—' }}</div>
                <div class="invoice-meta-title">Expiry</div>
                <div class="invoice-meta-field">{{ optional($inventory->expiry_date)->format('d-M-Y') ?? '—' }}</div>
            </div>

            <div class="invoice-meta-row">
                <div class="invoice-meta-title">Quantity</div>
                <div class="invoice-meta-field">{{ number_format((float) $inventory->quantity_in_stock, 3) }}</div>
                <div class="invoice-meta-title">Sale Price</div>
                <div class="invoice-meta-field">{{ $inventory->sale_price !== null ? number_format((float) $inventory->sale_price, 2) : '—' }}</div>
            </div>

            <div class="invoice-meta-row">
                <div class="invoice-meta-title">Remarks</div>
                <div class="invoice-meta-field" style="grid-column: 2 / -1;">{{ $inventory->remarks ?: '—' }}</div>
            </div>
        </div>

        <div class="mt-3 d-flex flex-wrap gap-2 align-items-center">
            <a href="{{ route('inventories.edit', $inventory->uid) }}" class="btn btn-primary">Edit</a>
            <form method="POST" action="{{ route('inventories.destroy', $inventory->uid) }}" class="d-inline"
                  onsubmit="return confirm('Delete this inventory record?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
            <a href="{{ route('inventories.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
