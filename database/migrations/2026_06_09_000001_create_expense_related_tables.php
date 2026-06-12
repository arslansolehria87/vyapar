<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('expense_categories')) {
            Schema::create('expense_categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('type')->default('Indirect Expense');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('expenses')) {
            Schema::create('expenses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('expense_category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
                $table->string('expense_no')->nullable();
                $table->date('expense_date')->nullable();
                $table->foreignId('party_id')->nullable()->constrained('parties')->nullOnDelete();
                $table->string('party')->nullable();
                $table->boolean('tax_enabled')->default(false);
                $table->foreignId('tax_rate_id')->nullable()->constrained('tax_rates')->nullOnDelete();
                $table->string('tax_rate_name')->nullable();
                $table->decimal('tax_rate_value', 10, 4)->nullable();
                $table->decimal('tax_amount', 10, 2)->default(0);
                $table->json('items_json')->nullable();
                $table->json('additional_charges')->nullable();
                $table->json('transportation_details')->nullable();
                $table->json('attachments')->nullable();
                $table->text('description')->nullable();
                $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->string('payment_type')->nullable();
                $table->string('reference_no')->nullable();
                $table->decimal('balance', 12, 2)->default(0);
                $table->timestamps();
            });
        } else {
            if (!Schema::hasColumn('expenses', 'attachments')) {
                Schema::table('expenses', function (Blueprint $table) {
                    $table->json('attachments')->nullable()->after('description');
                });
            }
        }

        if (!Schema::hasTable('expense_items')) {
            Schema::create('expense_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('expense_id')->nullable()->constrained('expenses')->cascadeOnDelete();
                $table->string('name');
                $table->integer('quantity')->default(1);
                $table->decimal('unit_price', 12, 2)->default(0);
                $table->foreignId('tax_rate_id')->nullable()->constrained('tax_rates')->nullOnDelete();
                $table->string('tax_rate_name')->nullable();
                $table->decimal('tax_rate_value', 10, 4)->nullable();
                $table->decimal('tax_amount', 10, 2)->default(0);
                $table->decimal('amount', 12, 2)->default(0);
                $table->decimal('price', 12, 2)->default(0);
                $table->boolean('tax_included')->nullable();
                $table->timestamps();
            });
        } else {
            if (!Schema::hasColumn('expense_items', 'expense_id')) {
                Schema::table('expense_items', function (Blueprint $table) {
                    $table->foreignId('expense_id')->nullable()->after('user_id')->constrained('expenses')->cascadeOnDelete();
                });
            }
            if (!Schema::hasColumn('expense_items', 'quantity')) {
                Schema::table('expense_items', function (Blueprint $table) {
                    $table->integer('quantity')->default(1)->after('name');
                });
            }
            if (!Schema::hasColumn('expense_items', 'unit_price')) {
                Schema::table('expense_items', function (Blueprint $table) {
                    $table->decimal('unit_price', 12, 2)->default(0)->after('quantity');
                });
            }
            if (!Schema::hasColumn('expense_items', 'tax_rate_id')) {
                Schema::table('expense_items', function (Blueprint $table) {
                    $table->foreignId('tax_rate_id')->nullable()->constrained('tax_rates')->nullOnDelete()->after('unit_price');
                });
            }
            if (!Schema::hasColumn('expense_items', 'tax_rate_name')) {
                Schema::table('expense_items', function (Blueprint $table) {
                    $table->string('tax_rate_name')->nullable()->after('tax_rate_id');
                });
            }
            if (!Schema::hasColumn('expense_items', 'tax_rate_value')) {
                Schema::table('expense_items', function (Blueprint $table) {
                    $table->decimal('tax_rate_value', 10, 4)->nullable()->after('tax_rate_name');
                });
            }
            if (!Schema::hasColumn('expense_items', 'tax_amount')) {
                Schema::table('expense_items', function (Blueprint $table) {
                    $table->decimal('tax_amount', 10, 2)->default(0)->after('tax_rate_value');
                });
            }
            if (!Schema::hasColumn('expense_items', 'amount')) {
                Schema::table('expense_items', function (Blueprint $table) {
                    $table->decimal('amount', 12, 2)->default(0)->after('tax_amount');
                });
            }
            if (!Schema::hasColumn('expense_items', 'price')) {
                Schema::table('expense_items', function (Blueprint $table) {
                    $table->decimal('price', 12, 2)->default(0)->after('amount');
                });
            }
            if (!Schema::hasColumn('expense_items', 'tax_included')) {
                Schema::table('expense_items', function (Blueprint $table) {
                    $table->boolean('tax_included')->nullable()->after('price');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_items');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
    }
};
