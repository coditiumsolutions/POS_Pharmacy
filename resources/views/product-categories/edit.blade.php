@extends('layouts.app')

@section('title', 'Edit Category')

@section('content')
<div class="container-fluid">
    <h1 class="h3 page-heading invoice-details-heading mb-3">Edit Category</h1>

    <div class="bg-white border rounded p-3">
        <form method="POST" action="{{ route('product-categories.update', $category->uid) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="invoice-meta-grid invoice-meta-form">
                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="category_name">Name *</label>
                    <div class="invoice-meta-field">
                        <input id="category_name" type="text" name="category_name"
                               class="form-control form-control-sm invoice-meta-input"
                               value="{{ old('category_name', $category->category_name) }}" required maxlength="100">
                        @error('category_name')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <label class="invoice-meta-title" for="is_active">Active</label>
                    <div class="invoice-meta-field">
                        <input type="hidden" name="is_active" value="0">
                        <input id="is_active" type="checkbox" name="is_active" value="1" class="form-check-input"
                               @checked(old('is_active', $category->is_active ? '1' : '0') == '1')>
                    </div>
                </div>

                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="description">Description</label>
                    <div class="invoice-meta-field" style="grid-column: 2 / -1;">
                        <input id="description" type="text" name="description"
                               class="form-control form-control-sm invoice-meta-input"
                               value="{{ old('description', $category->description) }}" maxlength="255">
                        @error('description')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="image">Category Image</label>
                    <div class="invoice-meta-field" style="grid-column: 2 / -1;">
                        @if($category->image_path)
                            <div class="d-flex align-items-center gap-3 mb-2 flex-wrap">
                                <img src="{{ asset($category->image_path) }}" alt="{{ $category->category_name }}"
                                     style="width:72px;height:72px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0;">
                                <div class="small text-muted">
                                    <div>{{ $category->image_name ?: basename($category->image_path) }}</div>
                                    <div class="font-monospace">{{ $category->image_path }}</div>
                                </div>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image">
                                <label class="form-check-label" for="remove_image">Remove current image</label>
                            </div>
                        @endif
                        <input id="image" type="file" name="image" class="form-control form-control-sm"
                               accept="image/jpeg,image/png,image/gif,image/webp">
                        <div class="form-text">JPG, PNG, GIF or WebP. Max 2 MB.</div>
                        @error('image')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('product-categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
