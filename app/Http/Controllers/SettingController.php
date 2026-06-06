<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class SettingController extends Controller
{
    //

    public function general()
    {
        return view('dashboard.settings.general', [
            'bankAccountPasswordSet' => filled(AppSetting::getValue('bank_account_password')),
            'generalSettings' => $this->getGeneralSettings(),
        ]);
    }

    public function updateGeneral(Request $request)
    {
        $data = $request->validate([
            'bank_account_password' => ['nullable', 'string', 'min:4', 'max:255'],
            'more_transactions.estimate_quotation_enabled' => ['nullable', 'boolean'],
            'more_transactions.proforma_invoice_enabled' => ['nullable', 'boolean'],
            'more_transactions.sale_purchase_order_enabled' => ['nullable', 'boolean'],
            'more_transactions.other_income_enabled' => ['nullable', 'boolean'],
            'more_transactions.fixed_assets_enabled' => ['nullable', 'boolean'],
            'more_transactions.delivery_challan_enabled' => ['nullable', 'boolean'],
            'more_transactions.goods_return_on_delivery_challan' => ['nullable', 'boolean'],
            'more_transactions.print_amount_in_delivery_challan' => ['nullable', 'boolean'],
        ]);

        if (!empty($data['bank_account_password'])) {
            AppSetting::setValue('bank_account_password', Hash::make($data['bank_account_password']));
        }

        $generalSettings = array_replace_recursive($this->getGeneralSettings(), Arr::only($data, ['more_transactions']));
        AppSetting::setValue('general_settings', json_encode($generalSettings));

        return redirect()
            ->route('settings.general')
            ->with('success', 'General settings updated successfully.');
    }

    public function generalSidebarConfig()
    {
        return response()->json($this->getGeneralSettings());
    }

    public function transactions()
    {
        $settings = $this->getTransactionSettings();

        return view('dashboard.settings.transactions', [
            'countEnabled' => (bool) data_get($settings, 'items_table.count_enabled', false),
            'customerPoDetailsEnabled' => (bool) data_get($settings, 'transaction_header.customer_po_enabled', false),
            'transactionSettings' => $settings,
        ]);
    }

    public function updateTransactions(Request $request)
    {
        $data = $request->validate([
            'transaction_header.invoice_number_enabled' => ['nullable', 'boolean'],
            'transaction_header.transaction_time_enabled' => ['nullable', 'boolean'],
            'transaction_header.cash_sale_default' => ['nullable', 'boolean'],
            'transaction_header.billing_name_enabled' => ['nullable', 'boolean'],
            'transaction_header.customer_po_enabled' => ['nullable', 'boolean'],
            'items_table.free_item_qty_enabled' => ['nullable', 'boolean'],
            'items_table.count_enabled' => ['nullable', 'boolean'],
            'items_table.count_label' => ['nullable', 'string', 'max:100'],
            'more_transaction_features.terms_conditions_enabled' => ['nullable', 'boolean'],
            'more_transaction_features.due_dates_payment_terms_enabled' => ['nullable', 'boolean'],
            'more_transaction_features.quick_entry' => ['nullable', 'boolean'],
            'more_transaction_features.link_payment_to_invoices' => ['nullable', 'boolean'],
            'more_transaction_features.passcode_enabled' => ['nullable', 'boolean'],
            'more_transaction_features.do_not_show_invoice_preview' => ['nullable', 'boolean'],
            'transaction_totals.discount_enabled' => ['nullable', 'boolean'],
            'transaction_totals.tax_enabled' => ['nullable', 'boolean'],
            'transaction_totals.round_total_enabled' => ['nullable', 'boolean'],
            'transaction_totals.round_total_mode' => ['nullable', 'in:nearest,down-to,up-to'],
            'transaction_totals.round_total_precision' => ['nullable', 'integer', 'in:1,10,50,100,1000'],
            'sale_prefix.enabled' => ['nullable', 'boolean'],
            'sale_prefix.active' => ['nullable', 'string', 'max:20'],
            'sale_prefix.options' => ['nullable', 'array'],
            'sale_prefix.options.*' => ['nullable', 'string', 'max:20'],
            'transaction_prefixes' => ['nullable', 'array'],
            'transaction_prefixes.*.active' => ['nullable', 'string', 'max:20'],
            'transaction_prefixes.*.options' => ['nullable', 'array'],
            'transaction_prefixes.*.options.*' => ['nullable', 'string', 'max:20'],
            'payment_terms.enabled' => ['nullable', 'boolean'],
            'payment_terms.name' => ['nullable', 'string', 'max:100'],
            'payment_terms.days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'invoice_fields.custom_field_1.enabled' => ['nullable', 'boolean'],
            'invoice_fields.custom_field_1.label' => ['nullable', 'string', 'max:100'],
            'invoice_fields.date_field_2.enabled' => ['nullable', 'boolean'],
            'invoice_fields.date_field_2.label' => ['nullable', 'string', 'max:100'],
            'invoice_fields.date_field_2.format' => ['nullable', 'in:dd/mm/yyyy,yyyy/mm/dd,mm/yyyy,dd-mm-yyyy'],
            'transportation_details.enabled' => ['nullable', 'boolean'],
            'transportation_details.fields' => ['nullable', 'array'],
            'transportation_details.fields.*.key' => ['nullable', 'string', 'max:30'],
            'transportation_details.fields.*.label' => ['nullable', 'string', 'max:100'],
            'transportation_details.fields.*.enabled' => ['nullable', 'boolean'],
            'transportation_details.fields.*.show_in_print' => ['nullable', 'boolean'],
            'additional_charges.enabled' => ['nullable', 'boolean'],
            'additional_charges.items' => ['nullable', 'array'],
            'additional_charges.items.*.key' => ['nullable', 'string', 'max:30'],
            'additional_charges.items.*.enabled' => ['nullable', 'boolean'],
            'additional_charges.items.*.label' => ['nullable', 'string', 'max:100'],
            'additional_charges.items.*.tax_rate' => ['nullable', 'string', 'max:20'],
            'additional_charges.items.*.tax_enabled' => ['nullable', 'boolean'],
            'transaction_passcode' => ['nullable', 'string', 'digits:4'],
            'transaction_passcode_confirmation' => ['nullable', 'string', 'digits:4', 'same:transaction_passcode'],
            'count_enabled' => ['nullable', 'boolean'],
            'customer_po_enabled' => ['nullable', 'boolean'],
        ]);

        $settings = array_replace_recursive($this->getTransactionSettings(), $data);

        if ($request->has('count_enabled')) {
            $settings['items_table']['count_enabled'] = !empty($data['count_enabled']);
        }
        if ($request->has('customer_po_enabled')) {
            $settings['transaction_header']['customer_po_enabled'] = !empty($data['customer_po_enabled']);
        }
        if (!empty($data['transaction_passcode'])) {
            $settings['more_transaction_features']['transaction_passcode_hash'] = Hash::make($data['transaction_passcode']);
            $settings['more_transaction_features']['passcode_enabled'] = true;
        }
        if ($request->has('transaction_totals')) {
            $settings['transaction_totals']['discount_enabled'] = !empty(data_get($data, 'transaction_totals.discount_enabled'));
            $settings['transaction_totals']['tax_enabled'] = !empty(data_get($data, 'transaction_totals.tax_enabled'));
            $settings['transaction_totals']['round_total_enabled'] = !empty(data_get($data, 'transaction_totals.round_total_enabled'));
            $settings['transaction_totals']['round_total_mode'] = data_get($data, 'transaction_totals.round_total_mode', data_get($settings, 'transaction_totals.round_total_mode', 'down-to'));
            $settings['transaction_totals']['round_total_precision'] = (int) data_get($data, 'transaction_totals.round_total_precision', data_get($settings, 'transaction_totals.round_total_precision', 100));
        }

        AppSetting::setValue('transaction_items_count_enabled', !empty(data_get($settings, 'items_table.count_enabled')) ? '1' : '0');
        AppSetting::setValue('transaction_customer_po_enabled', !empty(data_get($settings, 'transaction_header.customer_po_enabled')) ? '1' : '0');
        AppSetting::setValue('sale_form_settings', json_encode($settings));

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaction settings updated successfully.',
                'count_enabled' => !empty(data_get($settings, 'items_table.count_enabled')),
                'customer_po_enabled' => !empty(data_get($settings, 'transaction_header.customer_po_enabled')),
                'settings' => $settings,
            ]);
        }

        return redirect()
            ->route('settings.transactions')
            ->with('success', 'Transaction settings updated successfully.');
    }

    public function taxes()
    {
        return view('dashboard.settings.taxes');
    }

    public function items()
    {
        return view('dashboard.settings.items', [
            'itemSettings' => $this->getItemSettings(),
        ]);
    }

    public function updateItems(Request $request)
    {
        $data = $request->validate([
            'enable_item' => ['nullable', 'boolean'],
            'sell_type' => ['nullable', 'in:product,service,both'],
            'barcode_scan_enabled' => ['nullable', 'boolean'],
            'direct_barcode_scan_enabled' => ['nullable', 'boolean'],
            'stock_maintenance_enabled' => ['nullable', 'boolean'],
            'manufacturing_enabled' => ['nullable', 'boolean'],
              'show_low_stock_dialog' => ['nullable', 'boolean'],
              'items_unit_enabled' => ['nullable', 'boolean'],
              'default_unit_enabled' => ['nullable', 'boolean'],
              'default_unit_label' => ['nullable', 'string', 'max:100'],
              'default_unit_base' => ['nullable', 'string', 'max:100'],
              'default_unit_secondary' => ['nullable', 'string', 'max:100'],
              'default_unit_rate' => ['nullable', 'numeric', 'min:0'],
              'item_category_enabled' => ['nullable', 'boolean'],
              'party_wise_item_rate_enabled' => ['nullable', 'boolean'],
              'description_enabled' => ['nullable', 'boolean'],
            'description_label' => ['nullable', 'string', 'max:100'],
            'item_wise_tax_enabled' => ['nullable', 'boolean'],
            'item_wise_discount_enabled' => ['nullable', 'boolean'],
            'update_sale_price_from_transaction' => ['nullable', 'boolean'],
            'quantity_decimals' => ['nullable', 'integer', 'min:0', 'max:4'],
            'wholesale_price_enabled' => ['nullable', 'boolean'],
            'free_item_qty_enabled' => ['nullable', 'boolean'],
            'count_enabled' => ['nullable', 'boolean'],
            'count_label' => ['nullable', 'string', 'max:100'],
            'mrp.enabled' => ['nullable', 'boolean'],
            'mrp.label' => ['nullable', 'string', 'max:100'],
            'mrp.calculate_sale_price_from_mrp' => ['nullable', 'boolean'],
            'mrp.use_mrp_for_batch_tracking' => ['nullable', 'boolean'],
            'serial_tracking.enabled' => ['nullable', 'boolean'],
            'serial_tracking.label' => ['nullable', 'string', 'max:100'],
            'batch_tracking.batch_no.enabled' => ['nullable', 'boolean'],
            'batch_tracking.batch_no.label' => ['nullable', 'string', 'max:100'],
            'batch_tracking.exp_date.enabled' => ['nullable', 'boolean'],
            'batch_tracking.exp_date.label' => ['nullable', 'string', 'max:100'],
            'batch_tracking.exp_date.format' => ['nullable', 'in:mm/yy,dd/mm/yyyy,yyyy/mm/dd'],
            'batch_tracking.mfg_date.enabled' => ['nullable', 'boolean'],
            'batch_tracking.mfg_date.label' => ['nullable', 'string', 'max:100'],
            'batch_tracking.mfg_date.format' => ['nullable', 'in:mm/yy,dd/mm/yyyy,yyyy/mm/dd'],
            'batch_tracking.model_no.enabled' => ['nullable', 'boolean'],
            'batch_tracking.model_no.label' => ['nullable', 'string', 'max:100'],
            'batch_tracking.size.enabled' => ['nullable', 'boolean'],
            'batch_tracking.size.label' => ['nullable', 'string', 'max:100'],
            'custom_fields' => ['nullable', 'array'],
            'custom_fields.*.key' => ['nullable', 'string', 'max:40'],
            'custom_fields.*.enabled' => ['nullable', 'boolean'],
            'custom_fields.*.label' => ['nullable', 'string', 'max:100'],
            'custom_fields.*.show_in_print' => ['nullable', 'boolean'],
        ]);

        $settings = array_replace_recursive($this->getItemSettings(), $data);
        AppSetting::setValue('item_form_settings', json_encode($settings));

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Item settings updated successfully.',
                'settings' => $settings,
            ]);
        }

        return redirect()
            ->route('settings.items')
            ->with('success', 'Item settings updated successfully.');
    }

    public function parties()
    {
        return view('dashboard.settings.parties', [
            'partySettings' => $this->getPartySettings(),
        ]);
    }

    public function partyReminders()
    {
        return view('dashboard.settings.party-reminders', [
            'partySettings' => $this->getPartySettings(),
            'reminderParties' => \App\Models\Party::query()->orderBy('name')->get(),
        ]);
    }

    public function transactionMessages()
    {
        return view('dashboard.settings.transaction-message');
    }

    public function printLayout()
    {
        return view('dashboard.settings.print');
    }

    private function getPartySettings(): array
    {
        return [
            'party_grouping' => AppSetting::getValue('party_grouping', '1') === '1',
            'shipping_address' => AppSetting::getValue('shipping_address', '1') === '1',
            'print_shipping_address' => AppSetting::getValue('print_shipping_address', '1') === '1',
            'party_status' => AppSetting::getValue('party_status', '1') === '1',
            'payment_reminder' => AppSetting::getValue('payment_reminder', '1') === '1',
            'payment_reminder_days' => (int) AppSetting::getValue('payment_reminder_days', '2'),
            'payment_reminder_message' => (string) AppSetting::getValue('payment_reminder_message', "Dear [Party Name],\n\nYour payment of [Amount] is pending with [Business Name].\n\n[Additional Message]\n\nIf you already have made the payment, kindly ignore this message."),
            'additional_field_1' => AppSetting::getValue('party_additional_field_1', '0') === '1',
            'additional_field_1_name' => (string) AppSetting::getValue('party_additional_field_1_name', ''),
            'additional_field_1_print' => AppSetting::getValue('party_additional_field_1_print', '0') === '1',
            'additional_field_2' => AppSetting::getValue('party_additional_field_2', '0') === '1',
            'additional_field_2_name' => (string) AppSetting::getValue('party_additional_field_2_name', ''),
            'additional_field_2_print' => AppSetting::getValue('party_additional_field_2_print', '0') === '1',
        ];
    }

    private function defaultGeneralSettings(): array
    {
        return [
            'more_transactions' => [
                'estimate_quotation_enabled' => true,
                'proforma_invoice_enabled' => true,
                'sale_purchase_order_enabled' => true,
                'other_income_enabled' => false,
                'fixed_assets_enabled' => false,
                'delivery_challan_enabled' => true,
                'goods_return_on_delivery_challan' => false,
                'print_amount_in_delivery_challan' => false,
            ],
        ];
    }

    private function getGeneralSettings(): array
    {
        $stored = json_decode((string) AppSetting::getValue('general_settings', '{}'), true);
        if (!is_array($stored)) {
            $stored = [];
        }

        return array_replace_recursive($this->defaultGeneralSettings(), $stored);
    }

    private function defaultTransactionSettings(): array
    {
        return [
            'transaction_header' => [
                'invoice_number_enabled' => true,
                'transaction_time_enabled' => false,
                'cash_sale_default' => false,
                'billing_name_enabled' => true,
                'customer_po_enabled' => false,
            ],
            'items_table' => [
                'free_item_qty_enabled' => false,
                'count_enabled' => false,
                'count_label' => 'Count',
            ],
            'more_transaction_features' => [
                'terms_conditions_enabled' => true,
                'due_dates_payment_terms_enabled' => true,
                'quick_entry' => false,
                'link_payment_to_invoices' => true,
                'passcode_enabled' => false,
                'transaction_passcode_hash' => null,
                'do_not_show_invoice_preview' => false,
            ],
            'transaction_totals' => [
                'discount_enabled' => true,
                'tax_enabled' => true,
                'round_total_enabled' => true,
                'round_total_mode' => 'down-to',
                'round_total_precision' => 100,
            ],
            'sale_prefix' => [
                'enabled' => true,
                'active' => 'INV',
                'options' => ['INV'],
            ],
            'transaction_prefixes' => [
                'sale' => ['active' => 'INV', 'options' => ['INV']],
                'credit_note' => ['active' => 'CN', 'options' => ['CN']],
                'sale_order' => ['active' => 'SO', 'options' => ['SO']],
                'purchase_order' => ['active' => 'PO', 'options' => ['PO']],
                'estimate' => ['active' => 'EST', 'options' => ['EST']],
                'proforma_invoice' => ['active' => 'PI', 'options' => ['PI']],
                'delivery_challan' => ['active' => 'DC', 'options' => ['DC']],
                'payment_in' => ['active' => 'PIN', 'options' => ['PIN']],
            ],
            'invoice_fields' => [
                'custom_field_1' => ['enabled' => false, 'label' => 'Additional Field 1'],
                'date_field_2' => ['enabled' => false, 'label' => 'Date Field 2', 'format' => 'dd/mm/yyyy'],
            ],
            'payment_terms' => [
                'enabled' => true,
                'name' => 'Net 15',
                'days' => 15,
            ],
            'transportation_details' => [
                'enabled' => false,
                'fields' => [
                    ['key' => 'field_1', 'label' => 'Transport Name', 'enabled' => false, 'show_in_print' => true],
                    ['key' => 'field_2', 'label' => 'Vehicle Number', 'enabled' => false, 'show_in_print' => true],
                    ['key' => 'field_3', 'label' => 'Delivery Date', 'enabled' => false, 'show_in_print' => true],
                    ['key' => 'field_4', 'label' => 'Delivery Location', 'enabled' => false, 'show_in_print' => true],
                    ['key' => 'field_5', 'label' => 'Field 5', 'enabled' => false, 'show_in_print' => true],
                ],
            ],
            'additional_charges' => [
                'enabled' => true,
                'items' => [
                    ['key' => 'shipping', 'enabled' => true, 'label' => 'Shipping', 'tax_rate' => 'NONE', 'tax_enabled' => false],
                    ['key' => 'packaging', 'enabled' => true, 'label' => 'Packaging', 'tax_rate' => 'NONE', 'tax_enabled' => false],
                    ['key' => 'adjustment', 'enabled' => true, 'label' => 'Adjustment', 'tax_rate' => 'NONE', 'tax_enabled' => false],
                ],
            ],
        ];
    }

    private function getTransactionSettings(): array
    {
        $stored = json_decode((string) AppSetting::getValue('sale_form_settings', '{}'), true);
        $defaults = $this->defaultTransactionSettings();
        if (!is_array($stored)) {
            return $defaults;
        }

        return array_replace_recursive($defaults, $stored);
    }

    private function defaultItemSettings(): array
    {
        return [
            'enable_item' => true,
            'sell_type' => 'both',
            'barcode_scan_enabled' => false,
            'direct_barcode_scan_enabled' => false,
            'stock_maintenance_enabled' => false,
            'manufacturing_enabled' => false,
            'show_low_stock_dialog' => false,
              'items_unit_enabled' => true,
              'default_unit_enabled' => false,
              'default_unit_label' => 'Add Default Unit',
              'default_unit_base' => '',
              'default_unit_secondary' => '',
              'default_unit_rate' => 0,
              'item_category_enabled' => false,
              'party_wise_item_rate_enabled' => false,
            'description_enabled' => false,
            'description_label' => 'Description',
            'item_wise_tax_enabled' => false,
            'item_wise_discount_enabled' => false,
            'update_sale_price_from_transaction' => false,
            'quantity_decimals' => 2,
            'wholesale_price_enabled' => false,
            'free_item_qty_enabled' => false,
            'count_enabled' => false,
            'count_label' => 'Count',
            'mrp' => [
                'enabled' => false,
                'label' => 'MRP',
                'calculate_sale_price_from_mrp' => false,
                'use_mrp_for_batch_tracking' => false,
            ],
            'serial_tracking' => [
                'enabled' => false,
                'label' => 'Serial No.',
            ],
            'batch_tracking' => [
                'batch_no' => ['enabled' => false, 'label' => 'Batch No.'],
                'exp_date' => ['enabled' => false, 'label' => 'Exp. Date', 'format' => 'mm/yy'],
                'mfg_date' => ['enabled' => false, 'label' => 'Mfg. Date', 'format' => 'mm/yy'],
                'model_no' => ['enabled' => false, 'label' => 'Model No.'],
                'size' => ['enabled' => false, 'label' => 'Size'],
            ],
            'custom_fields' => collect(range(1, 6))->map(fn ($i) => [
                'key' => 'custom_field_' . $i,
                'enabled' => false,
                'label' => 'Custom Field ' . $i,
                'show_in_print' => false,
            ])->all(),
        ];
    }

    private function getItemSettings(): array
    {
        $stored = json_decode((string) AppSetting::getValue('item_form_settings', '{}'), true);
        $defaults = $this->defaultItemSettings();

        if (!is_array($stored)) {
            return $defaults;
        }

        return array_replace_recursive($defaults, $stored);
    }


}
