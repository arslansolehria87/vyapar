<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'po_no')) {
                $table->string('po_no')->nullable()->after('party');
            }

            if (!Schema::hasColumn('expenses', 'po_date')) {
                $table->date('po_date')->nullable()->after('po_no');
            }

            if (!Schema::hasColumn('expenses', 'transaction_time')) {
                $table->string('transaction_time')->nullable()->after('po_date');
            }

            if (!Schema::hasColumn('expenses', 'deal_days')) {
                $table->integer('deal_days')->nullable()->after('transaction_time');
            }

            if (!Schema::hasColumn('expenses', 'due_date')) {
                $table->date('due_date')->nullable()->after('deal_days');
            }

            if (!Schema::hasColumn('expenses', 'payment_terms_name')) {
                $table->string('payment_terms_name')->nullable()->after('due_date');
            }

            if (!Schema::hasColumn('expenses', 'status')) {
                $table->string('status')->nullable()->after('payment_terms_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            foreach ([
                'payment_terms_name',
                'due_date',
                'deal_days',
                'transaction_time',
                'po_date',
                'po_no',
                'status',
            ] as $column) {
                if (Schema::hasColumn('expenses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
