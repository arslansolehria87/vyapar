<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppSetting;
use App\Models\BankAccount;
use App\Models\ExpenseCategory;
use App\Models\ExpenseItem;
use App\Models\Expense;
use App\Models\Item;
use App\Models\Party;
use App\Models\PartyGroup;
use App\Models\Transaction;
use App\Models\TaxRate;
use App\Models\BankTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class ExpenseCreateController extends Controller
{
    // ─── MAIN VIEW ───────────────────────────────────────────────
    public function createExpense(Request $request)
    {
        return $this->expense($request, true);
    }

    public function expense(Request $request, bool $startInCreate = false)
    {
        $userId = Auth::id();
        $settings = json_decode((string) AppSetting::getValue('sale_form_settings', '{}'), true) ?: [];

        $categoriesRaw = ExpenseCategory::where('user_id', $userId)
            ->with(['expenses' => function($q) {
                $q->orderBy('expense_date', 'desc');
            }])
            ->orderBy('name')
            ->get();

        $categories = $categoriesRaw->map(function($cat) {
            return [
                'id'      => $cat->id,
                'name'    => $cat->name,
                'type'    => $cat->type,
                'amount'  => $cat->expenses->sum('total_amount'),
                'entries' => $cat->expenses->map(function($e) {
                    $items = $e->items_json;
                    return [
                        'id'          => $e->id,
                        'date'        => optional($e->expense_date)->format('Y-m-d') ?: '',
                        'expNo'       => $e->expense_no ?? '',
                        'reference_no'=> $e->reference_no ?? '',
                        'party_id'    => $e->party_id,
                        'party'       => $e->party ?? '',
                        'poNo'        => $e->po_no ?? '',
                        'poDate'      => optional($e->po_date)->format('Y-m-d') ?: '',
                        'transactionTime' => $e->transaction_time ?? '',
                        'dealDays'    => $e->deal_days ?? 0,
                        'dueDate'     => optional($e->due_date)->format('Y-m-d') ?: '',
                        'paymentTermsName' => $e->payment_terms_name ?? '',
                        'status'      => $e->status ?? (($e->balance ?? 0) <= 0 ? 'paid' : (((float) ($e->total_amount ?? 0) - (float) ($e->balance ?? 0)) > 0 ? 'partial' : 'unpaid')),
                        'taxEnabled'  => (bool) ($e->tax_enabled ?? false),
                        'taxRateId'   => $e->tax_rate_id,
                        'taxRateName' => $e->tax_rate_name ?? '',
                        'taxRateValue'=> $e->tax_rate_value ?? 0,
                        'taxAmount'   => $e->tax_amount ?? 0,
                        'discountPercent' => $e->discount_percent ?? 0,
                        'discountAmount' => $e->discount_amount ?? 0,
                        'summaryTaxRateId' => $e->tax_rate_id,
                        'summaryTaxAmount' => $e->tax_amount ?? 0,
                        'items'       => is_array($items) ? $items : [],
                        'additionalCharges' => $e->additional_charges ?? [],
                        'transportationDetails' => $e->transportation_details ?? [],
                        'description' => $e->description ?? '',
                        'attachments' => $e->attachments ?? [],
                        'bankAccountId' => $e->bank_account_id,
                        'paymentType' => $e->payment_type,
                        'amount'      => $e->total_amount,
                        'paidAmount'  => max((float) ($e->total_amount ?? 0) - (float) ($e->balance ?? 0), 0),
                        'balance'     => $e->balance,
                        'linkedRows'  => $this->getExpenseLinkedRows($e->id),
                    ];
                })->values(),
            ];
            })->values();

        ExpenseItem::query()
            ->whereNull('expense_id')
            ->orderBy('name')
            ->get()
            ->unique(function ($it) {
                return strtolower(trim((string) ($it->name ?? '')));
            })
            ->each(function (ExpenseItem $legacyItem) {
                $normalizedName = strtolower(trim((string) $legacyItem->name));
                if ($normalizedName === '') {
                    return;
                }

                $exists = Item::query()
                    ->whereRaw('LOWER(TRIM(name)) = ?', [$normalizedName])
                    ->exists();

                if ($exists) {
                    return;
                }

                $payload = [
                    'type' => 'product',
                    'name' => trim((string) $legacyItem->name),
                    'price' => (float) ($legacyItem->price ?? 0),
                ];
                if (Schema::hasColumn('items', 'sale_price')) {
                    $payload['sale_price'] = (float) ($legacyItem->price ?? 0);
                }
                if (Schema::hasColumn('items', 'purchase_price')) {
                    $payload['purchase_price'] = (float) ($legacyItem->price ?? 0);
                }
                if (Schema::hasColumn('items', 'wholesale_price')) {
                    $payload['wholesale_price'] = (float) ($legacyItem->price ?? 0);
                }

                Item::create($payload);
            });

        $expenseItems = Item::query()
            ->where(function ($q) {
                $q->where('type', 'product')->orWhereNull('type');
            })
            ->orderBy('name')
            ->get()
            ->map(fn ($it) => ['id' => $it->id, 'name' => $it->name, 'price' => $it->price])
            ->values();

        $parties = Party::orderBy('name')
            ->get()
            ->map(fn($party) => [
                'id' => $party->id,
                'name' => $party->name,
                'phone' => $party->phone,
                'phone_number_2' => $party->phone_number_2,
                'ptcl_number' => $party->ptcl_number,
                'email' => $party->email,
                'city' => $party->city,
                'party_group' => $party->party_group,
                'address' => $party->address,
                'billing_address' => $party->billing_address,
                'shipping_address' => $party->shipping_address,
                'opening_balance' => $party->opening_balance,
                'current_balance' => $party->current_balance,
                'transaction_type' => $party->transaction_type,
            ])
            ->values();

        $partyGroups = PartyGroup::orderBy('name')
            ->get(['id', 'name'])
            ->values();

        $bankAccounts = BankAccount::active()
            ->orderBy('display_name')
            ->get()
            ->map(fn($bank) => [
                'id' => $bank->id,
                'display_name' => $bank->display_name,
                'display_with_account' => $bank->display_with_account,
                'type' => $bank->type,
                'bank_name' => $bank->bank_name,
                'account_number' => $bank->account_number,
            ])
            ->values();

        $taxRates = TaxRate::where('user_id', $userId)
            ->orderBy('name')
            ->get()
            ->map(fn($rate) => [
                'id' => $rate->id,
                'name' => $rate->name,
                'rate' => $rate->rate,
            ])
            ->values();

        $transactionSettings = [
            'tax_enabled' => (bool) data_get($settings, 'transaction_totals.tax_enabled', true),
            'additional_charges_enabled' => (bool) data_get($settings, 'additional_charges.enabled', false),
            'transportation_details_enabled' => (bool) data_get($settings, 'transportation_details.enabled', false),
            'additional_charges_items' => data_get($settings, 'additional_charges.items', []),
            'transportation_details_fields' => data_get($settings, 'transportation_details.fields', []),
            'transaction_header' => data_get($settings, 'transaction_header', []),
            'more_transaction_features' => data_get($settings, 'more_transaction_features', []),
            'payment_terms' => data_get($settings, 'payment_terms', []),
        ];

        return view('dashboard.expense.expense', compact(
            'categories',
            'expenseItems',
            'parties',
            'partyGroups',
            'bankAccounts',
            'taxRates',
            'transactionSettings',
            'startInCreate'
        ));
    }

    // ─── EXPENSE CATEGORY CRUD ───────────────────────────────────
    public function storeCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255', 'type' => 'required|string']);
        $cat = ExpenseCategory::create([
            'user_id' => Auth::id(),
            'name'    => $request->name,
            'type'    => $request->type,
        ]);
        return response()->json(['success' => true, 'category' => [
            'id' => $cat->id, 'name' => $cat->name, 'type' => $cat->type, 'amount' => 0, 'entries' => []
        ]]);
    }

   public function updateCategory(Request $request, $id)
{
    $category = ExpenseCategory::where('user_id', auth()->id())
                    ->findOrFail($id);

    $category->update([
        'name' => $request->name,
        'type' => $request->type,
    ]);

    return response()->json(['success' => true, 'category' => $category]);
}

    public function destroyCategory($id)
    {
        $cat = ExpenseCategory::where('user_id', Auth::id())->findOrFail($id);
        if ($cat->expenses()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Cannot delete: category has transactions.'], 422);
        }
        $cat->delete();
        return response()->json(['success' => true]);
    }

    // ─── EXPENSE ITEM CRUD ───────────────────────────────────────
    public function storeItem(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'tax_included' => 'nullable|boolean',
            'tax_rate_id' => 'nullable|exists:tax_rates,id',
        ]);

        $normalizedName = strtolower(trim((string) $request->name));
        $existingItem = Item::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [$normalizedName])
            ->first();

        $price = (float) ($request->price ?? 0);
        $taxRate = null;
        if ($request->filled('tax_rate_id')) {
            $taxRate = TaxRate::where('user_id', Auth::id())
                ->whereKey($request->tax_rate_id)
                ->first();
        }
        $taxRateValue = (float) ($request->input('tax_rate_value', $taxRate?->rate ?? 0));
        $taxIncluded = $request->boolean('tax_included');
        $taxAmount = 0;
        $amount = $price;

        if ($taxRateValue > 0) {
            if ($taxIncluded) {
                $baseAmount = $price / (1 + ($taxRateValue / 100));
                $taxAmount = max($price - $baseAmount, 0);
                $amount = $price;
            } else {
                $taxAmount = $price * ($taxRateValue / 100);
                $amount = $price + $taxAmount;
            }
        }

        $itemData = [
            'type' => $request->input('type', $existingItem?->type ?? 'product'),
            'name' => trim((string) $request->name),
            'price' => $price,
        ];
        if (Schema::hasColumn('items', 'sale_price')) {
            $itemData['sale_price'] = $price;
        }
        if (Schema::hasColumn('items', 'purchase_price')) {
            $itemData['purchase_price'] = $price;
        }
        if (Schema::hasColumn('items', 'wholesale_price')) {
            $itemData['wholesale_price'] = $price;
        }
        if (Schema::hasColumn('items', 'description') && $request->filled('description')) {
            $itemData['description'] = $request->description;
        }

        if ($existingItem) {
            $existingItem->update($itemData);

            return response()->json(['success' => true, 'item' => [
                'id' => $existingItem->id,
                'name' => $existingItem->name,
                'price' => $existingItem->price,
            ]]);
        }

        $item = Item::create($itemData);
        return response()->json(['success' => true, 'item' => [
            'id' => $item->id,
            'name' => $item->name,
            'price' => $item->price,
        ]]);
    }

    public function updateItem(Request $request, $id)
    {
        $item = Item::findOrFail($id);
        $request->validate([
            'name' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'tax_included' => 'nullable|boolean',
            'tax_rate_id' => 'nullable|exists:tax_rates,id',
        ]);

        $price = (float) ($request->price ?? $item->price ?? 0);
        $taxRate = null;
        if ($request->filled('tax_rate_id')) {
            $taxRate = TaxRate::where('user_id', Auth::id())
                ->whereKey($request->tax_rate_id)
                ->first();
        }
        $taxRateValue = (float) ($request->input('tax_rate_value', $taxRate?->rate ?? $item->tax_rate_value ?? 0));
        $taxIncluded = $request->has('tax_included')
            ? $request->boolean('tax_included')
            : (bool) ($item->tax_included ?? false);
        $taxAmount = (float) ($item->tax_amount ?? 0);
        $amount = $price;

        if ($taxRateValue > 0) {
            if ($taxIncluded) {
                $baseAmount = $price / (1 + ($taxRateValue / 100));
                $taxAmount = max($price - $baseAmount, 0);
                $amount = $price;
            } else {
                $taxAmount = $price * ($taxRateValue / 100);
                $amount = $price + $taxAmount;
            }
        } else {
            $taxAmount = 0;
        }

        $updateData = [
            'name'  => $request->name ?? $item->name,
            'price' => $price,
        ];
        if (Schema::hasColumn('items', 'type') && $request->filled('type')) {
            $updateData['type'] = $request->type;
        }
        if (Schema::hasColumn('items', 'sale_price')) {
            $updateData['sale_price'] = $price;
        }
        if (Schema::hasColumn('items', 'purchase_price')) {
            $updateData['purchase_price'] = $price;
        }
        if (Schema::hasColumn('items', 'wholesale_price')) {
            $updateData['wholesale_price'] = $price;
        }
        if (Schema::hasColumn('items', 'description') && $request->filled('description')) {
            $updateData['description'] = $request->description;
        }

        $item->update($updateData);
        return response()->json(['success' => true, 'item' => [
            'id' => $item->id,
            'name' => $item->name,
            'price' => $item->price,
        ]]);
    }

    public function destroyItem($id)
    {
        $item = Item::findOrFail($id);
        $item->delete();
        return response()->json(['success' => true]);
    }

    // ─── EXPENSE CRUD ─────────────────────────────────────────────
    public function storeExpense(Request $request)
    {
        $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'expense_date'        => 'required|date',
            'total_amount'        => 'required|numeric|min:0',
            'payment_type'        => 'nullable|string',
            'party_id'            => 'nullable|exists:parties,id',
            'po_no'               => 'nullable|string|max:255',
            'po_date'             => 'nullable|date',
            'transaction_time'    => 'nullable|string|max:20',
            'deal_days'           => 'nullable|integer|min:0',
            'due_date'            => 'nullable|date',
            'payment_terms_name'  => 'nullable|string|max:100',
            'status'              => 'nullable|string|max:20',
            'discount_percent'    => 'nullable|numeric|min:0',
            'discount_amount'     => 'nullable|numeric|min:0',
            'bank_account_id'     => 'nullable|exists:bank_accounts,id',
            'tax_rate_id'         => 'nullable|exists:tax_rates,id',
            'tax_amount'          => 'nullable|numeric|min:0',
            'items_json'          => 'nullable',
            'additional_charges'  => 'nullable',
            'transportation_details' => 'nullable',
            'attachments'         => 'nullable',
            'description'         => 'nullable|string',
            'payments_json'       => 'nullable',
            'linked_rows'         => 'nullable',
            'images'              => 'nullable|array',
            'images.*'            => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:4096',
            'documents'           => 'nullable|array',
            'documents.*'         => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:4096',
        ]);

        return DB::transaction(function () use ($request) {
            $partyName = $request->input('party', '');
            if (!$partyName && $request->filled('party_id')) {
                $partyName = Party::whereKey($request->party_id)->value('name') ?? '';
            }

            $paymentType = $request->input('payment_type');
            if ($request->filled('bank_account_id') && (!$paymentType || strtolower((string) $paymentType) === 'bank')) {
                $paymentType = 'Bank';
            } elseif (!$paymentType) {
                $paymentType = 'Cash';
            }
            $status = strtolower(trim((string) $request->input('status', '')));
            $dealDays = max(0, (int) $request->input('deal_days', 0));
            $dueDate = $request->input('due_date');
            if (!$dueDate && $request->filled('expense_date') && $dealDays > 0) {
                $dueDate = Carbon::parse($request->expense_date)->addDays($dealDays)->toDateString();
            }
            if (!$dueDate && $request->filled('expense_date')) {
                $dueDate = Carbon::parse($request->expense_date)->toDateString();
            }
            $transactionTime = trim((string) $request->input('transaction_time', ''));
            if ($transactionTime === '' && $request->boolean('transaction_time_enabled', false)) {
                $transactionTime = now()->format('h:i A');
            }

            $itemsJson = $request->input('items_json', []);
            if (is_string($itemsJson)) {
                $itemsJson = json_decode($itemsJson, true) ?: [];
            }
            if (!is_array($itemsJson)) {
                $itemsJson = [];
            }

            $additionalCharges = $request->input('additional_charges', []);
            if (is_string($additionalCharges)) {
                $additionalCharges = json_decode($additionalCharges, true) ?: [];
            }
            if (!is_array($additionalCharges)) {
                $additionalCharges = [];
            }

            $transportationDetails = $request->input('transportation_details', []);
            if (is_string($transportationDetails)) {
                $transportationDetails = json_decode($transportationDetails, true) ?: [];
            }
            if (!is_array($transportationDetails)) {
                $transportationDetails = [];
            }

            $paymentsJson = $request->input('payments_json', []);
            if (is_string($paymentsJson)) {
                $paymentsJson = json_decode($paymentsJson, true) ?: [];
            }
            if (!is_array($paymentsJson)) {
                $paymentsJson = [];
            }
            $paymentEntries = collect($paymentsJson)->map(function ($payment) {
                return [
                    'type' => (string) ($payment['type'] ?? ''),
                    'amount' => (float) ($payment['amount'] ?? 0),
                    'ref' => $payment['ref'] ?? null,
                ];
            })->filter(fn ($payment) => $payment['type'] !== '')->values();

            $linkedRows = $request->input('linked_rows', []);
            if (is_string($linkedRows)) {
                $linkedRows = json_decode($linkedRows, true) ?: [];
            }
            if (!is_array($linkedRows)) {
                $linkedRows = [];
            }
            $linkedRows = collect($linkedRows)->map(function ($row) {
                $targetId = (int) ($row['transaction_id'] ?? $row['purchase_id'] ?? $row['sale_id'] ?? 0);
                return [
                    'target_id' => $targetId,
                    'transaction_id' => (int) ($row['transaction_id'] ?? 0),
                    'purchase_id' => (int) ($row['purchase_id'] ?? $row['sale_id'] ?? 0),
                    'linked_amount' => (float) ($row['linked_amount'] ?? 0),
                ];
            })->filter(fn ($row) => $row['target_id'] > 0 && $row['linked_amount'] > 0)->values();

            $attachmentPaths = $request->input('attachments', []);
            if (is_string($attachmentPaths)) {
                $attachmentPaths = json_decode($attachmentPaths, true) ?: [];
            }
            if (!is_array($attachmentPaths)) {
                $attachmentPaths = ['images' => [], 'documents' => []];
            }
            $attachmentPaths = array_merge(['images' => [], 'documents' => []], $attachmentPaths);

            foreach ((array) $request->file('images', []) as $image) {
                if ($image && $image->isValid()) {
                    $attachmentPaths['images'][] = $image->store('expenses/attachments/images', 'public');
                }
            }
            foreach ((array) $request->file('documents', []) as $document) {
                if ($document && $document->isValid()) {
                    $attachmentPaths['documents'][] = $document->store('expenses/attachments/documents', 'public');
                }
            }

            $expense = Expense::create([
                'user_id'             => Auth::id(),
                'expense_category_id' => $request->expense_category_id,
                'expense_no'          => $request->expense_no,
                'expense_date'        => $request->expense_date,
                'party_id'            => $request->party_id,
                'party'               => $partyName,
                'po_no'               => $request->po_no,
                'po_date'             => $request->po_date,
                'transaction_time'    => $transactionTime ?: null,
                'deal_days'           => $dealDays ?: null,
                'due_date'            => $dueDate,
                'payment_terms_name'  => $request->input('payment_terms_name'),
                'status'              => $status,
                'discount_percent'    => $request->input('discount_percent', 0),
                'discount_amount'     => $request->input('discount_amount', 0),
                'tax_enabled'         => (bool) $request->boolean('tax_enabled'),
                'tax_rate_id'         => $request->tax_rate_id,
                'tax_rate_name'       => $request->tax_rate_name,
                'tax_rate_value'      => $request->input('tax_rate_value', 0),
                'tax_amount'          => $request->input('tax_amount', 0),
                'items_json'          => $itemsJson,
                'additional_charges'  => $additionalCharges,
                'transportation_details' => $transportationDetails,
                'attachments'         => $attachmentPaths,
                'description'         => $request->description,
                'bank_account_id'     => $request->bank_account_id,
                'total_amount'        => $request->total_amount,
                'payment_type'        => $paymentType,
                'reference_no'        => $request->reference_no,
                'balance'             => 0,
            ]);

            foreach ($itemsJson as $itemPayload) {
                if (empty($itemPayload['name'])) {
                    continue;
                }
                $itemData = [
                    'user_id' => Auth::id(),
                    'expense_id' => $expense->id,
                    'name' => $itemPayload['name'],
                    'quantity' => (int) ($itemPayload['qty'] ?? $itemPayload['quantity'] ?? 1),
                    'unit_price' => (float) ($itemPayload['price'] ?? $itemPayload['unit_price'] ?? 0),
                    'price' => (float) ($itemPayload['price'] ?? $itemPayload['unit_price'] ?? 0),
                ];
                if (Schema::hasColumn('expense_items', 'tax_included')) {
                    $itemData['tax_included'] = $itemPayload['tax_included'] ?? null;
                }
                if (Schema::hasColumn('expense_items', 'tax_rate_id')) {
                    $itemData['tax_rate_id'] = $itemPayload['taxRateId'] ?? $itemPayload['tax_rate_id'] ?? null;
                }
                if (Schema::hasColumn('expense_items', 'tax_rate_name')) {
                    $itemData['tax_rate_name'] = $itemPayload['taxRateName'] ?? $itemPayload['tax_rate_name'] ?? null;
                }
                if (Schema::hasColumn('expense_items', 'tax_rate_value')) {
                    $itemData['tax_rate_value'] = $itemPayload['taxRateValue'] ?? $itemPayload['tax_rate_value'] ?? null;
                }
                if (Schema::hasColumn('expense_items', 'tax_amount')) {
                    $itemData['tax_amount'] = (float) ($itemPayload['taxAmount'] ?? 0);
                }
                if (Schema::hasColumn('expense_items', 'amount')) {
                    $itemData['amount'] = (float) ($itemPayload['amount'] ?? $itemData['unit_price'] * $itemData['quantity']);
                }
                ExpenseItem::create($itemData);
            }

            $bankPayments = $paymentEntries->map(function ($payment) use ($request) {
                $type = (string) ($payment['type'] ?? '');
                if (!str_starts_with($type, 'bank:')) {
                    return null;
                }

                return [
                    'bank_account_id' => (int) str_replace('bank:', '', $type),
                    'amount' => (float) ($payment['amount'] ?? 0),
                    'reference' => $payment['ref'] ?? null,
                ];
            })->filter(fn ($payment) => !empty($payment['bank_account_id']))->values();

            if ($bankPayments->isEmpty() && $request->filled('bank_account_id')) {
                $bankPayments = collect([[
                    'bank_account_id' => (int) $request->bank_account_id,
                    'amount' => (float) $request->total_amount,
                    'reference' => $request->reference_no,
                ]]);
            }

            $singlePaymentFallback = $bankPayments->count() === 1 ? (float) $request->total_amount : 0;
            foreach ($bankPayments as $payment) {
                $amount = (float) ($payment['amount'] ?: $singlePaymentFallback);
                if ($amount <= 0) {
                    continue;
                }

                $bank = BankAccount::find($payment['bank_account_id']);
                if (!$bank) {
                    continue;
                }

                $bank->opening_balance = (float) ($bank->opening_balance ?? 0) + $amount;
                $bank->save();

                BankTransaction::create([
                    'from_bank_account_id' => null,
                    'to_bank_account_id' => $bank->id,
                    'type' => 'expense_payment',
                    'amount' => $amount,
                    'transaction_date' => $expense->expense_date ?? now()->toDateString(),
                    'reference_type' => 'expense',
                    'reference_id' => $expense->id,
                    'description' => 'Expense payment added to bank account',
                    'meta' => [
                        'expense_no' => $expense->expense_no,
                        'party_id' => $expense->party_id,
                        'payment_type' => 'Bank',
                        'reference' => $payment['reference'] ?? null,
                    ],
                ]);
            }

            $paidAmount = (float) $paymentEntries->sum('amount');
            if ($paidAmount <= 0 && $request->filled('bank_account_id')) {
                $paidAmount = (float) $request->total_amount;
            } elseif ($paidAmount <= 0 && $bankPayments->count() === 1) {
                $paidAmount = (float) $request->total_amount;
            }
            $balance = max((float) $request->total_amount - $paidAmount, 0);
            if (!in_array($status, ['paid', 'pay', 'unpaid'], true)) {
                $status = $balance <= 0 ? 'paid' : ($paidAmount > 0 ? 'pay' : 'unpaid');
            }

            $expenseTransaction = null;
            if (!empty($expense->party_id) && (float) $expense->total_amount > 0) {
                $transactionStatus = $balance <= 0 ? 'paid' : ($paidAmount > 0 ? 'pay' : 'unpaid');
                $expenseTransaction = Transaction::create([
                    'party_id' => (int) $expense->party_id,
                    'type' => 'payment_out',
                    'number' => 'EXP-' . $expense->id,
                    'date' => $expense->expense_date,
                    'total' => (float) $expense->total_amount,
                    'credit' => (float) $expense->total_amount,
                    'debit' => 0,
                    'paid_amount' => $paidAmount,
                    'balance' => $balance,
                    'running_balance' => $balance,
                    'status' => $transactionStatus,
                    'description' => 'Expense: ' . ($expense->expense_no ?: ('#' . $expense->id)),
                ]);
            }

            if ($expenseTransaction && $linkedRows->isNotEmpty()) {
                foreach ($linkedRows as $linkedRow) {
                    $targetId = (int) ($linkedRow['target_id'] ?? 0);
                    $linkedAmount = (float) ($linkedRow['linked_amount'] ?? 0);
                    if ($targetId <= 0 || $linkedAmount <= 0) {
                        continue;
                    }

                    DB::table('payment_links')->insert([
                        'transaction_id' => $expenseTransaction->id,
                        'sale_id' => $targetId,
                        'linked_amount' => $linkedAmount,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $linkedTransaction = DB::table('transactions')->where('id', $targetId)->first();
                    if ($linkedTransaction) {
                        $paid = (float) ($linkedTransaction->paid_amount ?? 0) + $linkedAmount;
                        $grandTotal = (float) ($linkedTransaction->total ?? 0);
                        $linkedBalance = max($grandTotal - $paid, 0);

                        DB::table('transactions')->where('id', $targetId)->update([
                            'paid_amount' => $paid,
                            'balance' => $linkedBalance,
                            'status' => $linkedBalance <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
                        ]);
                        continue;
                    }

                    $purchase = DB::table('purchases')->where('id', $targetId)->first();
                    if ($purchase) {
                        $paid = (float) ($purchase->paid_amount ?? 0) + $linkedAmount;
                        $grandTotal = (float) ($purchase->grand_total ?? 0);
                        $purchaseBalance = max($grandTotal - $paid, 0);

                        DB::table('purchases')->where('id', $targetId)->update([
                            'paid_amount' => $paid,
                            'balance' => $purchaseBalance,
                            'status' => $purchaseBalance <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
                        ]);
                    }
                }
            }

            $expense->balance = $balance;
            $expense->save();

            return response()->json(['success' => true, 'expense' => [
                'id'          => $expense->id,
                'date'        => $expense->expense_date,
                'expNo'       => $expense->expense_no ?? '',
                'reference_no'=> $expense->reference_no ?? '',
                'party'       => $expense->party ?? '',
                'party_id'    => $expense->party_id,
                'poNo'        => $expense->po_no ?? '',
                'poDate'      => optional($expense->po_date)->format('Y-m-d') ?: '',
                'transactionTime' => $expense->transaction_time ?? '',
                'dealDays'    => $expense->deal_days ?? 0,
                'dueDate'     => optional($expense->due_date)->format('Y-m-d') ?: '',
                'paymentTermsName' => $expense->payment_terms_name ?? '',
                'status'      => $expense->status ?? $status,
                'taxEnabled'  => (bool) $expense->tax_enabled,
                'taxRateId'   => $expense->tax_rate_id,
                'taxRateName' => $expense->tax_rate_name,
                'taxRateValue'=> $expense->tax_rate_value,
                'taxAmount'   => $expense->tax_amount,
                'discountPercent' => $expense->discount_percent ?? 0,
                'discountAmount' => $expense->discount_amount ?? 0,
                'summaryTaxRateId' => $expense->tax_rate_id,
                'summaryTaxAmount' => $expense->tax_amount,
                'items'       => $expense->items_json ?? [],
                'additionalCharges' => $expense->additional_charges ?? [],
                'transportationDetails' => $expense->transportation_details ?? [],
                'description' => $expense->description ?? '',
                'bankAccountId' => $expense->bank_account_id,
                'paymentType' => $expense->payment_type,
                'amount'      => $expense->total_amount,
                'paidAmount'  => $paidAmount,
                'balance'     => $balance,
                'linkedRows'  => $linkedRows->values(),
            ]]);
        });
    }

    public function destroyExpense($id)
    {
        return DB::transaction(function () use ($id) {
            $expense = Expense::where('user_id', Auth::id())->findOrFail($id);
            $bankTransactions = BankTransaction::where('reference_type', 'expense')
                ->where('reference_id', $expense->id)
                ->get();

            foreach ($bankTransactions as $transaction) {
                if (!$transaction->to_bank_account_id || (float) $transaction->amount <= 0) {
                    continue;
                }

                $bank = BankAccount::find($transaction->to_bank_account_id);
                if ($bank) {
                    $bank->opening_balance = (float) ($bank->opening_balance ?? 0) - (float) $transaction->amount;
                    $bank->save();
                }
            }

            BankTransaction::where('reference_type', 'expense')
                ->where('reference_id', $expense->id)
                ->delete();

            $expenseTransaction = Transaction::where('party_id', $expense->party_id)
                ->where('type', 'payment_out')
                ->where('number', 'EXP-' . $expense->id)
                ->first();

            if ($expenseTransaction) {
                $linkedRows = DB::table('payment_links')
                    ->where('transaction_id', $expenseTransaction->id)
                    ->orderBy('id')
                    ->get(['sale_id', 'linked_amount']);

                foreach ($linkedRows as $linkedRow) {
                    $targetId = (int) ($linkedRow->sale_id ?? 0);
                    $linkedAmount = (float) ($linkedRow->linked_amount ?? 0);
                    if ($targetId <= 0 || $linkedAmount <= 0) {
                        continue;
                    }

                    $linkedTransaction = DB::table('transactions')->where('id', $targetId)->first();
                    if ($linkedTransaction) {
                        $paid = max((float) ($linkedTransaction->paid_amount ?? 0) - $linkedAmount, 0);
                        $grandTotal = (float) ($linkedTransaction->total ?? 0);
                        $linkedBalance = max($grandTotal - $paid, 0);

                        DB::table('transactions')->where('id', $targetId)->update([
                            'paid_amount' => $paid,
                            'balance' => $linkedBalance,
                            'status' => $linkedBalance <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
                        ]);
                        continue;
                    }

                    $purchase = DB::table('purchases')->where('id', $targetId)->first();
                    if ($purchase) {
                        $paid = max((float) ($purchase->paid_amount ?? 0) - $linkedAmount, 0);
                        $grandTotal = (float) ($purchase->grand_total ?? 0);
                        $purchaseBalance = max($grandTotal - $paid, 0);

                        DB::table('purchases')->where('id', $targetId)->update([
                            'paid_amount' => $paid,
                            'balance' => $purchaseBalance,
                            'status' => $purchaseBalance <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
                        ]);
                    }
                }

                DB::table('payment_links')->where('transaction_id', $expenseTransaction->id)->delete();
                $expenseTransaction->delete();
            }

            ExpenseItem::where('expense_id', $expense->id)->delete();
            $expense->delete();

            return response()->json(['success' => true]);
        });
    }

    private function getExpenseLinkedRows(int $expenseId): array
    {
        $transaction = Transaction::where('number', 'EXP-' . $expenseId)->first();
        if (!$transaction) {
            return [];
        }

        return DB::table('payment_links')
            ->where('transaction_id', $transaction->id)
            ->orderBy('id')
            ->get(['sale_id', 'linked_amount'])
            ->map(function ($row) {
                return [
                    'transaction_id' => (int) ($row->sale_id ?? 0),
                    'purchase_id' => (int) ($row->sale_id ?? 0),
                    'sale_id' => (int) ($row->sale_id ?? 0),
                    'linked_amount' => (float) ($row->linked_amount ?? 0),
                ];
            })
            ->values()
            ->all();
    }
}
