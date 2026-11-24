<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $primaryKey = 'uid';
    public $timestamps = false;

    protected $fillable = [
        'barcode',
        'product_name',
        'generic_name',
        'category_uid',
        'company_name',
        'strength',
        'pack_unit',
        'units_per_pack',
        'sale_price',
        'discount_percent',
        'reorder_level',
        'allow_loose_sale',
        'is_prescription_required',
        'remarks',
        'is_active',
    ];

    protected $casts = [
        'units_per_pack' => 'integer',
        'sale_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'allow_loose_sale' => 'boolean',
        'is_prescription_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_uid', 'uid');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'product_uid', 'uid');
    }
}
