<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sale_payments')) {
            return;
        }

        Schema::table('sale_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_payments', 'payment_in_id')) {
                $table->foreignId('payment_in_id')
                    ->nullable()
                    ->after('sale_id')
                    ->constrained('payment_ins')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('sale_payments')) {
            return;
        }

        Schema::table('sale_payments', function (Blueprint $table) {
            if (Schema::hasColumn('sale_payments', 'payment_in_id')) {
                $table->dropConstrainedForeignId('payment_in_id');
            }
        });
    }
};
