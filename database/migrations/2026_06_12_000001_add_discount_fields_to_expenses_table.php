<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('expenses')) {
            Schema::table('expenses', function (Blueprint $table) {
                if (!Schema::hasColumn('expenses', 'discount_percent')) {
                    $table->decimal('discount_percent', 10, 2)->default(0)->after('status');
                }
                if (!Schema::hasColumn('expenses', 'discount_amount')) {
                    $table->decimal('discount_amount', 12, 2)->default(0)->after('discount_percent');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('expenses')) {
            Schema::table('expenses', function (Blueprint $table) {
                if (Schema::hasColumn('expenses', 'discount_amount')) {
                    $table->dropColumn('discount_amount');
                }
                if (Schema::hasColumn('expenses', 'discount_percent')) {
                    $table->dropColumn('discount_percent');
                }
            });
        }
    }
};
