<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\VendorPayment;
use App\Models\PurchaseInvoice;
use Illuminate\Http\Request;

class VendorsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = $request->get('search');
        $query = Vendor::query()
            ->select('vendors.*')
            ->selectSub(function ($q) {
                $q->from('purchase_invoices')
                    ->selectRaw('COALESCE(SUM(balance_amount), 0)')
                    ->whereColumn('purchase_invoices.supplier_name', 'vendors.vendor_name')
                    ->where(function ($w) {
                        $w->whereNull('invoice_status')
                            ->orWhere('invoice_status', '!=', 'Cancelled');
                    })
                    ->where('balance_amount', '>', 0);
            }, 'outstanding_balance')
            ->selectSub(function ($q) {
                $q->from('purchase_invoices')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('purchase_invoices.supplier_name', 'vendors.vendor_name')
                    ->where(function ($w) {
                        $w->whereNull('invoice_status')
                            ->orWhere('invoice_status', '!=', 'Cancelled');
                    })
                    ->where('balance_amount', '>', 0);
            }, 'pending_invoice_count');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('vendor_name', 'like', "%{$search}%")
                  ->orWhere('vendor_code', 'like', "%{$search}%")
                  ->orWhere('mobile_no', 'like', "%{$search}%");
            });
        }

        $vendors = $query->orderBy('vendor_name')->paginate(50)->withQueryString();
        return view('vendors.index', compact('vendors'));
    }

    public function create()
    {
        $vendorCode = $this->generateVendorCode();
        return view('vendors.create', compact('vendorCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_name' => 'required|string|max:200',
            'contact_person' => 'nullable|string|max:150',
            'mobile_no' => 'nullable|string|max:30',
            'phone_no' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'ntn' => 'nullable|string|max:50',
            'opening_balance' => 'nullable|numeric',
            'credit_limit' => 'nullable|numeric',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['vendor_code'] = $this->generateVendorCode();
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['opening_balance'] = $validated['opening_balance'] ?? 0;
        $validated['credit_limit'] = $validated['credit_limit'] ?? 0;
        $validated['created_date'] = now();
        Vendor::create($validated);

        return redirect()->route('vendors.index')->with('success', 'Vendor created successfully!');
    }

    public function edit($uid)
    {
        $vendor = Vendor::findOrFail($uid);
        return view('vendors.edit', compact('vendor'));
    }

    public function update(Request $request, $uid)
    {
        $vendor = Vendor::findOrFail($uid);

        $validated = $request->validate([
            'vendor_code' => 'required|string|max:50|unique:vendors,vendor_code,' . $uid . ',uid',
            'vendor_name' => 'required|string|max:200',
            'contact_person' => 'nullable|string|max:150',
            'mobile_no' => 'nullable|string|max:30',
            'phone_no' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'ntn' => 'nullable|string|max:50',
            'opening_balance' => 'nullable|numeric',
            'credit_limit' => 'nullable|numeric',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['updated_date'] = now();
        $vendor->update($validated);

        return redirect()->route('vendors.index')->with('success', 'Vendor updated successfully!');
    }

    public function show($uid)
    {
        $vendor = Vendor::findOrFail($uid);

        $invoiceBase = PurchaseInvoice::where('supplier_name', $vendor->vendor_name)
            ->where(function ($w) {
                $w->whereNull('invoice_status')
                    ->orWhere('invoice_status', '!=', 'Cancelled');
            });

        $totalPurchases = (float) (clone $invoiceBase)->sum('net_amount');
        $totalPaid = (float) (clone $invoiceBase)->sum('paid_amount');
        $balance = (float) (clone $invoiceBase)->sum('balance_amount');

        $pendingInvoices = (clone $invoiceBase)
            ->where('balance_amount', '>', 0)
            ->orderBy('invoice_date')
            ->orderBy('uid')
            ->get();

        $invoices = PurchaseInvoice::where('supplier_name', $vendor->vendor_name)
            ->orderByDesc('invoice_date')
            ->orderByDesc('uid')
            ->paginate(25, ['*'], 'invoices_page')
            ->withQueryString();
        $payments = VendorPayment::with(['branch', 'user'])
            ->where('vendor_uid', $vendor->uid)
            ->orderByDesc('payment_date')
            ->orderByDesc('uid')
            ->limit(20)
            ->get();

        return view('vendors.show', compact(
            'vendor',
            'totalPurchases',
            'totalPaid',
            'balance',
            'pendingInvoices',
            'invoices',
            'payments'
        ));
    }

    public function destroy($uid)
    {
        $vendor = Vendor::findOrFail($uid);
        $hasPurchases = $vendor->purchaseInvoices()->exists();

        if ($hasPurchases) {
            $vendor->update(['is_active' => false, 'updated_date' => now()]);
            return redirect()->route('vendors.index')
                ->with('success', 'Vendor has purchases, so it was deactivated instead of deleted.');
        }

        $vendor->delete();
        return redirect()->route('vendors.index')->with('success', 'Vendor deleted successfully!');
    }

    private function generateVendorCode()
    {
        $lastVendor = Vendor::where('vendor_code', 'like', 'VEN-%')
            ->orderBy('vendor_code', 'desc')
            ->first();

        if ($lastVendor) {
            $lastNumber = (int)str_replace('VEN-', '', $lastVendor->vendor_code);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1000;
        }

        return 'VEN-' . $newNumber;
    }
}
