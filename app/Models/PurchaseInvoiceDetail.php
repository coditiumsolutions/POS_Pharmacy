<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoiceDetail extends Model
{
    use HasFactory;

    protected $table = 'purchase_invoice_details';
    protected $primaryKey = 'uid';
    public $timestamps = false;

    protected $fillable = [
        'purchase_invoice_uid',
        'inventory_uid',
        'purchase_quantity',
        'bonus_quantity',
        'loose_quantity',
        'units_per_pack',
        'received_base_quantity',
        'purchase_price',
        'discount_amount',
        'tax_amount',
        'line_total',
        'remarks',
    ];

    protected $casts = [
        'purchase_quantity' => 'decimal:3',
        'bonus_quantity' => 'decimal:3',
        'loose_quantity' => 'decimal:3',
        'units_per_pack' => 'decimal:3',
        'received_base_quantity' => 'decimal:3',
        'purchase_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_uid', 'uid');
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_uid', 'uid');
    }
}
