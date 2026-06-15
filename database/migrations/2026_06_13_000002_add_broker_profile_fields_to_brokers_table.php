<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brokers', function (Blueprint $table) {
            if (!Schema::hasColumn('brokers', 'city')) {
                $table->string('city')->nullable()->after('phone');
            }

            if (!Schema::hasColumn('brokers', 'commission_type')) {
                $table->string('commission_type')->default('percent')->after('address');
            }

            if (!Schema::hasColumn('brokers', 'total_brokerage')) {
                $table->decimal('total_brokerage', 12, 2)->default(0)->after('commission_rate');
            }

            if (!Schema::hasColumn('brokers', 'paid_brokerage')) {
                $table->decimal('paid_brokerage', 12, 2)->default(0)->after('total_brokerage');
            }

            if (!Schema::hasColumn('brokers', 'notes')) {
                $table->text('notes')->nullable()->after('paid_brokerage');
            }

            if (!Schema::hasColumn('brokers', 'status')) {
                $table->boolean('status')->default(true)->after('notes');
            }
        });

        if (Schema::hasColumn('brokers', 'is_active') && !Schema::hasColumn('brokers', 'status')) {
            Schema::table('brokers', function (Blueprint $table) {
                $table->boolean('status')->default(true)->after('notes');
            });
        }
    }

    public function down(): void
    {
        Schema::table('brokers', function (Blueprint $table) {
            $table->dropColumn(['city', 'commission_type', 'total_brokerage', 'paid_brokerage', 'notes', 'status']);
        });
    }
};
