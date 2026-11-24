<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('purchase_invoice_details')) {
            DB::table('purchase_invoice_details')->delete();
        }
        if (Schema::hasTable('purchase_invoices')) {
            DB::table('purchase_invoices')->delete();
        }
        if (Schema::hasTable('inventories')) {
            DB::table('inventories')->delete();
        }
        if (Schema::hasTable('inventory_transactions')) {
            DB::table('inventory_transactions')->delete();
        }

        Schema::dropIfExists('purchase_invoice_details');
        Schema::dropIfExists('purchase_invoices');
        Schema::dropIfExists('inventories');

        Schema::create('inventories', function (Blueprint $table) {
            $table->increments('uid'); // inventory_uid
            $table->unsignedInteger('product_uid');
            $table->string('batch_no', 100);
            $table->date('expiry_date')->nullable();
            $table->decimal('quantity_in_stock', 12, 3)->default(0);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->timestamps();

            $table->index('product_uid');
            $table->index('batch_no', 'idx_inventory_batch');
            $table->index('expiry_date', 'idx_inventory_expiry');
            $table->unique(['product_uid', 'batch_no'], 'inventories_product_batch_unique');
        });

        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->increments('uid'); // purchase_invoice_uid
            $table->string('invoice_no', 50)->unique();
            $table->string('supplier_invoice_no', 100)->nullable();
            $table->string('supplier_name', 150);
            $table->date('invoice_date');
            $table->date('received_date')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('other_charges', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance_amount', 12, 2)->default(0);
            $table->enum('payment_status', ['Unpaid', 'Partial', 'Paid'])->default('Unpaid');
            $table->enum('invoice_status', ['Draft', 'Received', 'Cancelled'])->default('Draft');
            $table->string('payment_method', 50)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_invoice_details', function (Blueprint $table) {
            $table->increments('uid'); // purchase_detail_uid
            $table->unsignedInteger('purchase_invoice_uid');
            $table->unsignedInteger('inventory_uid');
            $table->decimal('purchase_quantity', 12, 3)->default(0);
            $table->decimal('bonus_quantity', 12, 3)->default(0);
            $table->decimal('loose_quantity', 12, 3)->default(0);
            $table->decimal('units_per_pack', 12, 3)->default(1);
            $table->decimal('received_base_quantity', 12, 3)->default(0);
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->string('remarks', 255)->nullable();

            $table->index('purchase_invoice_uid');
            $table->index('inventory_uid', 'idx_purchase_detail_inventory');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Non-reversible rebuild
    }
};
