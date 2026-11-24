<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventories';
    protected $primaryKey = 'uid';
    public $timestamps = true;
    protected $guarded = ['uid'];

    protected $casts = [
        'quantity_in_stock' => 'decimal:3',
        'sale_price' => 'decimal:2',
        'expiry_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uid', 'uid');
    }

    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseInvoiceDetail::class, 'inventory_uid', 'uid');
    }

    public function getCurrentQuantityAttribute()
    {
        return $this->quantity_in_stock;
    }

    public function getBranchNameAttribute(): string
    {
        return '—';
    }

    /**
     * Prefer earliest-expiring batch with stock for the product.
     */
    public static function findForProductBranch(int $productUid, int $branchUid = 0): ?self
    {
        $withStock = static::query()
            ->where('product_uid', $productUid)
            ->where('quantity_in_stock', '>', 0)
            ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expiry_date')
            ->first();

        if ($withStock) {
            return $withStock;
        }

        return static::query()
            ->where('product_uid', $productUid)
            ->orderBy('uid')
            ->first();
    }

    public static function findOrCreateBatch(
        int $productUid,
        string $batchNo,
        ?string $expiryDate = null,
        ?float $salePrice = null
    ): self {
        $inventory = static::query()
            ->where('product_uid', $productUid)
            ->where('batch_no', $batchNo)
            ->first();

        if ($inventory) {
            if ($expiryDate && !$inventory->expiry_date) {
                $inventory->expiry_date = $expiryDate;
            }
            if ($salePrice !== null && $inventory->sale_price === null) {
                $inventory->sale_price = $salePrice;
            }
            $inventory->save();

            return $inventory;
        }

        return static::create([
            'product_uid' => $productUid,
            'batch_no' => $batchNo,
            'expiry_date' => $expiryDate,
            'quantity_in_stock' => 0,
            'sale_price' => $salePrice,
        ]);
    }
}
