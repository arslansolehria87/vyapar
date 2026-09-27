<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add SaaS subscription columns to companies table
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'status')) {
                $table->string('status', 20)->default('active')->after('name');
            }
            if (!Schema::hasColumn('companies', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('status');
            }
            if (!Schema::hasColumn('companies', 'subscription_plan')) {
                $table->string('subscription_plan', 50)->default('custom')->after('is_active');
            }
            if (!Schema::hasColumn('companies', 'monthly_price')) {
                $table->decimal('monthly_price', 10, 2)->default(0)->after('subscription_plan');
            }
            if (!Schema::hasColumn('companies', 'subscription_starts_at')) {
                $table->timestamp('subscription_starts_at')->nullable()->after('monthly_price');
            }
            if (!Schema::hasColumn('companies', 'subscription_expires_at')) {
                $table->timestamp('subscription_expires_at')->nullable()->after('subscription_starts_at');
            }
            if (!Schema::hasColumn('companies', 'enabled_features')) {
                $table->json('enabled_features')->nullable()->after('subscription_expires_at');
            }
            if (!Schema::hasColumn('companies', 'max_users')) {
                $table->integer('max_users')->default(1)->after('enabled_features');
            }
            if (!Schema::hasColumn('companies', 'phone')) {
                $table->string('phone', 50)->nullable()->after('max_users');
            }
            if (!Schema::hasColumn('companies', 'email')) {
                $table->string('email', 100)->nullable()->after('phone');
            }
            if (!Schema::hasColumn('companies', 'city')) {
                $table->string('city', 100)->nullable()->after('email');
            }
            if (!Schema::hasColumn('companies', 'address')) {
                $table->text('address')->nullable()->after('city');
            }
        });

        // 2. Add SaaS and role columns to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_super_admin')) {
                $table->boolean('is_super_admin')->default(false)->after('password');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_super_admin');
            }
            if (!Schema::hasColumn('users', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('is_active')->constrained('companies')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 50)->nullable()->after('company_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn(['is_super_admin', 'is_active', 'company_id', 'phone']);
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'is_active',
                'subscription_plan',
                'monthly_price',
                'subscription_starts_at',
                'subscription_expires_at',
                'enabled_features',
                'max_users',
                'phone',
                'email',
                'city',
                'address',
            ]);
        });
    }
};
