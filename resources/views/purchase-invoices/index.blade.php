@extends('layouts.app')
@section('title', 'Purchase Invoices')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Purchase Invoices</h1>
        <a href="{{ route('purchase-invoices.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> New Purchase
        </a>
    </div>
    <div class="card">
        <div class="card-body">
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" title="From date">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" title="To date">
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Invoice / supplier..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="payment_status" class="form-select">
                        <option value="">All payments</option>
                        @foreach(['Unpaid','Partial','Paid'] as $ps)
                            <option value="{{ $ps }}" @selected(request('payment_status') === $ps)>{{ $ps }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">Filter</button>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Invoice No</th>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>Amount</th>
                            <th>Paid</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->invoice_no }}</td>
                            <td>{{ optional($invoice->invoice_date)->format('Y-m-d') }}</td>
                            <td>{{ $invoice->supplier_name }}</td>
                            <td>Rs. {{ number_format((float) $invoice->net_amount, 2) }}</td>
                            <td>Rs. {{ number_format((float) $invoice->paid_amount, 2) }}</td>
                            <td><span class="badge bg-{{ $invoice->payment_status == 'Paid' ? 'success' : ($invoice->payment_status == 'Partial' ? 'info' : 'warning') }}">{{ $invoice->payment_status }}</span></td>
                            <td>{{ $invoice->invoice_status }}</td>
                            <td>
                                <a href="{{ route('purchase-invoices.show', $invoice->uid) }}" class="btn btn-sm btn-info">View</a>
                                <a href="{{ route('purchase-invoices.edit', $invoice->uid) }}" class="btn btn-sm btn-primary">Edit</a>
                                <a href="{{ route('purchase-invoices.print', $invoice->uid) }}" class="btn btn-sm btn-secondary" target="_blank">Print</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center">No invoices found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $invoices->links() }}
        </div>
    </div>
</div>
@endsection
