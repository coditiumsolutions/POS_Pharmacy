<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProductCategoriesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $categories = ProductCategory::orderBy('category_name')->paginate(50);

        return view('product-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('product-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        unset($validated['image']);

        if ($request->hasFile('image')) {
            [$path, $name] = $this->storeCategoryImage($request->file('image'));
            $validated['image_path'] = $path;
            $validated['image_name'] = $name;
        }

        ProductCategory::create($validated);

        return redirect()->route('product-categories.index')->with('success', 'Category created.');
    }

    public function edit($uid)
    {
        $category = ProductCategory::findOrFail($uid);

        return view('product-categories.edit', compact('category'));
    }

    public function update(Request $request, $uid)
    {
        $category = ProductCategory::findOrFail($uid);
        $validated = $request->validate([
            'category_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'remove_image' => 'nullable|boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        unset($validated['image'], $validated['remove_image']);

        if ($request->boolean('remove_image') && $category->image_path) {
            $this->deletePublicFile($category->image_path);
            $validated['image_path'] = null;
            $validated['image_name'] = null;
        }

        if ($request->hasFile('image')) {
            if ($category->image_path) {
                $this->deletePublicFile($category->image_path);
            }
            [$path, $name] = $this->storeCategoryImage($request->file('image'));
            $validated['image_path'] = $path;
            $validated['image_name'] = $name;
        }

        $category->update($validated);

        return redirect()->route('product-categories.index')->with('success', 'Category updated.');
    }

    public function destroy($uid)
    {
        $category = ProductCategory::findOrFail($uid);
        $label = $category->category_name ?? 'Category';
        $productCount = Product::where('category_uid', $uid)->count();

        if ($productCount > 0) {
            return back()->with(
                'error',
                "Cannot delete \"{$label}\" because it has {$productCount} product(s)."
            );
        }

        if ($category->image_path) {
            $this->deletePublicFile($category->image_path);
        }

        $category->delete();

        return back()->with('success', "Category \"{$label}\" deleted.");
    }

    /**
     * @return array{0: string, 1: string} [relativePath, originalName]
     */
    private function storeCategoryImage($file): array
    {
        $dir = $this->publicUploadRoot() . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'categories';
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $original = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $stored = 'category-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(6)) . '.' . $ext;
        $file->move($dir, $stored);

        // Keep local public/ in sync when Hostinger uses public_html as docroot.
        $alt = public_path('uploads/categories');
        if (realpath($alt) !== realpath($dir) && is_dir(dirname($alt))) {
            if (!File::isDirectory($alt)) {
                File::makeDirectory($alt, 0755, true);
            }
            @copy($dir . DIRECTORY_SEPARATOR . $stored, $alt . DIRECTORY_SEPARATOR . $stored);
        }

        return ['uploads/categories/' . $stored, $original];
    }

    private function publicUploadRoot(): string
    {
        $html = base_path('public_html');
        if (is_dir($html)) {
            return $html;
        }

        return public_path();
    }

    private function deletePublicFile(string $relativePath): void
    {
        $relativePath = ltrim(str_replace(['\\', '..'], ['/', ''], $relativePath), '/');
        foreach ([$this->publicUploadRoot(), public_path()] as $root) {
            $full = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            if (is_file($full)) {
                @unlink($full);
            }
        }
    }
}
