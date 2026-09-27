<?php

namespace App\Services;

use App\Models\Company;

class FeatureManager
{
    /**
     * Complete list of modular features available in CodiceSync.
     */
    public static function getAllFeatures(): array
    {
        return [
            'pos' => [
                'name' => 'POS Billing Counter',
                'description' => 'Fast touch & barcode POS billing counter for retail, grocery & stores.',
                'category' => 'Sales',
                'icon' => 'fa-cash-register',
            ],
            'estimates' => [
                'name' => 'Estimates & Quotations',
                'description' => 'Create quotations with 1-click conversion into Sales Invoices.',
                'category' => 'Sales',
                'icon' => 'fa-file-lines',
            ],
            'proforma' => [
                'name' => 'Proforma Invoices',
                'description' => 'Issue official proforma invoices prior to final billing.',
                'category' => 'Sales',
                'icon' => 'fa-receipt',
            ],
            'orders' => [
                'name' => 'Sale & Purchase Orders',
                'description' => 'Manage incoming customer orders and supplier purchase orders.',
                'category' => 'Orders',
                'icon' => 'fa-cart-shopping',
            ],
            'delivery_challan' => [
                'name' => 'Delivery Challans & Logistics',
                'description' => 'Dispatch goods with vehicle, driver, and logistics tracking.',
                'category' => 'Logistics',
                'icon' => 'fa-truck-fast',
            ],
            'sale_return' => [
                'name' => 'Sales & Purchase Returns',
                'description' => 'Issue Credit Notes and Debit Notes for returned goods.',
                'category' => 'Sales',
                'icon' => 'fa-arrow-rotate-left',
            ],
            'warehouses' => [
                'name' => 'Multi-Warehouse Management',
                'description' => 'Track stock across multiple godowns and warehouses.',
                'category' => 'Inventory',
                'icon' => 'fa-warehouse',
            ],
            'barcodes' => [
                'name' => 'Barcode Generator',
                'description' => 'Generate and print customized product barcodes.',
                'category' => 'Inventory',
                'icon' => 'fa-barcode',
            ],
            'banking' => [
                'name' => 'Bank Accounts & Cash',
                'description' => 'Multi-bank accounts, cash in hand, and bank-to-cash transfers.',
                'category' => 'Banking',
                'icon' => 'fa-building-columns',
            ],
            'loans' => [
                'name' => 'Loan Accounts & EMIs',
                'description' => 'Track loans borrowed or given, principal, and interest.',
                'category' => 'Banking',
                'icon' => 'fa-hand-holding-dollar',
            ],
            'cheques' => [
                'name' => 'Cheques Management',
                'description' => 'Track received & issued cheques, deposits, and clearances.',
                'category' => 'Banking',
                'icon' => 'fa-money-check',
            ],
            'expenses' => [
                'name' => 'Business Expense Tracking',
                'description' => 'Itemized operational expenses with custom categories.',
                'category' => 'Finance',
                'icon' => 'fa-receipt',
            ],
            'reports_basic' => [
                'name' => 'Standard Reports',
                'description' => 'Daybook, Sales report, Purchase report, and Party statements.',
                'category' => 'Reports',
                'icon' => 'fa-chart-simple',
            ],
            'reports_advanced' => [
                'name' => 'Profit & Loss & Financials',
                'description' => 'Profit & Loss statements, Balance Sheet, and Cash Flow analytics.',
                'category' => 'Reports',
                'icon' => 'fa-chart-pie',
            ],
            'tally_export' => [
                'name' => 'Exports to Tally ERP',
                'description' => 'Export master chart of accounts and vouchers directly to Tally.',
                'category' => 'Integrations',
                'icon' => 'fa-file-export',
            ],
            'reminders' => [
                'name' => 'Payment Reminders (WhatsApp/SMS)',
                'description' => 'Automated overdue balance reminders to customers.',
                'category' => 'CRM',
                'icon' => 'fa-comment-sms',
            ],
            'brokerage' => [
                'name' => 'Brokers & Commissions',
                'description' => 'Mandi and commodity broker commission calculation and tracking.',
                'category' => 'Trading',
                'icon' => 'fa-handshake',
            ],
        ];
    }

    /**
     * Check if a feature is enabled for a given company.
     */
    public static function isEnabled(?Company $company, string $featureKey): bool
    {
        if (!$company) {
            return false;
        }

        return $company->hasFeature($featureKey);
    }

    /**
     * Default feature templates based on plan tier.
     */
    public static function getDefaultFeaturesForPlan(string $plan): array
    {
        $all = array_keys(self::getAllFeatures());

        switch (strtolower($plan)) {
            case 'starter':
            case 'basic':
                return ['reports_basic', 'sale_return', 'expenses'];
            case 'standard':
            case 'business':
                return [
                    'pos', 'estimates', 'proforma', 'orders', 'sale_return',
                    'banking', 'expenses', 'reports_basic', 'reminders'
                ];
            case 'pro':
            case 'premium':
            case 'enterprise':
            default:
                return $all; // All features enabled
        }
    }
}
