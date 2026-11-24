<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = $request->get('search');
        $categoryUid = $request->get('category_uid');

        $query = Product::with(['category']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('generic_name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        if ($categoryUid) {
            $query->where('category_uid', $categoryUid);
        }

        $products = $query->orderBy('product_name')->paginate(50)->withQueryString();
        $categories = ProductCategory::where('is_active', true)->orderBy('category_name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = ProductCategory::where('is_active', true)->orderBy('category_name')->get();

        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'barcode' => 'nullable|string|max:100',
            'product_name' => 'required|string|max:200',
            'generic_name' => 'nullable|string|max:150',
            'category_uid' => 'required|exists:categories,uid',
            'company_name' => 'nullable|string|max:150',
            'strength' => 'nullable|string|max:50',
            'pack_unit' => 'nullable|string|max:50',
            'units_per_pack' => 'nullable|integer|min:1',
            'sale_price' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'reorder_level' => 'nullable|numeric|min:0',
            'allow_loose_sale' => 'nullable|boolean',
            'is_prescription_required' => 'nullable|boolean',
            'remarks' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['units_per_pack'] = (int) ($validated['units_per_pack'] ?? 1);
        $validated['sale_price'] = $validated['sale_price'] ?? 0;
        $validated['discount_percent'] = $validated['discount_percent'] ?? 0;
        $validated['reorder_level'] = $validated['reorder_level'] ?? 0;
        $validated['allow_loose_sale'] = $request->boolean('allow_loose_sale');
        $validated['is_prescription_required'] = $request->boolean('is_prescription_required');
        $validated['is_active'] = $request->boolean('is_active', true);

        $product = Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function edit($uid)
    {
        $product = Product::findOrFail($uid);
        $categories = ProductCategory::where('is_active', true)->orderBy('category_name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, $uid)
    {
        $product = Product::findOrFail($uid);

        $validated = $request->validate([
            'barcode' => 'nullable|string|max:100',
            'product_name' => 'required|string|max:200',
            'generic_name' => 'nullable|string|max:150',
            'category_uid' => 'required|exists:categories,uid',
            'company_name' => 'nullable|string|max:150',
            'strength' => 'nullable|string|max:50',
            'pack_unit' => 'nullable|string|max:50',
            'units_per_pack' => 'nullable|integer|min:1',
            'sale_price' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'reorder_level' => 'nullable|numeric|min:0',
            'allow_loose_sale' => 'nullable|boolean',
            'is_prescription_required' => 'nullable|boolean',
            'remarks' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['units_per_pack'] = (int) ($validated['units_per_pack'] ?? 1);
        $validated['sale_price'] = $validated['sale_price'] ?? 0;
        $validated['discount_percent'] = $validated['discount_percent'] ?? 0;
        $validated['reorder_level'] = $validated['reorder_level'] ?? 0;
        $validated['allow_loose_sale'] = $request->boolean('allow_loose_sale');
        $validated['is_prescription_required'] = $request->boolean('is_prescription_required');
        $validated['is_active'] = $request->boolean('is_active', true);

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy($uid)
    {
        $product = Product::findOrFail($uid);
        $label = $product->product_name ?? 'Product';

        $reasons = [];
        $salesCount = DB::table('sales_invoice_details')->where('product_uid', $uid)->count();
        if ($salesCount > 0) {
            $reasons[] = "{$salesCount} sales invoice line(s)";
        }
        $purchaseCount = DB::table('purchase_invoice_details')->where('product_uid', $uid)->count();
        if ($purchaseCount > 0) {
            $reasons[] = "{$purchaseCount} purchase invoice line(s)";
        }
        $adjustmentCount = DB::table('stock_adjustment_details')->where('product_uid', $uid)->count();
        if ($adjustmentCount > 0) {
            $reasons[] = "{$adjustmentCount} stock adjustment line(s)";
        }
        $supplyCount = DB::table('customer_monthly_supplies')->where('product_uid', $uid)->count();
        if ($supplyCount > 0) {
            $reasons[] = "{$supplyCount} monthly supply record(s)";
        }

        if ($reasons) {
            return back()->with(
                'error',
                "Cannot delete \"{$label}\" because it is used in " . implode(', ', $reasons) . '.'
            );
        }

        try {
            DB::transaction(function () use ($product, $uid) {
                DB::table('inventory_transactions')->where('product_uid', $uid)->delete();
                Inventory::where('product_uid', $uid)->delete();
                DB::table('customer_monthly_supply_overrides')->where('product_uid', $uid)->delete();
                $product->delete();
            });
        } catch (QueryException $e) {
            return back()->with('error', "Could not delete \"{$label}\" because it is still referenced.");
        }

        return back()->with('success', "Product \"{$label}\" deleted successfully.");
    }
}
