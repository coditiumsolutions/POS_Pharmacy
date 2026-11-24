<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Clear dependent product/inventory data so schema can be rebuilt safely.
        $this->deleteIfExists('inventory_transactions');
        $this->deleteIfExists('inventories');
        $this->deleteIfExists('sales_invoice_details');
        $this->deleteIfExists('purchase_invoice_details');
        $this->deleteIfExists('stock_adjustment_details');
        $this->deleteIfExists('customer_monthly_supply_overrides');
        $this->deleteIfExists('customer_monthly_supplies');
        $this->deleteIfExists('products');
        $this->deleteIfExists('product_categories');
        $this->deleteIfExists('categories');

        // categories (replaces product_categories)
        if (Schema::hasTable('product_categories') && !Schema::hasTable('categories')) {
            Schema::rename('product_categories', 'categories');
        }

        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->increments('uid');
                $table->string('category_name', 100);
                $table->string('description', 255)->nullable();
                $table->boolean('is_active')->default(1);
            });
        } else {
            Schema::table('categories', function (Blueprint $table) {
                if (!Schema::hasColumn('categories', 'category_name')) {
                    $table->string('category_name', 100)->nullable();
                }
                if (!Schema::hasColumn('categories', 'description')) {
                    $table->string('description', 255)->nullable();
                }
                if (!Schema::hasColumn('categories', 'is_active')) {
                    $table->boolean('is_active')->default(1);
                }
            });

            $dropCategoryCols = [
                'category_code', 'image_path', 'image_name', 'created_date', 'updated_date',
            ];
            foreach ($dropCategoryCols as $col) {
                if (Schema::hasColumn('categories', $col)) {
                    Schema::table('categories', function (Blueprint $table) use ($col) {
                        $table->dropColumn($col);
                    });
                }
            }
        }

        // products — reshape to pharmacy master fields
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'generic_name')) {
                $table->string('generic_name', 150)->nullable()->after('product_name');
            }
            if (!Schema::hasColumn('products', 'company_name')) {
                $table->string('company_name', 150)->nullable()->after('category_uid');
            }
            if (!Schema::hasColumn('products', 'strength')) {
                $table->string('strength', 50)->nullable()->after('company_name');
            }
            if (!Schema::hasColumn('products', 'pack_unit')) {
                $table->string('pack_unit', 50)->nullable();
            }
            if (!Schema::hasColumn('products', 'units_per_pack')) {
                $table->integer('units_per_pack')->default(1);
            }
            if (!Schema::hasColumn('products', 'reorder_level')) {
                $table->decimal('reorder_level', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('products', 'allow_loose_sale')) {
                $table->boolean('allow_loose_sale')->default(0);
            }
            if (!Schema::hasColumn('products', 'is_prescription_required')) {
                $table->boolean('is_prescription_required')->default(0);
            }
            if (!Schema::hasColumn('products', 'remarks')) {
                $table->text('remarks')->nullable();
            }
        });

        // Widen/adjust remarks if it was varchar
        try {
            DB::statement('ALTER TABLE `products` MODIFY `remarks` TEXT NULL');
        } catch (\Throwable $e) {
            // ignore if already TEXT
        }

        $dropProductCols = [
            'product_code', 'branch_uid', 'unit_uid', 'brand_name', 'purchase_price',
            'wholesale_price', 'minimum_stock', 'maximum_stock', 'tax_percent',
            'is_taxable', 'description', 'image_path', 'image_name',
            'created_date', 'updated_date',
        ];
        foreach ($dropProductCols as $col) {
            if (Schema::hasColumn('products', $col)) {
                $this->dropProductIndexesForColumn($col);
                Schema::table('products', function (Blueprint $table) use ($col) {
                    $table->dropColumn($col);
                });
            }
        }

        // Ensure sale_price / discount defaults
        try {
            DB::statement('ALTER TABLE `products` MODIFY `sale_price` DECIMAL(12,2) NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE `products` MODIFY `discount_percent` DECIMAL(5,2) NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE `products` MODIFY `category_uid` INT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE `products` MODIFY `is_active` TINYINT(1) NOT NULL DEFAULT 1');
        } catch (\Throwable $e) {
            // keep going
        }

        // inventories — full rebuild to match pharmacy batch stock
        Schema::dropIfExists('inventories');
        Schema::create('inventories', function (Blueprint $table) {
            $table->increments('uid');
            $table->unsignedInteger('product_uid');
            $table->string('batch_no', 100)->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('quantity_in_stock', 12, 2)->default(0);
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->string('supplier_name', 150)->nullable();
            $table->date('date_of_purchase')->nullable();
            $table->text('remarks')->nullable();

            $table->index('product_uid');
            $table->index('batch_no');
            $table->index('expiry_date');
            $table->unique(['product_uid', 'batch_no'], 'inventories_product_batch_unique');
        });
    }

    public function down(): void
    {
        // Non-reversible pharmacy reshape; recreate minimal previous shapes if needed.
    }

    private function deleteIfExists(string $table): void
    {
        if (Schema::hasTable($table)) {
            DB::table($table)->delete();
        }
    }

    private function dropProductIndexesForColumn(string $column): void
    {
        $db = Schema::getConnection()->getDatabaseName();
        $indexes = DB::select(
            'SELECT DISTINCT INDEX_NAME FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? AND INDEX_NAME != ?',
            [$db, 'products', $column, 'PRIMARY']
        );
        foreach ($indexes as $idx) {
            $name = $idx->INDEX_NAME;
            try {
                DB::statement("ALTER TABLE `products` DROP INDEX `{$name}`");
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }
};
