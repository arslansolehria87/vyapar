<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payment_ins')) {
            return;
        }

        Schema::table('payment_ins', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_ins', 'attachments')) {
                $table->json('attachments')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('payment_ins')) {
            return;
        }

        Schema::table('payment_ins', function (Blueprint $table) {
            if (Schema::hasColumn('payment_ins', 'attachments')) {
                $table->dropColumn('attachments');
            }
        });
    }
};
