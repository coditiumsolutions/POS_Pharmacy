@extends('layouts.app')

@section('title', 'Add Product')

@section('content')
<div class="container-fluid">
    <h1 class="h3 page-heading invoice-details-heading mb-3">Add Product</h1>

    <div class="bg-white border rounded p-3">
        <form method="POST" action="{{ route('products.store') }}">
            @csrf

            <div class="invoice-meta-grid invoice-meta-form">
                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="barcode">Barcode</label>
                    <div class="invoice-meta-field">
                        <input id="barcode" type="text" name="barcode" class="form-control form-control-sm invoice-meta-input" value="{{ old('barcode') }}">
                        @error('barcode')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <label class="invoice-meta-title" for="product_name">Item Name *</label>
                    <div class="invoice-meta-field">
                        <input id="product_name" type="text" name="product_name" class="form-control form-control-sm invoice-meta-input" value="{{ old('product_name') }}" required>
                        @error('product_name')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="generic_name">Generic Name</label>
                    <div class="invoice-meta-field">
                        <input id="generic_name" type="text" name="generic_name" class="form-control form-control-sm invoice-meta-input" value="{{ old('generic_name') }}">
                        @error('generic_name')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <label class="invoice-meta-title" for="category_uid">Category *</label>
                    <div class="invoice-meta-field">
                        <select id="category_uid" name="category_uid" class="form-select form-select-sm invoice-meta-input" required>
                            <option value="">Select category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->uid }}" @selected(old('category_uid') == $category->uid)>{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                        @error('category_uid')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="company_name">Company</label>
                    <div class="invoice-meta-field">
                        <input id="company_name" type="text" name="company_name" class="form-control form-control-sm invoice-meta-input" value="{{ old('company_name') }}">
                        @error('company_name')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <label class="invoice-meta-title" for="strength">Strength</label>
                    <div class="invoice-meta-field">
                        <input id="strength" type="text" name="strength" class="form-control form-control-sm invoice-meta-input" value="{{ old('strength') }}" placeholder="e.g. 500mg">
                        @error('strength')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="pack_unit">Pack Unit</label>
                    <div class="invoice-meta-field">
                        <input id="pack_unit" type="text" name="pack_unit" class="form-control form-control-sm invoice-meta-input" value="{{ old('pack_unit') }}" placeholder="e.g. Box, Strip">
                        @error('pack_unit')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <label class="invoice-meta-title" for="units_per_pack">Units / Pack</label>
                    <div class="invoice-meta-field">
                        <input id="units_per_pack" type="number" min="1" name="units_per_pack" class="form-control form-control-sm invoice-meta-input" value="{{ old('units_per_pack', 1) }}">
                        @error('units_per_pack')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="sale_price">Sale Price</label>
                    <div class="invoice-meta-field">
                        <input id="sale_price" type="number" step="0.01" min="0" name="sale_price" class="form-control form-control-sm invoice-meta-input" value="{{ old('sale_price', '0') }}">
                        @error('sale_price')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <label class="invoice-meta-title" for="discount_percent">Discount %</label>
                    <div class="invoice-meta-field">
                        <input id="discount_percent" type="number" step="0.01" min="0" max="100" name="discount_percent" class="form-control form-control-sm invoice-meta-input" value="{{ old('discount_percent', '0') }}">
                        @error('discount_percent')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="reorder_level">Reorder Level</label>
                    <div class="invoice-meta-field">
                        <input id="reorder_level" type="number" step="0.01" min="0" name="reorder_level" class="form-control form-control-sm invoice-meta-input" value="{{ old('reorder_level', '0') }}">
                        @error('reorder_level')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <label class="invoice-meta-title">Options</label>
                    <div class="invoice-meta-field d-flex flex-wrap gap-3 align-items-center">
                        <label class="mb-0 small"><input type="checkbox" name="allow_loose_sale" value="1" @checked(old('allow_loose_sale'))> Allow loose sale</label>
                        <label class="mb-0 small"><input type="checkbox" name="is_prescription_required" value="1" @checked(old('is_prescription_required'))> Prescription required</label>
                        <label class="mb-0 small"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> Active</label>
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
                <button type="submit" class="btn btn-primary">Save Product</button>
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
