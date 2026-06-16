<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PartyController;
use App\Http\Controllers\PartyGroupController;
use App\Http\Controllers\LoanAccountController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleSectionController;
use App\Http\Controllers\EstimateController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\SaleOrderController;
use App\Http\Controllers\PurchaseExpenseController;
use App\Http\Controllers\PurchaseBillController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PaymentInController;
use App\Http\Controllers\PaymentLinkController;
use App\Http\Controllers\PerfomaController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ExpenseCreateController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\CloseFinancialYearController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BrokerController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\ExportsToTallyController;
use App\Http\Controllers\SimpleInvoiceController;
use App\Models\Barcode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\ChequeController;
use App\Http\Controllers\CompanyController;

use Illuminate\Support\Facades\Route;

// Default landing page
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::middleware(['auth'])->prefix('dashboard')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Roles
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    // User management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Sales
    Route::get('/sales', [SaleController::class, 'index'])->name('sale.index');
    Route::get('/sale-report-preview', [SaleController::class, 'reportPreview'])->name('sale.report-preview');
    Route::get('/sale-report-pdf', [SaleController::class, 'reportPdf'])->name('sale.report-pdf');
    Route::post('/sale-report-email', [SaleController::class, 'reportEmail'])->name('sale.report-email');
    Route::post('/sales/verify-passcode', [SaleController::class, 'verifyTransactionPasscode'])->name('sale.verify-passcode');
    Route::get('/sale/create/{type?}', [SaleController::class, 'create'])->name('sale.create');
    Route::post('/sales', [SaleController::class, 'store'])->name('sale.store');
    Route::get('/sales/{sale}/edit', [SaleController::class, 'edit'])->name('sale.edit');
    Route::put('/sales/{sale}', [SaleController::class, 'update'])->name('sale.update');
    Route::get('/sales/{sale}/invoice-preview', [SaleController::class, 'invoicePreview'])->name('sale.invoice-preview');
    Route::get('/sales/{sale}/invoice-pdf', [SaleController::class, 'invoicePdf'])->name('sale.invoice-pdf');
    Route::get('/sales/{sale}/print', [SaleController::class, 'print'])->name('sale.print');
    Route::post('/sales/{sale}/invoice-theme', [SaleController::class, 'storeInvoiceTheme'])->name('sale.invoice-theme.store');
    Route::get('/sales/{sale}/delivery-preview', [SaleController::class, 'deliveryPreview'])->name('sale.delivery-preview');
    Route::get('/sales/{sale}/payment-history', [SaleController::class, 'paymentHistory'])->name('sale.payment-history');
    Route::get('/sales/{sale}/bank-history', [SaleController::class, 'bankHistory'])->name('sale.bank-history');
    Route::post('/sales/settings', [SaleController::class, 'updateFormSettings'])->name('sale.settings.update');
    Route::post('/sales/terms-conditions', [SaleController::class, 'storeTermsTemplate'])->name('sale.terms-conditions.store');
    Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sale.cancel');
    Route::delete('/sales/{sale}', [SaleController::class, 'destroy'])->name('sale.destroy');
    Route::get('sales/pos', [SaleController::class, 'pos1'])->name('sale.pos');



    Route::get('/estimates/{sale}/convert-to-sale', [SaleController::class, 'createFromEstimate'])->name('estimates.convert-to-sale');
    Route::get('/estimates/{sale}/edit', [SaleController::class, 'edit'])->name('estimates.edit');
    Route::delete('/estimates/{sale}', [SaleController::class, 'destroy'])->name('estimates.destroy');
    Route::get('/estimates/{sale}/preview', [SaleController::class, 'previewEstimate'])->name('estimates.preview');
    Route::get('/estimates/{sale}/print', [SaleController::class, 'printEstimate'])->name('estimates.print');
    Route::get('/estimates/{sale}/pdf', [SaleController::class, 'pdfEstimate'])->name('estimates.pdf');
    Route::get('/sale-orders/{sale}/convert-to-sale', [SaleOrderController::class, 'createFromSaleOrder'])->name('sale-orders.convert-to-sale');
    Route::post('/sale-orders/bulk-convert', [SaleController::class, 'bulkConvertSaleOrders'])->name('sale-orders.bulk-convert');
    Route::get('/delivery-challans/{sale}/convert-to-sale', [SaleController::class, 'createFromDeliveryChallan'])->name('delivery-challans.convert-to-sale');
    Route::get('/sale-orders/{sale}/preview', [SaleController::class, 'previewSaleOrder'])->name('sale-orders.preview');
    Route::get('/sale-orders/{sale}/print', [SaleController::class, 'printSaleOrder'])->name('sale-orders.print');
    Route::get('/sale-orders/{sale}/pdf', [SaleController::class, 'pdfSaleOrder'])->name('sale-orders.pdf');

    // Estimates
    Route::get('sales/estimate', [EstimateController::class, 'index'])->name('sale.estimate');
    Route::get('estimate/create', [EstimateController::class, 'create'])->name('sale.estimate.create');
    Route::get('estimates/create', [EstimateController::class, 'create'])->name('estimates.create');
    Route::post('/estimates', [EstimateController::class, 'store'])->name('estimate.store');

    // Sale Sections
    Route::get('/payment-in', [PaymentInController::class, 'index'])->name('payment-in');    // Link Payment endpoints\n    Route::post('/payments/link-data', [PaymentLinkController::class, 'linkData']);\n    Route::post('/payments/link-save', [PaymentLinkController::class, 'saveLinks']);



    Route::get('/proforma-invoice', [PerfomaController::class, 'proformaInvoice'])->name('proforma-invoice');
    Route::get('/proforma-invoice/create', [PerfomaController::class, 'createProformaInvoice'])->name('proforma-invoice.create');
    Route::post('/proforma-invoice', [PerfomaController::class, 'store'])->name('proforma-invoice.store');
    Route::get('/proforma-invoice/{sale}/edit', [PerfomaController::class, 'edit'])->name('proforma-invoice.edit');
    Route::put('/proforma-invoice/{sale}', [PerfomaController::class, 'update'])->name('proforma-invoice.update');
    Route::delete('/proforma-invoice/{sale}', [PerfomaController::class, 'destroy'])->name('proforma-invoice.destroy');
    Route::get('/proforma-invoice/{sale}/preview', [PerfomaController::class, 'preview'])->name('proforma-invoice.preview');
    Route::get('/proforma-invoice/{sale}/print', [PerfomaController::class, 'print'])->name('proforma-invoice.print');
    Route::get('/proforma-invoice/{sale}/pdf', [PerfomaController::class, 'pdf'])->name('proforma-invoice.pdf');
    Route::get('/proforma-invoice/{sale}/duplicate', [PerfomaController::class, 'duplicate'])->name('proforma-invoice.duplicate');
    Route::get('/proforma-invoice/{sale}/convert-to-sale', [SaleController::class, 'createFromProforma'])->name('proforma-invoice.convert-to-sale');
    Route::get('/proforma-invoice/{sale}/convert-to-sale-order', [SaleOrderController::class, 'createFromProforma'])->name('proforma-invoice.convert-to-sale-order');
    Route::get('/proforma-invoice/{sale}/react-preview', [InvoiceController::class, 'proforma'])->name('proforma-invoice.react');
    Route::post('/proforma-invoice/{sale}/email', [InvoiceController::class, 'emailProforma'])->name('proforma-invoice.email');
    //emailsend
    Route::post('/sales/{sale}/invoice-email', [InvoiceController::class, 'emailDocument'])->name('sale.invoice-email');
    // Sale Return
    Route::get('/sale-return', [SaleReturnController::class, 'saleReturn'])->name('sale-return');
    Route::get('/sale-return/create', [SaleReturnController::class, 'salereturncreate'])->name('sale-return.create');
    Route::post('/sale-return', [SaleReturnController::class, 'store'])->name('sale-return.store');
    Route::get('/sale-return/{sale}/edit', [SaleReturnController::class, 'edit'])->name('sale-return.edit');
    Route::put('/sale-return/{sale}', [SaleReturnController::class, 'update'])->name('sale-return.update');
    Route::delete('/sale-return/{sale}', [SaleReturnController::class, 'destroy'])->name('sale-return.destroy');
    Route::get('/sale-return/{sale}/preview', [SaleReturnController::class, 'preview'])->name('sale-return.preview');
    Route::get('/sale-return/{sale}/print', [SaleReturnController::class, 'print'])->name('sale-return.print');
    Route::get('/sale-return/{sale}/pdf', [SaleReturnController::class, 'pdf'])->name('sale-return.pdf');
    Route::get('/sale-return/{sale}/bank-history', [SaleReturnController::class, 'bankHistory'])->name('sale-return.bank-history');
    Route::get('/sale-return/{sale}/duplicate', [SaleReturnController::class, 'duplicate'])->name('sale-return.duplicate');
    Route::get('/sale-return/next-number', [SaleReturnController::class, 'nextInvoiceNumber'])->name('sale-return.next-number');
    Route::get('/sale/next-number', [SaleController::class, 'getNextNumber'])->name('sale.next-number');

    // Delivery Challan
    Route::get('delivery-challan', [DeliveryController::class, 'deliveryChallan'])->name('delivery-challan');

    Route::get('create-challan', [DeliveryController::class, 'createChallan'])->name('create-challan');
    Route::post('delivery-challan', [DeliveryController::class, 'store'])->name('delivery-challan.store');
    Route::get('delivery-challan/{sale}/edit', [DeliveryController::class, 'edit'])->name('delivery-challan.edit');
    Route::put('delivery-challan/{sale}', [DeliveryController::class, 'update'])->name('delivery-challan.update');
    Route::delete('delivery-challan/{sale}', [DeliveryController::class, 'destroy'])->name('delivery-challan.destroy');
    Route::get('delivery-challan/{sale}/preview', [DeliveryController::class, 'preview'])->name('delivery-challan.preview');
    Route::get('delivery-challan/{sale}/print', [DeliveryController::class, 'print'])->name('delivery-challan.print');
    Route::get('delivery-challan/{sale}/pdf', [DeliveryController::class, 'pdf'])->name('delivery-challan.pdf');
    Route::get('delivery-challan/{sale}/duplicate', [DeliveryController::class, 'duplicate'])->name('delivery-challan.duplicate');

    Route::get('delivery-challan/next-number', [DeliveryController::class, 'getNextNumber'])->name('delivery-challan.next-number');

    // Sale Orders
    Route::get('sale-order', [SaleOrderController::class, 'saleOrder'])->name('sale-order');
    Route::get('sale-order/create', [SaleOrderController::class, 'create'])->name('sale-order.create');
    Route::post('sale-order', [SaleOrderController::class, 'store'])->name('sale-order.store');
    Route::put('sale-order/{sale}', [SaleOrderController::class, 'update'])->name('sale-order.update');
    Route::get('sale-order/{sale}/edit', [SaleOrderController::class, 'edit'])->name('sale-order.edit');
    Route::get('estimates/{sale}/convert-to-sale-order', [SaleOrderController::class, 'createFromEstimate'])->name('estimates.convert-to-sale-order');

    // Invoice
    Route::get('/invoice', [InvoiceController::class, 'index'])->name('invoice');
    Route::get('/invoice/modal-preview', [InvoiceController::class, 'modalPreview'])->name('invoice.modal-preview');
    Route::get('/invoice/download-pdf', [InvoiceController::class, 'downloadPdf'])->name('invoice.download-pdf');
    Route::get('/invoice/print', [InvoiceController::class, 'print'])->name('invoice.print');
    Route::get('/invoice/payment-in', [InvoiceController::class, 'paymentIn'])->name('invoice.payment-in');
    Route::get('/invoice/react-assets/{source}/{file}', [InvoiceController::class, 'reactAsset'])
        ->where('source', 'public|nested_public|root|public_parent|base_parent|dist')
        ->where('file', '.*')
        ->name('invoice.react-asset');
    Route::get('/market-invoices/create', [SimpleInvoiceController::class, 'create'])->name('market-invoices.create');
    Route::post('/market-invoices', [SimpleInvoiceController::class, 'store'])->name('market-invoices.store');
    Route::get('/market-invoices/{marketInvoice}', [SimpleInvoiceController::class, 'show'])->name('market-invoices.show');

    // Loan Accounts
    Route::get('/loan-accounts', [LoanAccountController::class, 'index'])->name('loan-accounts');
    Route::post('/loan-accounts', [LoanAccountController::class, 'store'])->name('loan-accounts.store');
    Route::get('/loan-accounts/{loanAccount}', [LoanAccountController::class, 'show'])->name('loan-accounts.show');
    Route::get('/loan-accounts/{loanAccount}/edit', [LoanAccountController::class, 'edit'])->name('loan-accounts.edit');
    Route::put('/loan-accounts/{loanAccount}', [LoanAccountController::class, 'update'])->name('loan-accounts.update');
    Route::delete('/loan-accounts/{loanAccount}', [LoanAccountController::class, 'destroy'])->name('loan-accounts.destroy');
    Route::post('/loan-accounts/{loanAccount}/transactions', [LoanAccountController::class, 'storeTransaction'])->name('loan-accounts.transactions.store');
    Route::put('/loan-accounts/{loanAccount}/transactions/{transaction}', [LoanAccountController::class, 'updateTransaction'])->name('loan-accounts.transactions.update');

    // Bank Accounts
    Route::get('/bank-accounts', [BankAccountController::class, 'index'])->name('bank-accounts');
    Route::post('/bank-accounts', [BankAccountController::class, 'store'])->name('bank-accounts.store');
    Route::post('/bank-accounts/transfer', [BankAccountController::class, 'transfer'])->name('bank-accounts.transfer');
    Route::get('cash-in-hand', [BankAccountController::class, 'cashInHand'])->name('cash-in-hand');
    Route::post('cash-in-hand/adjust', [BankAccountController::class, 'adjustCash'])->name('cash-in-hand.adjust');
    Route::get('/bank-accounts/{bankAccount}', [BankAccountController::class, 'show'])->name('bank-accounts.show');
    Route::get('/bank-accounts/{bankAccount}/edit', [BankAccountController::class, 'edit'])->name('bank-accounts.edit');
    Route::put('/bank-accounts/{bankAccount}', [BankAccountController::class, 'update'])->name('bank-accounts.update');
    Route::delete('/bank-accounts/{bankAccount}', [BankAccountController::class, 'destroy'])->name('bank-accounts.destroy');
    // ─── Cheques ───────────────────────────────────────────────
    Route::get('/cheques',                    [ChequeController::class, 'index'])->name('cheques.index');
    Route::post('/cheques',                   [ChequeController::class, 'store'])->name('cheques.store');
    Route::get('/cheques/{cheque}',           [ChequeController::class, 'show'])->name('cheques.show');
    Route::put('/cheques/{cheque}',           [ChequeController::class, 'update'])->name('cheques.update');
    Route::patch('/cheques/{cheque}',         [ChequeController::class, 'update']);       // alias
    Route::delete('/cheques/{cheque}',        [ChequeController::class, 'destroy'])->name('cheques.destroy');
    Route::post('/cheques/{cheque}/deposit',  [ChequeController::class, 'deposit'])->name('cheques.deposit');
    Route::post('/cheques/{cheque}/status',   [ChequeController::class, 'updateStatus'])->name('cheques.status');
    Route::get('/cheques/{cheque}/history',   [ChequeController::class, 'history'])->name('cheques.history');


    // Purchase & Expenses



    Route::get('/purchase-bill', [PurchaseBillController::class, 'purchaseExpenses'])->name('purchase-expenses');
    Route::get('/purchase-bill/create', [PurchaseBillController::class, 'create'])->name('purchase-bill.create');
    Route::post('/purchase-bills', [PurchaseBillController::class, 'store'])->name('purchase-bills.store');
    Route::get('/purchase-bills/{purchase}/edit', [PurchaseBillController::class, 'edit'])->name('purchase-bills.edit');
    Route::put('/purchase-bills/{purchase}', [PurchaseBillController::class, 'update'])->name('purchase-bills.update');
    Route::delete('/purchase-bills/{purchase}', [PurchaseBillController::class, 'destroy'])->name('purchase-bills.destroy');
    Route::get('/purchase-bills/{purchase}/preview', [PurchaseBillController::class, 'preview'])->name('purchase-bills.preview');
    Route::get('/purchase-bills/{purchase}/print', [PurchaseBillController::class, 'print'])->name('purchase-bills.print');
    Route::get('/purchase-bills/{purchase}/pdf', [PurchaseBillController::class, 'pdf'])->name('purchase-bills.pdf');
    Route::get('/purchase-bills/{purchase}/download-pdf', [PurchaseBillController::class, 'downloadPdf'])->name('purchase-bills.download-pdf');



    Route::get('purchase-order', [PurchaseOrderController::class, 'purchaseOrder'])->name('purchase-order');
    Route::get('purchase-order/create', [PurchaseOrderController::class, 'create'])->name('purchase-order.create');
    Route::get('purchase-orders/{purchase}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
    Route::post('purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
    Route::get('purchase-orders/{purchase}/edit', [PurchaseOrderController::class, 'edit'])->name('purchase-orders.edit');
    Route::get('purchase-orders/{purchase}/preview', [PurchaseOrderController::class, 'preview'])->name('purchase-orders.preview');
    Route::get('purchase-orders/{purchase}/print', [PurchaseOrderController::class, 'print'])->name('purchase-orders.print');
    Route::get('purchase-orders/{purchase}/pdf', [PurchaseOrderController::class, 'pdf'])->name('purchase-orders.pdf');
    Route::get('purchase-orders/{purchase}/history', [PurchaseOrderController::class, 'history'])->name('purchase-orders.history');
    Route::put('purchase-orders/{purchase}', [PurchaseOrderController::class, 'update'])->name('purchase-orders.update');
    Route::delete('purchase-orders/{purchase}', [PurchaseOrderController::class, 'destroy'])->name('purchase-orders.destroy');


    Route::get('settings/general', [SettingController::class, 'general'])->name('settings.general');
    Route::post('settings/general', [SettingController::class, 'updateGeneral'])->name('settings.general.update');
    Route::get('settings/general/sidebar-config', [SettingController::class, 'generalSidebarConfig'])->name('settings.general.sidebar-config');
    Route::get('settings/transactions', [SettingController::class, 'transactions'])->name('settings.transactions');
    Route::post('settings/transactions', [SettingController::class, 'updateTransactions'])->name('settings.transactions.update');
    Route::get('settings/taxes', [SettingController::class, 'taxes'])->name('settings.taxes');
    // Persist tax rates and groups
    Route::post('settings/taxes/rates', [App\Http\Controllers\Settings\TaxController::class, 'storeRate'])->name('settings.taxes.rates.store');
    Route::put('settings/taxes/rates/{id}', [App\Http\Controllers\Settings\TaxController::class, 'updateRate'])->name('settings.taxes.rates.update');
    Route::delete('settings/taxes/rates/{id}', [App\Http\Controllers\Settings\TaxController::class, 'destroyRate'])->name('settings.taxes.rates.destroy');
    Route::post('settings/taxes/groups', [App\Http\Controllers\Settings\TaxController::class, 'storeGroup'])->name('settings.taxes.groups.store');
    Route::put('settings/taxes/groups/{id}', [App\Http\Controllers\Settings\TaxController::class, 'updateGroup'])->name('settings.taxes.groups.update');
    Route::delete('settings/taxes/groups/{id}', [App\Http\Controllers\Settings\TaxController::class, 'destroyGroup'])->name('settings.taxes.groups.destroy');
    Route::get('settings/items', [SettingController::class, 'items'])->name('settings.items');
    Route::post('settings/items', [SettingController::class, 'updateItems'])->name('settings.items.update');
    Route::get('settings/parties', [SettingController::class, 'parties'])->name('settings.parties');
    Route::get('settings/party-reminders', [SettingController::class, 'partyReminders'])->name('settings.party-reminders');
    Route::get('settings/transaction-messages', [SettingController::class, 'transactionMessages'])->name('settings.transaction-messages');
    Route::get('settings/print-layout', [SettingController::class, 'printLayout'])->name('settings.print-layout');

    // Utilities
    Route::get('/utilities/import-items', function () {
        return view('dashboard.utilities.import-items');
    })->name('utilities.import-items');
    Route::post('/utilities/import-items/valid-items', [ItemController::class, 'importValidItems'])->name('utilities.import-items.valid-items');
    Route::get('/utilities/barcode-generator', function () {
        $barcodes = Barcode::latest()->take(20)->get();
        return view('dashboard.utilities.barcode-generator', compact('barcodes'));
    })->name('utilities.barcode-generator');
    Route::post('/utilities/barcode-generator', function (Request $request) {
        $data = $request->validate([
            'item_id' => 'nullable|exists:items,id',
            'item_name' => 'required|string|max:255',
            'item_code' => 'required|string|max:255',
            'sale_price' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'header' => 'nullable|string|max:255',
            'line_1' => 'nullable|string|max:255',
            'line_2' => 'nullable|string|max:255',
            'line_3' => 'nullable|string|max:255',
            'line_4' => 'nullable|string|max:255',
            'labels' => 'nullable|integer|min:1',
            'barcode_value' => 'nullable|string|max:255',
        ]);

        $data['barcode_value'] = $data['barcode_value'] ?: $data['item_code'] ?: Str::upper(Str::random(12));
        $data['user_id'] = auth()->id();
        $data['labels'] = $data['labels'] ?? 1;
        $data['sale_price'] = $data['sale_price'] ?? 0;
        $data['discount'] = $data['discount'] ?? 0;

        Barcode::create($data);

        return redirect()->route('utilities.barcode-generator')->with('success', 'Barcode entry saved successfully.');
    })->name('utilities.barcode-generator.store');
    Route::get('/utilities/update-items-in-bulk', function () {
        return view('dashboard.utilities.update-items-in-bulk');
    })->name('utilities.update-items-in-bulk');
    Route::get('/utilities/update-items-in-bulk/data', [ItemController::class, 'bulkUpdateData'])->name('utilities.update-items-in-bulk.data');
    Route::get('/utilities/import-parties', function () {
        return view('dashboard.utilities.import-parties');
    })->name('utilities.import-parties');
    Route::get('/utilities/import-parties/sample', [PartyController::class, 'downloadImportTemplate'])->name('utilities.import-parties.sample');
    Route::post('/utilities/import-parties/preview', [PartyController::class, 'previewImport'])->name('utilities.import-parties.preview');
    Route::post('/utilities/import-parties/valid-parties', [PartyController::class, 'importValidParties'])->name('utilities.import-parties.valid-parties');
    Route::get('/utilities/exports-to-tally', [ExportsToTallyController::class, 'index'])->name('utilities.exports-to-tally');
    Route::get('/utilities/exports-to-tally/data', [ExportsToTallyController::class, 'data'])->name('utilities.exports-to-tally.data');
    Route::get('/utilities/exports-to-tally/download', [ExportsToTallyController::class, 'download'])->name('utilities.exports-to-tally.download');
    Route::post('/utilities/exports-to-tally/push', [ExportsToTallyController::class, 'push'])->name('utilities.exports-to-tally.push');
    Route::get('/utilities/export-items/data', [ItemController::class, 'exportItemsData'])->name('utilities.export-items.data');
    Route::get('/utilities/export-items/download', [ItemController::class, 'exportItemsDownload'])->name('utilities.export-items.download');
    Route::get('/utilities/export-items', function () {
        return view('dashboard.utilities.export-items');
    })->name('utilities.export-items');
    Route::get('/utilities/verify-my-data', function () {
        return view('dashboard.utilities.verify-my-data');
    })->name('utilities.verify-my-data');
    Route::get('/utilities/close-financial-year', [CloseFinancialYearController::class, 'index'])->name('utilities.close-financial-year');
    Route::post('/utilities/close-financial-year/prefixes', [CloseFinancialYearController::class, 'updatePrefixes'])->name('utilities.close-financial-year.prefixes');
    Route::post('/utilities/close-financial-year/backup', [CloseFinancialYearController::class, 'backupAndStartFresh'])->name('utilities.close-financial-year.backup');


    Route::get('/payment-out', [PurchaseExpenseController::class, 'paymentOut'])->name('payment-out');
    Route::get('/payment-out/linkable-purchases/{party}', [PurchaseExpenseController::class, 'linkablePurchases'])->name('payment-out.linkable-purchases');
    Route::get('/expense/linkable-transactions/{party}', [PaymentLinkController::class, 'expenseLinkData'])->name('expense.linkable-transactions');
    Route::post('/payment-out', [PurchaseExpenseController::class, 'storePaymentOut'])->name('payment-out.store');
    Route::get('purchase-return', [PurchaseReturnController::class, 'index'])->name('purchase-return');
    Route::get('purchase-return/create', [PurchaseReturnController::class, 'create'])->name('purchase-return.create');
    Route::post('/purchase-return', [PurchaseReturnController::class, 'store'])->name('purchase-return.store');
    Route::get('/purchase-return/{purchase}/edit', [PurchaseReturnController::class, 'edit'])->name('purchase-return.edit');
    Route::put('/purchase-return/{purchase}', [PurchaseReturnController::class, 'update'])->name('purchase-return.update');
    Route::delete('/purchase-return/{purchase}', [PurchaseReturnController::class, 'destroy'])->name('purchase-return.destroy');
    Route::get('/purchase-return/{purchase}/preview', [PurchaseReturnController::class, 'preview'])->name('purchase-return.preview');
    Route::get('/purchase-return/{purchase}/print', [PurchaseReturnController::class, 'print'])->name('purchase-return.print');
    Route::get('/purchase-return/{purchase}/pdf', [PurchaseReturnController::class, 'pdf'])->name('purchase-return.pdf');
    Route::post('/purchase-return/{purchase}/invoice-theme', [PurchaseReturnController::class, 'storeInvoiceTheme'])->name('purchase-return.invoice-theme.store');
    Route::get('/purchase-return/{purchase}/duplicate', [PurchaseReturnController::class, 'duplicate'])->name('purchase-return.duplicate');




    Route::get('reports', [ReportController::class, 'index'])->name('reports');
    Route::get('reports', [ReportController::class, 'index'])->name('reports');
    Route::get('/reports/sale', [ReportController::class, 'saleReport'])->name('reports.sale');
    Route::get('reports/all-transactions', [ReportController::class, 'allTransactions'])->name('reports.all-transactions');
    Route::get('/reports/purchase', [ReportController::class, 'purchaseReport'])->name('reports.purchase');

    // Cash Flow
    Route::get('/reports/cash-flow', [ReportController::class, 'cashFlow']);
    Route::get('/reports/cash-flow/export', [ReportController::class, 'cashFlowExport']);

    Route::get('reports/item-wise-discount', [ReportController::class, 'itemWiseDiscount'])->name('reports.item-wise-discount');
    Route::get('reports/item-detail', [ReportController::class, 'itemDetail'])->name('reports.item-detail');
    Route::get('reports/item-report-by-party', [ReportController::class, 'itemReportByParty'])->name('reports.item-report-by-party');
    Route::get('reports/party-statement', [ReportController::class, 'partyStatement']);
    Route::get('reports/party-statement/{partyId}', [ReportController::class, 'partyStatement']);
    Route::get('reports/all-parties', [ReportController::class, 'allParties']);
    Route::get('reports/party-report-by-items', [ReportController::class, 'partyReportByItems']);
    Route::get('reports/sale-purchase-by-party', [ReportController::class, 'salePurchaseByParty']);
    Route::get('reports/sale-purchase-by-party-group', [ReportController::class, 'salePurchaseByPartyGroup']);
    Route::get('/reports/profit-loss', [ReportController::class, 'profitAndLoss']);
    Route::get('/reports/profit-loss/export', [ReportController::class, 'profitAndLossExport']);
    Route::get('/reports/balance-sheet', [ReportController::class, 'balanceSheet']);
    Route::get('/reports/balance-sheet/export', [ReportController::class, 'balanceSheetExport']);
    Route::get('/reports/bill-wise-profit', [ReportController::class, 'billWiseProfit']);
    Route::get('/reports/bill-wise-profit/export', [ReportController::class, 'billWiseProfitExport']);
    Route::get('/reports/bill-wise-profit/{id}/items', [ReportController::class, 'billWiseProfitItems']);
    Route::get('/reports/bank-statement', [ReportController::class, 'bankStatement']);
    Route::get('/reports/bank-statement/export', [ReportController::class, 'bankStatementExport']);
    Route::get('/reports/discount-report', [ReportController::class, 'discountReport']);
    Route::get('/reports/discount-report/export', [ReportController::class, 'discountReportExport']);
    Route::get('/reports/tax-report', [ReportController::class, 'taxReport']);
    Route::get('/reports/tax-report/export', [ReportController::class, 'taxReportExport']);
    Route::get('/reports/tax-rate-report', [ReportController::class, 'taxRateReport']);
    Route::get('/reports/tax-rate-report/export', [ReportController::class, 'taxRateReportExport']);
    Route::get('/reports/expense', [ReportController::class, 'expenseReport']);
    Route::get('/reports/expense/export', [ReportController::class, 'expenseReportExport']);
    Route::get('/reports/expense-category-report', [ReportController::class, 'expenseCategoryReport']);
    Route::get('/reports/expense-category-report/export', [ReportController::class, 'expenseCategoryReportExport']);
    Route::get('/reports/expense-item-report', [ReportController::class, 'expenseItemReport']);
    Route::get('/reports/expense-item-report/export', [ReportController::class, 'expenseItemReportExport']);
    Route::get('/reports/sale-order', [ReportController::class, 'saleOrder'])->name('reports.sale-order');
    Route::get('/reports/sale-order-items', [ReportController::class, 'saleOrderItems'])->name('reports.sale-order-items');
    Route::get('/reports/unreceived-invoices/pdf', [ReportController::class, 'unreceivedInvoicePdf'])->name('reports.unreceived-invoices.pdf');
    Route::get('/reports/daybook', [ReportController::class, 'dayBook'])->name('reports.daybook');

    // Loan Statement JSON
    Route::get('/loan-accounts-json', [LoanAccountController::class, 'allJson'])->name('loan-accounts.json');
    Route::get('reports/item-wise-discount', [ReportController::class, 'itemWiseDiscount'])
        ->name('reports.item-wise-discount');
    Route::get('reports/item-report-by-party', [ReportController::class, 'itemReportByParty'])
        ->name('reports.item-report-by-party');
    Route::get('reports/party-statement',             [ReportController::class, 'partyStatement']);
    Route::get('reports/party-statement/{partyId}',   [ReportController::class, 'partyStatement']);
    Route::get('reports/all-parties',                  [ReportController::class, 'allParties']);
    Route::get('reports/party-report-by-items',        [ReportController::class, 'partyReportByItems']);
    Route::get('reports/sale-purchase-by-party',       [ReportController::class, 'salePurchaseByParty']);
    Route::get('reports/sale-purchase-by-party-group', [ReportController::class, 'salePurchaseByPartyGroup']);
    // Company management — inside the dashboard middleware group
    Route::get('/company',                       [CompanyController::class, 'index'])->name('company.index');
    Route::post('/company',                      [CompanyController::class, 'store'])->name('company.store');
    Route::post('/company/{company}/switch',     [CompanyController::class, 'switchCompany'])->name('company.switch');
    Route::put('/company/{company}/rename',      [CompanyController::class, 'rename'])->name('company.rename');
    Route::post('/company/{company}/rename',     [CompanyController::class, 'rename']);
    Route::delete('/company/{company}',          [CompanyController::class, 'destroy'])->name('company.destroy');
    Route::get('/company/current',               [CompanyController::class, 'currentCompany'])->name('company.current');
    Route::put('/company/{company}/rename', [CompanyController::class, 'rename'])->name('company.rename');

    // ═══════════════════════════════════════
    // ADD THESE ROUTES inside the auth middleware group in web.php
    // Replace the two existing expense routes:
    //   Route::get('expense', [ExpenseCreateController::class, 'expense'])->name('expense');
    //   Route::get('expense/create', [ExpenseCreateController::class, 'createExpense'])->name('expense.create');
    // WITH THESE:
    // ═══════════════════════════════════════

    Route::get('expense', [ExpenseCreateController::class, 'expense'])->name('expense');
    Route::get('expense/create', [ExpenseCreateController::class, 'createExpense'])->name('expense.create');

    // Expense Categories
    Route::post('expense/categories', [ExpenseCreateController::class, 'storeCategory'])->name('expense.categories.store');
    Route::delete('expense/categories/{id}', [ExpenseCreateController::class, 'destroyCategory'])->name('expense.categories.destroy');
    Route::put('expense/categories/{id}', [ExpenseCreateController::class, 'updateCategory'])->name('expense.categories.update');

    // Expense Items
    Route::post('expense/items', [ExpenseCreateController::class, 'storeItem'])->name('expense.items.store');
    Route::put('expense/items/{id}', [ExpenseCreateController::class, 'updateItem'])->name('expense.items.update');
    Route::delete('expense/items/{id}', [ExpenseCreateController::class, 'destroyItem'])->name('expense.items.destroy');

    // Expenses
    Route::post('expense/save', [ExpenseCreateController::class, 'storeExpense'])->name('expense.save');
    Route::delete('expense/{id}', [ExpenseCreateController::class, 'destroyExpense'])->name('expense.destroy');






    // Items — static routes BEFORE wildcard {id} routes
    Route::get('/items', [ItemController::class, 'index'])->name('items');
    Route::get('/items/services', [ItemController::class, 'services'])->name('items.services');
    Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');




    Route::get('/items/category', [ItemController::class, 'category'])->name('items.category');
    Route::get('/items/category/list', [ItemController::class, 'categoryList'])->name('items.category.list');
    Route::post('/items/category', [ItemController::class, 'storeCategory'])->name('items.category.store');
    Route::put('/items/category/{id}', [ItemController::class, 'updateCategory'])->name('items.category.update');
    Route::delete('/items/category/{id}', [ItemController::class, 'destroyCategory'])->name('items.category.destroy');

    Route::get('/items/units', [ItemController::class, 'units'])->name('items.units');
    Route::post('/items/units', [ItemController::class, 'storeUnit'])->name('items.units.store');
    Route::put('/items/units/{id}', [ItemController::class, 'updateUnit'])->name('items.units.update');
    Route::delete('/items/units/{id}', [ItemController::class, 'destroyUnit'])->name('items.units.destroy');

    Route::post('/items/{id}/adjust', [ItemController::class, 'adjust'])->name('items.adjust');
    Route::post('/items/bulk-status', [ItemController::class, 'bulkStatus'])->name('items.bulk-status');
    Route::get('/items/{id}/transactions', [ItemController::class, 'transactions'])->name('items.transactions');
    Route::get('/items/{id}', [ItemController::class, 'show'])->name('items.show');
    Route::get('/items/{id}/edit', [ItemController::class, 'edit'])->name('items.edit');
    Route::put('/items/{id}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{id}', [ItemController::class, 'destroy'])->name('items.destroy');

    // Parties
    Route::get('/parties', [PartyController::class, 'index'])->name('parties');
    Route::post('/parties', [PartyController::class, 'store'])->name('parties.store');
    Route::post('/parties/settings/update', [PartyController::class, 'updateSettings'])->name('parties.settings.update');
    Route::get('/parties/status/list', [PartyController::class, 'statusList'])->name('parties.status.list');
    Route::post('/parties/status/update', [PartyController::class, 'updateStatuses'])->name('parties.status.update');
    Route::post('/party-groups', [PartyGroupController::class, 'store'])->name('party-groups.store');
    Route::put('/party-groups/{partyGroup}', [PartyGroupController::class, 'update'])->name('party-groups.update');
    Route::delete('/party-groups/{partyGroup}', [PartyGroupController::class, 'destroy'])->name('party-groups.destroy');
    Route::post('/parties/groups/move', [PartyController::class, 'moveGroups'])->name('parties.groups.move');
    Route::get('/parties/{party}', [PartyController::class, 'show'])->name('parties.show');
    Route::put('/parties/{party}', [PartyController::class, 'update'])->name('parties.update');
    Route::delete('/parties/{id}', [PartyController::class, 'destroy'])->name('parties.destroy');
    Route::get('parties/{party}/transactions', [PartyController::class, 'transactions'])->name('parties.transactions');
    Route::get('parties/{party}/ledger', [PartyController::class, 'ledger'])->name('parties.ledger');
    Route::get('parties/{party}/statement-pdf', [PartyController::class, 'statementPdf'])->name('parties.statement-pdf');
    Route::get('parties/{party}/transfer-history', [PartyController::class, 'transferHistory'])->name('parties.transfer-history');
    Route::post('parties/transfer', [PartyController::class, 'storeTransfer'])->name('parties.transfer.store');
    Route::get('/parties/create', [PartyController::class, 'create'])->name('parties.create');
    // Brokers
    Route::get('/brokers', [BrokerController::class, 'index'])->name('brokers.index');
    Route::get('/brokers/{broker}/history', [BrokerController::class, 'history'])->name('brokers.history');
    Route::post('/brokers', [BrokerController::class, 'store'])->name('brokers.store');
    Route::put('/brokers/{broker}', [BrokerController::class, 'update'])->name('brokers.update');
    Route::delete('/brokers/{broker}', [BrokerController::class, 'destroy'])->name('brokers.destroy');
    Route::post('/warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
    Route::post('/bank-accounts/bulk-status', [BankAccountController::class, 'bulkStatus'])->name('bank-accounts.bulk-status');
    // payment-in
Route::post('/payments-in', [PaymentInController::class, 'store'])->name('payments-in.store');
Route::get('/payments-in/{paymentIn}/edit', [PaymentInController::class, 'edit'])->name('payments-in.edit');
Route::get('/payments-in/{paymentIn}/duplicate', [PaymentInController::class, 'duplicate'])->name('payments-in.duplicate');
Route::put('/payments-in/{paymentIn}', [PaymentInController::class, 'update'])->name('payments-in.update');
Route::delete('/payments-in/{paymentIn}', [PaymentInController::class, 'destroy'])->name('payments-in.destroy');
Route::get('/payments-in/{paymentIn}/print', [PaymentInController::class, 'print'])->name('payments-in.print');
Route::get('/payments-in/{paymentIn}/pdf', [PaymentInController::class, 'pdf'])->name('payments-in.pdf');
Route::get('/payments-in/{paymentIn}/history', [PaymentInController::class, 'getHistory'])->name('payments-in.history');
Route::get('/payments-in/linkable-sales/{party}', [PaymentInController::class, 'linkableSales'])->name('payments-in.linkable-sales');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Debug pages
    Route::get('/debug/admin', function () {
        return view('debug.admin_test');
    })->name('debug.admin');

    Route::get('/debug/user', function () {
        return view('debug.user_test');
    })->name('debug.user');

    // Sidebar test pages
    Route::get('/test/admin-sidebar', function () {
        return view('dashboard.test_admin_sidebar');
    })->name('test.admin_sidebar');

    Route::get('/test/user-sidebar', function () {
        return view('dashboard.test_user_sidebar');
    })->name('test.user_sidebar');

    Route::get('/theme', function () {
        return view('themes.index');
    })->name('theme');
});





Route::post('/items/bulk-update', [ItemController::class, 'bulkUpdate'])->name('items.bulk-update');

require __DIR__ . '/auth.php';
