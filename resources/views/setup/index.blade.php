@extends('layouts.app')
@section('title', 'Setup')
@section('content')
<div class="modules-page-head">
    <h1 class="page-heading">Setup</h1>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-lg-4">
        <a class="text-decoration-none" href="{{ route('setup.system') }}">
            <div class="metric-card h-100" style="border-color:#93c5fd; background:#eff6ff;">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-gear-wide-connected" style="color:#2563eb;font-size:1.35rem;"></i>
                    <span class="fw-semibold text-dark">System Setup</span>
                </div>
                <div class="small text-muted">Navbar colors, Top Navbar title, Print Slip heading, and Print Slip image</div>
            </div>
        </a>
    </div>
    @if(config('new_setup.enabled'))
    <div class="col-12 col-md-6 col-lg-4">
        <a class="text-decoration-none" href="{{ route('setup.new') }}">
            <div class="metric-card h-100" style="border-color:#fcd34d; background:#fffbeb;">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-copy" style="color:#b45309;font-size:1.35rem;"></i>
                    <span class="fw-semibold text-dark">New Setup</span>
                </div>
                <div class="small text-muted">Create a separate blank copy (new DB + subdomain) for another customer</div>
            </div>
        </a>
    </div>
    @endif
</div>

<div class="row g-3">
    @foreach($modules as $module)
        <div class="col-6 col-md-4 col-lg-3">
            <a class="text-decoration-none" href="{{ route($module['route']) }}">
                <div class="metric-card h-100">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi {{ $module['icon'] }}" style="color:#ea580c;font-size:1.25rem;"></i>
                        <span class="fw-semibold text-dark">{{ $module['name'] }}</span>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>
@endsection
