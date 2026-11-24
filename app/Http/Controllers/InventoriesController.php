<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class InventoriesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $categoryUid = $request->get('category_uid');

        $query = Inventory::with(['product.category']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('batch_no', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('product_name', 'like', "%{$search}%")
                            ->orWhere('generic_name', 'like', "%{$search}%")
                            ->orWhere('barcode', 'like', "%{$search}%");
                    });
            });
        }

        if ($categoryUid !== null && $categoryUid !== '') {
            $query->whereHas('product', function ($pq) use ($categoryUid) {
                $pq->where('category_uid', $categoryUid);
            });
        }

        $inventories = $query->orderByDesc('uid')->paginate(25)->withQueryString();
        $categories = ProductCategory::where('is_active', true)->orderBy('category_name')->get();

        return view('inventories.index', compact('inventories', 'search', 'categories', 'categoryUid'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->orderBy('product_name')->get();

        return view('inventories.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_uid' => 'required|integer|exists:products,uid',
            'batch_no' => 'required|string|max:100',
            'expiry_date' => 'nullable|date',
            'quantity_in_stock' => 'nullable|numeric',
            'sale_price' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:255',
        ]);

        $batchNo = trim($validated['batch_no']);

        $exists = Inventory::where('product_uid', $validated['product_uid'])
            ->where('batch_no', $batchNo)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'batch_no' => 'Inventory already exists for this product and batch.',
            ]);
        }

        Inventory::create([
            'product_uid' => $validated['product_uid'],
            'batch_no' => $batchNo,
            'expiry_date' => $validated['expiry_date'] ?? null,
            'quantity_in_stock' => $validated['quantity_in_stock'] ?? 0,
            'sale_price' => $validated['sale_price'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
        ]);

        return redirect()->route('inventories.index')->with('success', 'Inventory created successfully.');
    }

    public function show($uid)
    {
        $inventory = Inventory::with(['product.category'])->findOrFail($uid);

        return view('inventories.show', compact('inventory'));
    }

    public function edit($uid)
    {
        $inventory = Inventory::with(['product'])->findOrFail($uid);
        $products = Product::where('is_active', true)->orderBy('product_name')->get();

        return view('inventories.edit', compact('inventory', 'products'));
    }

    public function update(Request $request, $uid)
    {
        $inventory = Inventory::findOrFail($uid);

        $validated = $request->validate([
            'batch_no' => 'required|string|max:100',
            'expiry_date' => 'nullable|date',
            'quantity_in_stock' => 'required|numeric',
            'sale_price' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:255',
        ]);

        $batchNo = trim($validated['batch_no']);

        $duplicate = Inventory::where('product_uid', $inventory->product_uid)
            ->where('batch_no', $batchNo)
            ->where('uid', '!=', $inventory->uid)
            ->exists();

        if ($duplicate) {
            return back()->withInput()->withErrors([
                'batch_no' => 'Another inventory row already uses this batch for the same product.',
            ]);
        }

        $inventory->update([
            'batch_no' => $batchNo,
            'expiry_date' => $validated['expiry_date'] ?? null,
            'quantity_in_stock' => $validated['quantity_in_stock'],
            'sale_price' => $validated['sale_price'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
        ]);

        return redirect()->route('inventories.index')->with('success', 'Inventory updated successfully.');
    }

    public function destroy($uid)
    {
        $inventory = Inventory::with('product')->findOrFail($uid);
        $label = $inventory->product->product_name ?? ('#' . $inventory->product_uid);
        $inventory->delete();

        return redirect()->route('inventories.index')->with('success', 'Inventory deleted: ' . $label);
    }

    public function printBalance()
    {
        $inventories = Inventory::with(['product'])
            ->get()
            ->sortBy(fn ($inv) => strtolower(($inv->product->product_name ?? '') . '|' . ($inv->batch_no ?? '')))
            ->values();

        return view('inventories.print-balance', compact('inventories'));
    }
}
