<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_ins')) {
            DB::statement('ALTER TABLE payment_ins MODIFY party_id BIGINT UNSIGNED NULL');
            Schema::table('payment_ins', function (Blueprint $table) {
                if (!Schema::hasColumn('payment_ins', 'entity_type')) {
                    $table->string('entity_type', 20)->nullable()->after('party_id');
                }
                if (!Schema::hasColumn('payment_ins', 'entity_id')) {
                    $table->unsignedBigInteger('entity_id')->nullable()->after('entity_type');
                }
                if (!Schema::hasColumn('payment_ins', 'entity_name')) {
                    $table->string('entity_name')->nullable()->after('entity_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payment_ins')) {
            Schema::table('payment_ins', function (Blueprint $table) {
                if (Schema::hasColumn('payment_ins', 'entity_name')) {
                    $table->dropColumn('entity_name');
                }
                if (Schema::hasColumn('payment_ins', 'entity_id')) {
                    $table->dropColumn('entity_id');
                }
                if (Schema::hasColumn('payment_ins', 'entity_type')) {
                    $table->dropColumn('entity_type');
                }
            });
            DB::statement('ALTER TABLE payment_ins MODIFY party_id BIGINT UNSIGNED NOT NULL');
        }
    }
};
