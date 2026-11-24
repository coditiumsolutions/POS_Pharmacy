<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PurchaseInvoicesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = PurchaseInvoice::query();

        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                    ->orWhere('supplier_invoice_no', 'like', "%{$search}%")
                    ->orWhere('supplier_name', 'like', "%{$search}%");
            });
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $invoices = $query->orderByDesc('invoice_date')->orderByDesc('uid')->paginate(50);

        return view('purchase-invoices.index', compact('invoices'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->orderBy('product_name')->get();
        $invoiceNo = $this->generateInvoiceNo();

        return view('purchase-invoices.create', compact('products', 'invoiceNo'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_name' => 'required|string|max:150',
            'supplier_invoice_no' => 'nullable|string|max:100',
            'invoice_date' => 'required|date',
            'received_date' => 'nullable|date',
            'payment_method' => 'nullable|string|max:50',
            'paid_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
            'invoice_status' => 'nullable|in:Draft,Received,Cancelled',
            'items' => 'required|array|min:1|max:30',
            'items.*.product_uid' => 'nullable|exists:products,uid',
            'items.*.batch_no' => 'nullable|string|max:100',
            'items.*.expiry_date' => 'nullable|date',
            'items.*.purchase_quantity' => 'nullable|numeric|min:0',
            'items.*.bonus_quantity' => 'nullable|numeric|min:0',
            'items.*.loose_quantity' => 'nullable|numeric|min:0',
            'items.*.units_per_pack' => 'nullable|numeric|min:0.001',
            'items.*.purchase_price' => 'nullable|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.tax_amount' => 'nullable|numeric|min:0',
            'items.*.sale_price' => 'nullable|numeric|min:0',
            'items.*.remarks' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()
                ->with('error', 'Purchase was not saved. Please fix the highlighted issues.');
        }

        $validated = $validator->validated();
        $lines = [];
        $lineErrors = [];

        foreach (($validated['items'] ?? []) as $index => $item) {
            $lineNo = $index + 1;
            $productUid = $item['product_uid'] ?? null;
            $batchNo = trim((string) ($item['batch_no'] ?? ''));
            $qty = (float) ($item['purchase_quantity'] ?? 0);
            $bonus = (float) ($item['bonus_quantity'] ?? 0);
            $loose = (float) ($item['loose_quantity'] ?? 0);
            $unitsPerPack = (float) ($item['units_per_pack'] ?? 1);
            $price = isset($item['purchase_price']) ? (float) $item['purchase_price'] : null;

            if (empty($productUid) && $batchNo === '' && $qty <= 0 && $price === null) {
                continue;
            }
            if (empty($productUid)) {
                $lineErrors["items.$index.product_uid"] = "Line {$lineNo}: select a product.";
                continue;
            }
            if ($batchNo === '') {
                $lineErrors["items.$index.batch_no"] = "Line {$lineNo}: enter batch number.";
                continue;
            }
            if ($qty <= 0 && $bonus <= 0 && $loose <= 0) {
                $lineErrors["items.$index.purchase_quantity"] = "Line {$lineNo}: enter quantity.";
                continue;
            }
            if ($price === null || $price < 0) {
                $lineErrors["items.$index.purchase_price"] = "Line {$lineNo}: enter purchase price.";
                continue;
            }
            if ($unitsPerPack <= 0) {
                $unitsPerPack = 1;
            }

            $receivedBase = round(($qty * $unitsPerPack) + $bonus + $loose, 3);
            $discount = (float) ($item['discount_amount'] ?? 0);
            $tax = (float) ($item['tax_amount'] ?? 0);
            $lineTotal = round(($qty * $price) - $discount + $tax, 2);

            $lines[] = [
                'product_uid' => (int) $productUid,
                'batch_no' => $batchNo,
                'expiry_date' => $item['expiry_date'] ?? null,
                'purchase_quantity' => $qty,
                'bonus_quantity' => $bonus,
                'loose_quantity' => $loose,
                'units_per_pack' => $unitsPerPack,
                'received_base_quantity' => $receivedBase,
                'purchase_price' => $price,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'line_total' => $lineTotal,
                'sale_price' => isset($item['sale_price']) ? (float) $item['sale_price'] : null,
                'remarks' => $item['remarks'] ?? null,
            ];
        }

        if ($lineErrors) {
            return back()->withErrors($lineErrors)->withInput()
                ->with('error', 'Purchase was not saved. Check item lines.');
        }
        if (count($lines) < 1) {
            return back()->withErrors(['items' => 'Add at least one valid item.'])->withInput()
                ->with('error', 'Purchase was not saved. No valid items.');
        }

        DB::beginTransaction();
        try {
            $subtotal = round(collect($lines)->sum(fn ($l) => $l['purchase_quantity'] * $l['purchase_price']), 2);
            $headerDiscount = (float) ($validated['discount_amount'] ?? 0);
            $headerTax = (float) ($validated['tax_amount'] ?? collect($lines)->sum('tax_amount'));
            $other = (float) ($validated['other_charges'] ?? 0);
            $lineDiscount = round(collect($lines)->sum('discount_amount'), 2);
            $net = round($subtotal - $headerDiscount - $lineDiscount + $headerTax + $other, 2);
            $paid = (float) ($validated['paid_amount'] ?? 0);
            $balance = round($net - $paid, 2);
            $status = $validated['invoice_status'] ?? 'Received';
            $invoiceNo = $this->generateInvoiceNo();

            $invoice = PurchaseInvoice::create([
                'invoice_no' => $invoiceNo,
                'supplier_invoice_no' => $validated['supplier_invoice_no'] ?? null,
                'supplier_name' => $validated['supplier_name'],
                'invoice_date' => $validated['invoice_date'],
                'received_date' => $validated['received_date'] ?? ($status === 'Received' ? $validated['invoice_date'] : null),
                'subtotal' => $subtotal,
                'discount_amount' => $headerDiscount + $lineDiscount,
                'tax_amount' => $headerTax,
                'other_charges' => $other,
                'net_amount' => $net,
                'paid_amount' => $paid,
                'balance_amount' => $balance,
                'payment_status' => $balance <= 0 ? 'Paid' : ($paid > 0 ? 'Partial' : 'Unpaid'),
                'invoice_status' => $status,
                'payment_method' => $validated['payment_method'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
            ]);

            foreach ($lines as $line) {
                $inventory = Inventory::findOrCreateBatch(
                    $line['product_uid'],
                    $line['batch_no'],
                    $line['expiry_date'],
                    $line['sale_price']
                );

                if ($status === 'Received') {
                    $inventory->quantity_in_stock = (float) $inventory->quantity_in_stock + $line['received_base_quantity'];
                    if ($line['sale_price'] !== null) {
                        $inventory->sale_price = $line['sale_price'];
                    }
                    if ($line['expiry_date']) {
                        $inventory->expiry_date = $line['expiry_date'];
                    }
                    $inventory->save();

                    InventoryTransaction::create([
                        'product_uid' => $line['product_uid'],
                        'warehouse_uid' => null,
                        'transaction_type' => 'Purchase',
                        'reference_no' => $invoiceNo,
                        'transaction_date' => now(),
                        'quantity' => $line['received_base_quantity'],
                        'unit_cost' => $line['purchase_price'],
                        'total_cost' => $line['line_total'],
                        'user_uid' => Auth::id(),
                        'remarks' => 'Purchase ' . $invoiceNo . ' batch ' . $line['batch_no'],
                        'created_date' => now(),
                    ]);
                }

                PurchaseInvoiceDetail::create([
                    'purchase_invoice_uid' => $invoice->uid,
                    'inventory_uid' => $inventory->uid,
                    'purchase_quantity' => $line['purchase_quantity'],
                    'bonus_quantity' => $line['bonus_quantity'],
                    'loose_quantity' => $line['loose_quantity'],
                    'units_per_pack' => $line['units_per_pack'],
                    'received_base_quantity' => $line['received_base_quantity'],
                    'purchase_price' => $line['purchase_price'],
                    'discount_amount' => $line['discount_amount'],
                    'tax_amount' => $line['tax_amount'],
                    'line_total' => $line['line_total'],
                    'remarks' => $line['remarks'],
                ]);
            }

            DB::commit();

            return redirect()->route('purchase-invoices.show', $invoice->uid)
                ->with('success', 'Purchase invoice ' . $invoiceNo . ' saved successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Purchase invoice save failed', ['message' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Purchase was not saved. ' . $e->getMessage());
        }
    }

    public function show($uid)
    {
        $invoice = PurchaseInvoice::with(['details.inventory.product'])->findOrFail($uid);

        return view('purchase-invoices.show', compact('invoice'));
    }

    public function edit($uid)
    {
        $invoice = PurchaseInvoice::with(['details.inventory.product'])->findOrFail($uid);
        $products = Product::where('is_active', true)->orderBy('product_name')->get();

        return view('purchase-invoices.edit', compact('invoice', 'products'));
    }

    public function update(Request $request, $uid)
    {
        $invoice = PurchaseInvoice::findOrFail($uid);

        $validated = $request->validate([
            'supplier_name' => 'required|string|max:150',
            'supplier_invoice_no' => 'nullable|string|max:100',
            'invoice_date' => 'required|date',
            'received_date' => 'nullable|date',
            'payment_method' => 'nullable|string|max:50',
            'paid_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
            'invoice_status' => 'nullable|in:Draft,Received,Cancelled',
            'payment_status' => 'nullable|in:Unpaid,Partial,Paid',
        ]);

        $paid = (float) ($validated['paid_amount'] ?? $invoice->paid_amount);
        $net = (float) $invoice->net_amount;
        // Keep existing net unless header charges change without lines rebuild
        $discount = (float) ($validated['discount_amount'] ?? $invoice->discount_amount);
        $tax = (float) ($validated['tax_amount'] ?? $invoice->tax_amount);
        $other = (float) ($validated['other_charges'] ?? $invoice->other_charges);
        $subtotal = (float) $invoice->subtotal;
        $net = round($subtotal - $discount + $tax + $other, 2);
        $balance = round($net - $paid, 2);

        $invoice->update([
            'supplier_name' => $validated['supplier_name'],
            'supplier_invoice_no' => $validated['supplier_invoice_no'] ?? null,
            'invoice_date' => $validated['invoice_date'],
            'received_date' => $validated['received_date'] ?? null,
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'other_charges' => $other,
            'net_amount' => $net,
            'paid_amount' => $paid,
            'balance_amount' => $balance,
            'payment_method' => $validated['payment_method'] ?? null,
            'invoice_status' => $validated['invoice_status'] ?? $invoice->invoice_status,
            'payment_status' => $validated['payment_status'] ?? ($balance <= 0 ? 'Paid' : ($paid > 0 ? 'Partial' : 'Unpaid')),
            'remarks' => $validated['remarks'] ?? null,
        ]);

        return redirect()->route('purchase-invoices.show', $invoice->uid)
            ->with('success', 'Purchase invoice updated.');
    }

    public function destroy($uid)
    {
        $invoice = PurchaseInvoice::with('details')->findOrFail($uid);
        if ($invoice->invoice_status === 'Received') {
            return back()->with('error', 'Received purchases cannot be deleted. Mark as Cancelled instead.');
        }

        DB::transaction(function () use ($invoice) {
            PurchaseInvoiceDetail::where('purchase_invoice_uid', $invoice->uid)->delete();
            $invoice->delete();
        });

        return redirect()->route('purchase-invoices.index')->with('success', 'Purchase invoice deleted.');
    }

    public function print($uid)
    {
        $invoice = PurchaseInvoice::with(['details.inventory.product'])->findOrFail($uid);

        return view('purchase-invoices.print', compact('invoice'));
    }

    private function generateInvoiceNo(): string
    {
        $date = now()->format('Ymd');
        $lastInvoice = PurchaseInvoice::where('invoice_no', 'like', "PI{$date}%")
            ->orderBy('invoice_no', 'desc')
            ->first();

        $newNumber = $lastInvoice
            ? ((int) substr($lastInvoice->invoice_no, -4)) + 1
            : 1;

        return 'PI' . $date . str_pad((string) $newNumber, 4, '0', STR_PAD_LEFT);
    }
}
