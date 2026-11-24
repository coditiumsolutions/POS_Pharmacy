<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoice extends Model
{
    use HasFactory;

    protected $table = 'purchase_invoices';
    protected $primaryKey = 'uid';
    public $timestamps = true;

    protected $fillable = [
        'invoice_no',
        'supplier_invoice_no',
        'supplier_name',
        'invoice_date',
        'received_date',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'other_charges',
        'net_amount',
        'paid_amount',
        'balance_amount',
        'payment_status',
        'invoice_status',
        'payment_method',
        'remarks',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'received_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(PurchaseInvoiceDetail::class, 'purchase_invoice_uid', 'uid');
    }
}
