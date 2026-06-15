<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('transactions') || Schema::hasColumn('transactions', 'item_id')) {
            return;
        }

        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('item_id')->nullable()->after('broker_id');
        });

        DB::statement('ALTER TABLE transactions ADD CONSTRAINT transactions_item_id_foreign FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE SET NULL');
    }

    public function down(): void
    {
        if (!Schema::hasTable('transactions') || !Schema::hasColumn('transactions', 'item_id')) {
            return;
        }

        try {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropForeign('transactions_item_id_foreign');
            });
        } catch (\Throwable $e) {
            // If the FK name differs in a local DB, ignore and drop the column.
        }

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('item_id');
        });
    }
};
