<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'pack_unit')) {
                $table->string('pack_unit', 50)->nullable()->after('unit_uid');
            }
            if (!Schema::hasColumn('products', 'remarks')) {
                $table->string('remarks', 500)->nullable()->after('description');
            }
        });

        Schema::table('inventories', function (Blueprint $table) {
            if (!Schema::hasColumn('inventories', 'batch_no')) {
                $table->string('batch_no', 100)->nullable()->default('')->after('warehouse_uid');
            }
            if (!Schema::hasColumn('inventories', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('batch_no');
            }
            if (!Schema::hasColumn('inventories', 'loose_quantity')) {
                $table->decimal('loose_quantity', 18, 3)->default(0)->after('quantity_out');
            }
            if (!Schema::hasColumn('inventories', 'purchase_price')) {
                $table->decimal('purchase_price', 18, 2)->default(0)->after('loose_quantity');
            }
            if (!Schema::hasColumn('inventories', 'discount')) {
                $table->decimal('discount', 18, 2)->default(0)->after('purchase_price');
            }
            if (!Schema::hasColumn('inventories', 'sale_value')) {
                $table->decimal('sale_value', 18, 2)->default(0)->after('discount');
            }
            if (!Schema::hasColumn('inventories', 'date_of_purchase')) {
                $table->date('date_of_purchase')->nullable()->after('sale_value');
            }
            if (!Schema::hasColumn('inventories', 'vendor_uid')) {
                $table->unsignedInteger('vendor_uid')->nullable()->after('date_of_purchase');
                $table->index('vendor_uid');
            }
            if (!Schema::hasColumn('inventories', 'remarks')) {
                $table->string('remarks', 500)->nullable()->after('vendor_uid');
            }
        });

        // Normalize null batches so unique index works.
        DB::table('inventories')->whereNull('batch_no')->update(['batch_no' => '']);

        // Allow multiple batches per product/warehouse.
        $this->dropIndexIfExists('inventories', 'inventories_product_uid_warehouse_uid_unique');
        $this->dropIndexIfExists('inventories', 'inventories_product_warehouse_batch_unique');

        Schema::table('inventories', function (Blueprint $table) {
            $table->unique(
                ['product_uid', 'warehouse_uid', 'batch_no'],
                'inventories_product_warehouse_batch_unique'
            );
        });

        $this->addIndexIfMissing('inventories', 'inventories_expiry_date_index', 'expiry_date');
        $this->addIndexIfMissing('inventories', 'inventories_batch_no_index', 'batch_no');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('inventories', 'inventories_product_warehouse_batch_unique');
        $this->dropIndexIfExists('inventories', 'inventories_expiry_date_index');
        $this->dropIndexIfExists('inventories', 'inventories_batch_no_index');

        Schema::table('inventories', function (Blueprint $table) {
            if (Schema::hasColumn('inventories', 'vendor_uid')) {
                $table->dropIndex(['vendor_uid']);
            }

            $cols = [
                'batch_no', 'expiry_date', 'loose_quantity', 'purchase_price',
                'discount', 'sale_value', 'date_of_purchase', 'vendor_uid', 'remarks',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('inventories', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->unique(
                ['product_uid', 'warehouse_uid'],
                'inventories_product_uid_warehouse_uid_unique'
            );
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'pack_unit')) {
                $table->dropColumn('pack_unit');
            }
            if (Schema::hasColumn('products', 'remarks')) {
                $table->dropColumn('remarks');
            }
        });
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        $db = Schema::getConnection()->getDatabaseName();
        $exists = DB::selectOne(
            'SELECT COUNT(1) AS c FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$db, $table, $index]
        );
        if ((int) ($exists->c ?? 0) > 0) {
            DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$index}`");
        }
    }

    private function addIndexIfMissing(string $table, string $index, string $column): void
    {
        $db = Schema::getConnection()->getDatabaseName();
        $exists = DB::selectOne(
            'SELECT COUNT(1) AS c FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$db, $table, $index]
        );
        if ((int) ($exists->c ?? 0) === 0) {
            DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$index}` (`{$column}`)");
        }
    }
};
