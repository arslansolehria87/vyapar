<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables that require company_id for tenant scoping.
     */
    protected array $tables = [
        'sales',
        'sale_items',
        'parties',
        'party_groups',
        'items',
        'purchases',
        'purchase_items',
        'bank_accounts',
        'bank_transactions',
        'expenses',
        'expense_categories',
        'expense_items',
        'transactions',
        'transaction_items',
        'cheques',
        'loan_accounts',
        'warehouses',
        'brokers',
        'barcodes',
        'delivery_challans',
        'estimate_invoices',
        'payment_ins',
        'sale_terms_conditions',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'company_id')) {
                        $table->unsignedBigInteger('company_id')->nullable()->index()->after('id');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'company_id')) {
                        $table->dropIndex([$tableName . '_company_id_index']);
                        $table->dropColumn('company_id');
                    }
                });
            }
        }
    }
};
