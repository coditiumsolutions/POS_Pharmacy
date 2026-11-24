@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h1 class="h3 page-heading mb-1">All Products</h1>
            <p class="text-muted mb-0">Pharmacy product catalog</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('product-categories.create') }}" class="btn btn-outline-primary">
                <i class="bi bi-tags"></i> Add Category
            </a>
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Product
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Delete blocked:</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-center mb-3">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control"
                           placeholder="Search name, generic, barcode, company..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="category_uid" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->uid }}" {{ request('category_uid') == $category->uid ? 'selected' : '' }}>
                            {{ $category->category_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-secondary">Search</button>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Clear</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Barcode</th>
                            <th>Item Name</th>
                            <th>Generic</th>
                            <th>Category</th>
                            <th>Company</th>
                            <th>Strength</th>
                            <th>Pack</th>
                            <th class="text-end">Sale Price</th>
                            <th>Status</th>
                            <th class="text-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td>{{ $product->barcode ?: '—' }}</td>
                            <td>{{ $product->product_name }}</td>
                            <td>{{ $product->generic_name ?: '—' }}</td>
                            <td>{{ $product->category->category_name ?? '—' }}</td>
                            <td>{{ $product->company_name ?: '—' }}</td>
                            <td>{{ $product->strength ?: '—' }}</td>
                            <td>{{ $product->pack_unit ?: '—' }}@if($product->units_per_pack > 1) ×{{ $product->units_per_pack }}@endif</td>
                            <td class="text-end">Rs. {{ number_format((float) $product->sale_price, 2) }}</td>
                            <td>
                                @if($product->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ route('products.edit', $product->uid) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('products.destroy', $product->uid) }}" class="d-inline"
                                      onsubmit="return confirm('Permanently delete this product?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">No products found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 products-pagination">
                {{ $products->withQueryString()->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
