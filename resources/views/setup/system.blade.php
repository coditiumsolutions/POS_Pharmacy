@extends('layouts.app')

@section('title', 'System Setup')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h1 class="h3 page-heading mb-1">System Setup</h1>
            <p class="text-muted mb-0">Configure navbar colors, branding, and print slip.</p>
        </div>
        <a href="{{ route('setup.index') }}" class="btn btn-outline-secondary btn-sm">Back to Setup</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white border rounded p-3">
        <form method="POST" action="{{ route('setup.system.update') }}" enctype="multipart/form-data">
            @csrf

            <div class="invoice-meta-grid invoice-meta-form">
                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="top_navbar_title">Top Navbar *</label>
                    <div class="invoice-meta-field">
                        <input id="top_navbar_title" type="text" name="top_navbar_title"
                               class="form-control form-control-sm invoice-meta-input"
                               value="{{ old('top_navbar_title', $settings['top_navbar_title']) }}"
                               placeholder="LipSip POS" required maxlength="100">
                        <div class="invoice-meta-sub text-muted">Shown in the first (dark) top navbar.</div>
                    </div>
                    <label class="invoice-meta-title"></label>
                    <div class="invoice-meta-field"></div>
                </div>

                <div class="invoice-meta-row">
                    <label class="invoice-meta-title">Color Combination *</label>
                    <div class="invoice-meta-field" style="grid-column: 2 / -1;">
                        <div class="invoice-meta-sub text-muted mb-2">
                            Top Navbar and Side Navbar use the first color; Second Top Navbar uses the second color.
                        </div>
                        @php
                            $selectedCombo = old('color_combination', $settings['color_combination']);
                        @endphp
                        <div class="row g-2">
                            @foreach($colorCombinations as $combo)
                                <div class="col-12 col-md-6 col-xl-4">
                                    <label class="color-combo-card {{ $selectedCombo === $combo['id'] ? 'is-selected' : '' }}">
                                        <input type="radio"
                                               name="color_combination"
                                               value="{{ $combo['id'] }}"
                                               class="form-check-input color-combo-input"
                                               {{ $selectedCombo === $combo['id'] ? 'checked' : '' }}
                                               required>
                                        <div class="color-combo-swatches" aria-hidden="true">
                                            <span style="background: {{ $combo['primary'] }};"></span>
                                            <span style="background: {{ $combo['secondary'] }};"></span>
                                        </div>
                                        <div class="color-combo-meta">
                                            <div class="fw-semibold">{{ $combo['label'] }}</div>
                                            <div class="small text-muted font-monospace">
                                                {{ strtoupper($combo['primary']) }} / {{ strtoupper($combo['secondary']) }}
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('color_combination')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="print_slip_heading">Print Slip Heading *</label>
                    <div class="invoice-meta-field">
                        <input id="print_slip_heading" type="text" name="print_slip_heading"
                               class="form-control form-control-sm invoice-meta-input"
                               value="{{ old('print_slip_heading', $settings['print_slip_heading']) }}"
                               placeholder="LipSip Mart" required maxlength="150">
                        <div class="invoice-meta-sub text-muted">Main title on POS print slip.</div>
                    </div>
                    <label class="invoice-meta-title" for="print_slip_phone">Print Slip Phone</label>
                    <div class="invoice-meta-field">
                        <input id="print_slip_phone" type="text" name="print_slip_phone"
                               class="form-control form-control-sm invoice-meta-input"
                               value="{{ old('print_slip_phone', $settings['print_slip_phone']) }}"
                               placeholder="0339-5470747" maxlength="50">
                        <div class="invoice-meta-sub text-muted">Phone line under the print slip heading.</div>
                    </div>
                </div>

                <div class="invoice-meta-row">
                    <label class="invoice-meta-title" for="print_slip_image">Print Slip Image</label>
                    <div class="invoice-meta-field" style="grid-column: 2 / -1;">
                        <input id="print_slip_image" type="file" name="print_slip_image"
                               class="form-control form-control-sm invoice-meta-input"
                               accept="image/png,image/jpeg,image/gif,image/webp">
                        <div class="invoice-meta-sub text-muted">Optional logo/image printed above the slip heading (max 2MB).</div>
                        @if(!empty($settings['print_slip_image_url']))
                            <div class="mt-2 d-flex align-items-center gap-3 flex-wrap">
                                <img src="{{ $settings['print_slip_image_url'] }}" alt="Print slip image"
                                     style="max-height: 72px; max-width: 180px; object-fit: contain; border: 1px solid #e2e8f0; padding: 4px; background: #fff;">
                                <label class="small mb-0">
                                    <input type="checkbox" name="remove_print_slip_image" value="1">
                                    Remove current image
                                </label>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary btn-sm">Save System Setup</button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .color-combo-card {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        width: 100%;
        margin: 0;
        padding: 0.65rem 0.75rem;
        border: 1px solid #dbe3ef;
        border-radius: 0.5rem;
        background: #f8fafc;
        cursor: pointer;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
    }
    .color-combo-card:hover {
        border-color: #94a3b8;
        background: #fff;
    }
    .color-combo-card.is-selected,
    .color-combo-card:has(.color-combo-input:checked) {
        border-color: #0f766e;
        box-shadow: 0 0 0 2px rgba(15, 118, 110, 0.18);
        background: #fff;
    }
    .color-combo-input {
        margin: 0;
        flex: 0 0 auto;
    }
    .color-combo-swatches {
        display: flex;
        width: 64px;
        height: 36px;
        border-radius: 0.35rem;
        overflow: hidden;
        border: 1px solid rgba(15, 23, 42, 0.2);
        flex: 0 0 auto;
    }
    .color-combo-swatches span {
        flex: 1 1 50%;
    }
    .color-combo-meta {
        min-width: 0;
        line-height: 1.25;
    }
</style>
@endpush

@push('scripts')
<script>
    document.querySelectorAll('.color-combo-input').forEach(function (input) {
        input.addEventListener('change', function () {
            document.querySelectorAll('.color-combo-card').forEach(function (card) {
                card.classList.toggle('is-selected', card.querySelector('.color-combo-input')?.checked);
            });
        });
    });
</script>
@endpush
@endsection
