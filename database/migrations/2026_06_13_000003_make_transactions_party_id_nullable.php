<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('transactions') && Schema::hasColumn('transactions', 'party_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                try {
                    $table->dropForeign(['party_id']);
                } catch (\Throwable $e) {
                    // Foreign key may already be absent in some environments.
                }
            });

            DB::statement('ALTER TABLE transactions MODIFY party_id BIGINT UNSIGNED NULL');
            Schema::table('transactions', function (Blueprint $table) {
                $table->foreign('party_id')
                    ->references('id')
                    ->on('parties')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('transactions') && Schema::hasColumn('transactions', 'party_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                try {
                    $table->dropForeign(['party_id']);
                } catch (\Throwable $e) {
                    // ignore rollback issues when foreign key is already missing
                }
            });

            DB::statement('ALTER TABLE transactions MODIFY party_id BIGINT UNSIGNED NOT NULL');
            Schema::table('transactions', function (Blueprint $table) {
                $table->foreign('party_id')
                    ->references('id')
                    ->on('parties')
                    ->cascadeOnDelete();
            });
        }
    }
};
