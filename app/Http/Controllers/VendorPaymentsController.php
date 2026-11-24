<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Models\PurchaseInvoice;
use App\Models\Vendor;
use App\Models\VendorPayment;
use App\Support\CurrentBranch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VendorPaymentsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = VendorPayment::with(['vendor', 'user', 'branch']);

        if ($request->filled('vendor_uid')) {
            $query->where('vendor_uid', $request->vendor_uid);
        }

        $dateFromDisplay = trim((string) $request->get('date_from', ''));
        $dateToDisplay = trim((string) $request->get('date_to', ''));
        $dateFrom = $this->parseDate($dateFromDisplay);
        $dateTo = $this->parseDate($dateToDisplay);

        if ($dateFrom) {
            $query->whereDate('payment_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('payment_date', '<=', $dateTo);
        }

        $totalAmount = (clone $query)->sum('amount');
        $payments = $query->orderBy('payment_date', 'desc')->paginate(50)->withQueryString();
        $vendors = Vendor::where('is_active', true)->orderBy('vendor_name')->get();

        return view('vendor-payments.index', compact('payments', 'vendors', 'totalAmount', 'dateFromDisplay', 'dateToDisplay'));
    }

    public function create(Request $request)
    {
        $vendorUid = $request->get('vendor_uid');
        $invoiceUid = $request->get('invoice_uid');
        $returnTo = $request->get('return_to', 'vendor');
        $vendor = null;
        $invoices = collect();
        $pendingInvoices = collect();
        $focusInvoice = null;
        $suggestedAmount = null;

        if ($invoiceUid && !$vendorUid) {
            $focusInvoice = PurchaseInvoice::find($invoiceUid);
            if ($focusInvoice && $focusInvoice->supplier_name) {
                $matched = Vendor::where('vendor_name', $focusInvoice->supplier_name)->first();
                $vendorUid = $matched?->uid;
            }
        }

        if ($vendorUid) {
            $vendor = Vendor::findOrFail($vendorUid);

            $invoices = PurchaseInvoice::where('supplier_name', $vendor->vendor_name)
                ->where(function ($w) {
                    $w->whereNull('invoice_status')
                        ->orWhere('invoice_status', '!=', 'Cancelled');
                })
                ->orderByDesc('invoice_date')
                ->orderByDesc('uid')
                ->get();

            $pendingInvoices = $invoices->filter(fn ($inv) => (float) $inv->balance_amount > 0)->values();

            if ($invoiceUid) {
                $focusInvoice = $pendingInvoices->firstWhere('uid', (int) $invoiceUid)
                    ?: $invoices->firstWhere('uid', (int) $invoiceUid);
            }

            if (!$focusInvoice && $pendingInvoices->isNotEmpty()) {
                $focusInvoice = $pendingInvoices->first();
            }

            if ($focusInvoice && (float) $focusInvoice->balance_amount > 0) {
                $suggestedAmount = round((float) $focusInvoice->balance_amount, 2);
            } elseif ($request->boolean('pay_all') && $pendingInvoices->isNotEmpty()) {
                $suggestedAmount = round((float) $pendingInvoices->sum('balance_amount'), 2);
            }
        }

        $vendors = Vendor::where('is_active', true)->orderBy('vendor_name')->get();
        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('payment_method_name')->get();
        $paymentNo = $this->generatePaymentNo();
        $defaultBranchUid = CurrentBranch::uid();

        return view('vendor-payments.create', compact(
            'vendors',
            'vendor',
            'invoices',
            'pendingInvoices',
            'paymentMethods',
            'paymentNo',
            'defaultBranchUid',
            'focusInvoice',
            'suggestedAmount',
            'returnTo'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_uid' => 'required|exists:vendors,uid',
            'payment_date' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'payment_mode' => 'required|string|max:50',
            'payment_method_uid' => 'nullable|exists:payment_methods,uid',
            'reference_no' => 'nullable|string|max:100',
            'remarks' => 'nullable|string|max:500',
            'branch_uid' => 'nullable|exists:branches,uid',
            'selected_invoice_uid' => 'nullable|exists:purchase_invoices,uid',
            'apply_to_invoices' => 'nullable|array',
            'apply_to_invoices.*' => 'nullable|numeric|min:0',
            'auto_apply' => 'nullable|boolean',
            'return_to' => 'nullable|string|max:255',
        ], [
            'vendor_uid.required' => 'Please select a vendor.',
            'amount.min' => 'Payment amount must be greater than 0.',
            'payment_mode.required' => 'Please select a payment mode.',
        ]);

        $paymentDate = $this->parseDate($validated['payment_date']);
        if (!$paymentDate) {
            return back()->withInput()->withErrors(['payment_date' => 'Payment date is invalid. Use format like 10-Nov-2026.']);
        }

        $amount = round((float) $validated['amount'], 2);
        $branchUid = $validated['branch_uid'] ?: CurrentBranch::uid();
        $selectedInvoiceUid = $validated['selected_invoice_uid'] ?? null;

        $vendor = Vendor::findOrFail($validated['vendor_uid']);
        $supplierName = $vendor->vendor_name;

        if ($selectedInvoiceUid) {
            $selectedInvoice = PurchaseInvoice::where('uid', $selectedInvoiceUid)
                ->where('supplier_name', $supplierName)
                ->first();
            if (!$selectedInvoice || (float) $selectedInvoice->balance_amount <= 0) {
                return back()->withInput()->withErrors([
                    'selected_invoice_uid' => 'Select an unpaid purchase invoice to apply this payment.',
                ]);
            }
            if ($amount > (float) $selectedInvoice->balance_amount + 0.0001) {
                return back()->withInput()->withErrors([
                    'amount' => 'Amount cannot exceed due now (Rs. ' . number_format((float) $selectedInvoice->balance_amount, 2) . ').',
                ]);
            }
        }

        DB::beginTransaction();
        try {
            $payload = [
                'payment_no' => $this->generatePaymentNo(),
                'payment_date' => $paymentDate,
                'vendor_uid' => $validated['vendor_uid'],
                'branch_uid' => $branchUid,
                'user_uid' => Auth::id(),
                'amount' => $amount,
                'payment_mode' => $validated['payment_mode'],
                'reference_no' => $validated['reference_no'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'created_date' => now(),
            ];

            if (Schema::hasColumn('vendor_payments', 'payment_method_uid')) {
                $payload['payment_method_uid'] = $validated['payment_method_uid'] ?? null;
            }

            $payment = VendorPayment::create($payload);

            $remaining = $amount;
            $appliedTotal = 0.0;

            if ($selectedInvoiceUid) {
                $invoice = PurchaseInvoice::where('uid', $selectedInvoiceUid)
                    ->where('supplier_name', $supplierName)
                    ->lockForUpdate()
                    ->first();
                if ($invoice && (float) $invoice->balance_amount > 0) {
                    $amountToApply = min($remaining, (float) $invoice->balance_amount);
                    $this->applyToInvoice($invoice, $amountToApply);
                    $remaining = round($remaining - $amountToApply, 2);
                    $appliedTotal = round($appliedTotal + $amountToApply, 2);
                }
            } else {
                $applyMap = collect($validated['apply_to_invoices'] ?? [])
                    ->map(fn ($v) => (float) $v)
                    ->filter(fn ($v) => $v > 0);

                if ($applyMap->isNotEmpty()) {
                    foreach ($applyMap as $invoiceUid => $applyAmount) {
                        if ($remaining <= 0) {
                            break;
                        }
                        $invoice = PurchaseInvoice::where('uid', $invoiceUid)
                            ->where('supplier_name', $supplierName)
                            ->lockForUpdate()
                            ->first();
                        if (!$invoice || (float) $invoice->balance_amount <= 0) {
                            continue;
                        }
                        $amountToApply = min($applyAmount, $remaining, (float) $invoice->balance_amount);
                        $this->applyToInvoice($invoice, $amountToApply);
                        $remaining = round($remaining - $amountToApply, 2);
                        $appliedTotal = round($appliedTotal + $amountToApply, 2);
                    }
                } elseif ($request->boolean('auto_apply', true)) {
                    $pending = PurchaseInvoice::where('supplier_name', $supplierName)
                        ->where('balance_amount', '>', 0)
                        ->where(function ($w) {
                            $w->whereNull('invoice_status')
                                ->orWhere('invoice_status', '!=', 'Cancelled');
                        })
                        ->orderBy('invoice_date')
                        ->orderBy('uid')
                        ->lockForUpdate()
                        ->get();

                    foreach ($pending as $invoice) {
                        if ($remaining <= 0) {
                            break;
                        }
                        $amountToApply = min($remaining, (float) $invoice->balance_amount);
                        $this->applyToInvoice($invoice, $amountToApply);
                        $remaining = round($remaining - $amountToApply, 2);
                        $appliedTotal = round($appliedTotal + $amountToApply, 2);
                    }
                }
            }

            if (Schema::hasColumn('vendor_payments', 'applied_to_invoice_amount')) {
                $payment->applied_to_invoice_amount = $appliedTotal;
                $payment->save();
            }

            DB::commit();

            $message = 'Vendor payment ' . $payment->payment_no . ' saved successfully.';
            if ($appliedTotal > 0) {
                $message .= ' Applied Rs. ' . number_format($appliedTotal, 2) . ' to purchase invoice(s).';
            }

            $returnTo = trim((string) ($validated['return_to'] ?? ''));
            if ($returnTo === 'vendor') {
                return redirect()->route('vendors.show', $validated['vendor_uid'])->with('success', $message);
            }

            return redirect()->route('vendor-payments.index')->with('success', $message);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Payment was not saved. ' . $e->getMessage());
        }
    }

    private function applyToInvoice(PurchaseInvoice $invoice, float $amountToApply): void
    {
        $invoice->paid_amount = round((float) $invoice->paid_amount + $amountToApply, 2);
        $invoice->balance_amount = round((float) $invoice->balance_amount - $amountToApply, 2);
        if ($invoice->balance_amount <= 0) {
            $invoice->balance_amount = 0;
            $invoice->payment_status = 'Paid';
        } elseif ((float) $invoice->paid_amount > 0) {
            $invoice->payment_status = 'Partial';
        }
        $invoice->save();
    }

    private function parseDate(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        foreach (['d-M-Y', 'Y-m-d', 'd/m/Y', 'd-m-Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d');
            } catch (\Throwable $e) {
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function generatePaymentNo()
    {
        $date = now()->format('Ymd');
        $lastPayment = VendorPayment::where('payment_no', 'like', "VP{$date}%")
            ->orderBy('payment_no', 'desc')
            ->first();

        if ($lastPayment) {
            $lastNumber = (int) substr($lastPayment->payment_no, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'VP' . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
