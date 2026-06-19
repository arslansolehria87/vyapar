<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Party;
use App\Models\PartyGroup;
use App\Models\AppSetting;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PartyController extends Controller
{
    // Display all parties
    public function index()
    {
        Party::query()->select('id')->orderBy('id')->chunk(200, function ($parties) {
            foreach ($parties as $party) {
                $this->syncPartyCurrentBalance((int) $party->id);
            }
        });

        $parties = Party::with('sales')->latest()->get();
        $partyGroups = PartyGroup::orderBy('name')->get();
        $partySettings = $this->getPartySettings();
        $partyStatusEnabled = $partySettings['party_status'];
        $reminderParties = Party::query()
            ->orderBy('name')
            ->get();

        return view('parties.index', compact('parties', 'partyGroups', 'partyStatusEnabled', 'partySettings', 'reminderParties'));
    }

    // Show create party form
    public function create(Request $request)
    {
        $returnUrl = $request->query('return_url');
        return view('parties.create', compact('returnUrl'));
    }

    // Store a new party
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'phone_number_2' => 'nullable|string|max:20',
            'ptcl_number' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:1000',
            'billing_address' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'opening_balance' => 'nullable|numeric',
            'as_of_date' => 'nullable|date',
            'credit_limit_enabled' => 'nullable|boolean',
            'credit_limit_amount' => 'nullable|numeric|min:0|required_if:credit_limit_enabled,1',
            'due_days' => 'nullable|integer|min:1|max:100',
            'custom_fields' => 'nullable|array',
            'transaction_type' => 'nullable|in:receive,pay',
            'party_type' => 'nullable|array',
            'party_type.*' => 'in:customer,supplier,broker',
            'party_group' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ]);
        $data['party_type'] = $this->normalizePartyType($data['party_type'] ?? []);
        $data['opening_balance'] = (float) ($data['opening_balance'] ?? 0);
        $data['custom_fields'] = collect($data['custom_fields'] ?? [])
            ->filter(fn ($value) => filled($value))
            ->values()
            ->all();

        if (empty($data['credit_limit_enabled'])) {
            $data['credit_limit_amount'] = null;
        }

        $data['is_active'] = $request->boolean('is_active', true);
        $party = Party::create($data);

        if (!empty($data['party_group'])) {
            PartyGroup::firstOrCreate(['name' => trim($data['party_group'])]);
        }

        $openingBalance = $data['opening_balance'] ?? 0;
        $transactionType = $data['transaction_type'] ?? null;

        if ($openingBalance > 0 && in_array($transactionType, ['receive', 'pay'], true)) {
            Transaction::create([
                'party_id' => $party->id,
                'type'     => $transactionType,
                'number'   => 'TXN' . time(),
                'date'     => $data['as_of_date'] ?? now(),
                'total'    => $openingBalance,
                'debit'    => $transactionType === 'receive' ? $openingBalance : 0,
                'credit'   => $transactionType === 'pay' ? $openingBalance : 0,
                'balance'  => $openingBalance,
                'running_balance' => $openingBalance,
                'status'   => $transactionType,
            ]);
        }

        $this->syncPartyCurrentBalance($party->id);

        $party = $party->fresh();
        $party->load('sales');

        return response()->json([
            'success' => true,
            'party' => $party
        ]);
    }

    public function downloadImportTemplate()
    {
        $columns = [
            'Name*',
            'Contact No.',
            'Email ID',
            'Address',
            'Shipping Address',
            'Opening Balance',
            'Opening Date (dd/MM/yyyy)',
            'Party Type',
            'Transaction Type',
            'Credit Limit Enabled',
            'Credit Limit Amount',
            'Party Group',
            'Phone Number 2',
            'PTCL Number',
            'Billing Address',
            'Custom Fields',
        ];
        $sampleRows = [
            [
                'Party 1',
                '1234567891',
                'abc1@xyz.com',
                'Address 1',
                'XYZ Street, Bangalore',
                '200',
                '07/07/2019',
                'customer',
                'receive',
                'No',
                '',
                'Retail',
                '',
                '',
                'Billing Address 1',
                '',
            ],
            [
                'Party 2',
                '1234567892',
                'abc2@xyz.com',
                'Address 2',
                'XYZ Street, Bangalore',
                '300',
                '08/08/2019',
                'supplier',
                'pay',
                'Yes',
                '5000',
                'Wholesale',
                '02123456789',
                '04234567890',
                'Billing Address 2',
                'ref:sample',
            ],
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($columns as $index => $column) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $column);
        }

        foreach ($sampleRows as $rowIndex => $row) {
            foreach ($row as $columnIndex => $value) {
                $sheet->setCellValueByColumnAndRow($columnIndex + 1, $rowIndex + 2, $value);
            }
        }

        foreach (range(1, count($columns)) as $columnIndex) {
            $sheet->getColumnDimensionByColumn($columnIndex)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'party-import-template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function previewImport(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xls,xlsx,csv',
        ]);

        $rows = $this->loadSpreadsheetRows($request->file('import_file'));
        $headerRow = [];
        $importRows = [];

        foreach ($rows as $rowNumber => $row) {
            if ($rowNumber === 1) {
                $headerRow = $row;
                continue;
            }

            $mapped = [];
            foreach ($row as $column => $value) {
                if (isset($headerRow[$column])) {
                    $mapped[$this->normalizeHeaderColumn((string) $headerRow[$column])] = trim((string) $value);
                }
            }

            if (count(array_filter($mapped, fn ($value) => $value !== '')) === 0) {
                continue;
            }

            $mapped['_row'] = $rowNumber;
            $importRows[] = $mapped;
        }

        if (count($importRows) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'The uploaded file does not contain any valid party rows.',
            ], 422);
        }

        $validRows = [];
        $invalidRows = [];
        $seenNames = [];

        foreach ($importRows as $rowData) {
            $rowNumber = $rowData['_row'] ?? null;
            $data = array_merge($rowData, [
                'party_type' => $this->normalizePartyTypeString($rowData['party_type'] ?? null),
                'credit_limit_enabled' => $this->normalizeBoolean($rowData['credit_limit_enabled'] ?? null),
                'transaction_type' => strtolower(trim($rowData['transaction_type'] ?? '')),
                'as_of_date' => $this->normalizeDateValue($rowData['as_of_date'] ?? $rowData['opening_date'] ?? null),
                'opening_balance' => $this->normalizeNumeric($rowData['opening_balance'] ?? null),
            ]);

            $validator = Validator::make($data, [
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:20',
                'phone_number_2' => 'nullable|string|max:20',
                'ptcl_number' => 'nullable|string|max:30',
                'email' => 'nullable|email|max:255',
                'city' => 'nullable|string|max:100',
                'address' => 'nullable|string|max:1000',
                'billing_address' => 'nullable|string',
                'shipping_address' => 'nullable|string',
                'opening_balance' => 'nullable|numeric',
                'as_of_date' => 'nullable|date',
                'credit_limit_enabled' => 'nullable|boolean',
                'credit_limit_amount' => 'nullable|numeric|min:0',
                'due_days' => 'nullable|integer|min:1|max:100',
                'custom_fields' => 'nullable|array',
                'custom_fields.*' => 'nullable|string|max:255',
                'transaction_type' => 'nullable|in:receive,pay',
                'party_type' => 'nullable|string',
                'party_group' => 'nullable|string|max:100',
            ]);

            $errors = $validator->errors()->all();

            if (!empty($data['name'])) {
                $normalName = strtolower($data['name']);
                if (isset($seenNames[$normalName])) {
                    $errors[] = 'Duplicate party name in the uploaded file.';
                }

                if (Party::whereRaw('LOWER(name) = ?', [$normalName])->exists()) {
                    $errors[] = 'A party with this name already exists.';
                }

                $seenNames[$normalName] = true;
            }

            if ($data['credit_limit_enabled'] && ($data['credit_limit_amount'] === null || $data['credit_limit_amount'] === '')) {
                $errors[] = 'Credit limit amount is required when credit limit is enabled.';
            }

            if (!empty($errors)) {
                $invalidRows[] = [
                    'row' => $rowNumber,
                    'errors' => array_values(array_unique($errors)),
                    'data' => $data,
                ];
                continue;
            }

            $validRows[] = [
                'row' => $rowNumber,
                'data' => [
                    'name' => $data['name'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'phone_number_2' => $data['phone_number_2'] ?? null,
                    'ptcl_number' => $data['ptcl_number'] ?? null,
                    'email' => $data['email'] ?? null,
                    'city' => $data['city'] ?? null,
                    'address' => $data['address'] ?? null,
                    'billing_address' => $data['billing_address'] ?? null,
                    'shipping_address' => $data['shipping_address'] ?? null,
                    'opening_balance' => $data['opening_balance'] ?? null,
                    'as_of_date' => $data['as_of_date'] ?? null,
                    'credit_limit_enabled' => $data['credit_limit_enabled'] ? 1 : 0,
                    'credit_limit_amount' => $data['credit_limit_amount'] ?? null,
                'due_days' => $data['due_days'] ?? null,
                'custom_fields' => $this->normalizePartyCustomFields($data['custom_fields'] ?? null),
                'transaction_type' => $data['transaction_type'] ?? null,
                'party_type' => $data['party_type'] ?? null,
                'party_group' => $data['party_group'] ?? null,
            ],
            ];
        }

        return response()->json([
            'success' => true,
            'valid_parties' => $validRows,
            'invalid_parties' => $invalidRows,
            'valid_count' => count($validRows),
            'invalid_count' => count($invalidRows),
        ]);
    }

    public function importValidParties(Request $request)
    {
        $payload = $request->validate([
            'parties' => 'required|array|min:1',
            'parties.*.name' => 'required|string|max:255',
            'parties.*.phone' => 'nullable|string|max:20',
            'parties.*.phone_number_2' => 'nullable|string|max:20',
            'parties.*.ptcl_number' => 'nullable|string|max:30',
            'parties.*.email' => 'nullable|email|max:255',
            'parties.*.city' => 'nullable|string|max:100',
            'parties.*.address' => 'nullable|string|max:1000',
            'parties.*.billing_address' => 'nullable|string',
            'parties.*.shipping_address' => 'nullable|string',
            'parties.*.opening_balance' => 'nullable|numeric',
            'parties.*.as_of_date' => 'nullable|date',
            'parties.*.credit_limit_enabled' => 'nullable|boolean',
            'parties.*.credit_limit_amount' => 'nullable|numeric|min:0',
            'parties.*.due_days' => 'nullable|integer|min:1|max:100',
            'parties.*.custom_fields' => 'nullable|string',
            'parties.*.transaction_type' => 'nullable|in:receive,pay',
            'parties.*.party_type' => 'nullable|string',
            'parties.*.party_group' => 'nullable|string|max:100',
        ]);

        $createdCount = 0;
        $partyColumns = array_flip(Schema::getColumnListing('parties'));

        DB::beginTransaction();
        try {
            foreach ($payload['parties'] as $partyRow) {
                $partyRow['party_type'] = $this->normalizePartyTypeString($partyRow['party_type'] ?? null);
                if (empty($partyRow['credit_limit_enabled'])) {
                    $partyRow['credit_limit_amount'] = null;
                }

                if (Party::whereRaw('LOWER(name) = ?', [strtolower($partyRow['name'])])->exists()) {
                    continue;
                }

                $openingBalance = isset($partyRow['opening_balance']) ? (float) $partyRow['opening_balance'] : 0;
                $partyRow['current_balance'] = $openingBalance;
                $partyRow['custom_fields'] = !empty($partyRow['custom_fields'])
                    ? ['imported_value' => $partyRow['custom_fields']]
                    : null;

                $partyData = array_filter(
                    $partyRow,
                    fn ($value, $key) => isset($partyColumns[$key]),
                    ARRAY_FILTER_USE_BOTH
                );

                $party = Party::create($partyData);

                Transaction::create([
                    'party_id' => $party->id,
                    'type' => $partyRow['transaction_type'] ?? null,
                    'number' => 'TXN' . time() . rand(10, 99),
                    'date' => $partyRow['as_of_date'] ?? now(),
                    'total' => $openingBalance,
                    'debit' => $partyRow['transaction_type'] === 'receive' ? $openingBalance : 0,
                    'credit' => $partyRow['transaction_type'] === 'pay' ? $openingBalance : 0,
                    'balance' => $openingBalance,
                    'running_balance' => $openingBalance,
                    'status' => $partyRow['transaction_type'] ?? 'unpaid',
                ]);

                $this->syncPartyCurrentBalance($party->id);
                $createdCount++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Unable to import valid parties at this time.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'created' => $createdCount,
        ]);
    }

    private function loadSpreadsheetRows($file): array
    {
        $reader = IOFactory::createReaderForFile($file->getPathname());
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();

        return $sheet->toArray(null, true, true, true);
    }

    private function normalizeHeaderColumn(string $value): string
    {
        $key = trim(strtolower($value));
        $map = [
            'party name' => 'name',
            'name' => 'name',
            'name*' => 'name',
            'contact no' => 'phone',
            'contact no.' => 'phone',
            'contact number' => 'phone',
            'email id' => 'email',
            'email' => 'email',
            'address' => 'address',
            'shipping address' => 'shipping_address',
            'shipping_address' => 'shipping_address',
            'opening balance' => 'opening_balance',
            'opening date (dd/mm/yyyy)' => 'as_of_date',
            'opening date (dd-mm-yyyy)' => 'as_of_date',
            'opening date' => 'as_of_date',
            'as of date' => 'as_of_date',
            'as_of_date' => 'as_of_date',
            'credit limit enabled' => 'credit_limit_enabled',
            'credit_limit_enabled' => 'credit_limit_enabled',
            'credit limit amount' => 'credit_limit_amount',
            'credit_limit_amount' => 'credit_limit_amount',
            'due days' => 'due_days',
            'due_days' => 'due_days',
            'custom fields' => 'custom_fields',
            'custom_fields' => 'custom_fields',
            'transaction type' => 'transaction_type',
            'transaction_type' => 'transaction_type',
            'party type' => 'party_type',
            'party_type' => 'party_type',
            'party group' => 'party_group',
            'party_group' => 'party_group',
            'phone number 2' => 'phone_number_2',
            'phone_number_2' => 'phone_number_2',
            'ptcl number' => 'ptcl_number',
            'ptcl_number' => 'ptcl_number',
            'billing address' => 'billing_address',
            'billing_address' => 'billing_address',
        ];

        return $map[$key] ?? str_replace([' ', '-'], '_', $key);
    }

    private function normalizeBoolean($value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $text = strtolower(trim((string) $value));
        if (in_array($text, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }

        if (in_array($text, ['0', 'false', 'no', 'off'], true)) {
            return false;
        }

        return null;
    }

    private function normalizeNumeric($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '' || $value === null) {
            return null;
        }

        return is_numeric($value) ? $value : null;
    }

    private function normalizeDateValue($value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $text = trim((string) $value);

        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $text, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $year = $matches[3];
            return "$year-$month-$day";
        }

        if (preg_match('/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/', $text, $matches)) {
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $day = str_pad($matches[3], 2, '0', STR_PAD_LEFT);
            return "$matches[1]-$month-$day";
        }

        try {
            $date = \Carbon\Carbon::parse($text);
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function normalizePartyTypeString(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $types = preg_split('/[;,|]+/', strtolower(trim($value)));
        $types = array_filter(array_map('trim', $types));
        $allowed = ['customer', 'supplier', 'broker'];
        $normalized = array_values(array_unique(array_filter($types, fn ($type) => in_array($type, $allowed, true))));

        return $normalized ? implode(',', $normalized) : null;
    }

    // Show single party
  public function show($id)
{
    // Eager load transactions
    $party = Party::with(['transactions', 'sales'])->findOrFail($id);

    // Format transactions
    $transactions = $party->transactions
        ->sortByDesc('date')
        ->map(function ($txn) {
            return [
                'id'      => $txn->id,
                'type'    => $txn->type,
                'number'  => $txn->number,
                'date'    => $txn->date->format('d/m/Y'),
                'total'   => number_format($txn->total, 2),
                'balance' => number_format($txn->balance, 2),
                'status'  => $txn->status,
            ];
        });

    return response()->json([
        'success'       => true,
        'party'         => $party,
        'transactions'  => $transactions,
        'total_balance' => number_format((float) $party->current_balance, 2),
    ]);
}

    // Update existing party
public function update(Request $request, $id)
{
    $party = Party::findOrFail($id);

    if (!$party->is_active) {
        return response()->json([
            'success' => false,
            'message' => 'Inactive party cannot be edited.',
        ], 423);
    }

    $data = $request->validate([
        'name' => 'sometimes|string|max:255',
        'phone' => 'sometimes|nullable|string|max:20',
        'phone_number_2' => 'sometimes|nullable|string|max:20',
        'ptcl_number' => 'sometimes|nullable|string|max:30',
        'email' => 'sometimes|nullable|email|max:255',
        'city' => 'sometimes|nullable|string|max:100',
        'address' => 'sometimes|nullable|string|max:1000',
        'billing_address' => 'sometimes|nullable|string',
        'shipping_address' => 'sometimes|nullable|string',
        'opening_balance' => 'sometimes|nullable|numeric',
        'as_of_date' => 'sometimes|nullable|date',
        'credit_limit_enabled' => 'sometimes|nullable|boolean',
        'credit_limit_amount' => 'sometimes|nullable|numeric|min:0|required_if:credit_limit_enabled,1',
        'due_days' => 'sometimes|nullable|integer|min:1|max:100',
        'custom_fields' => 'sometimes|nullable|array',
        'custom_fields.*' => 'nullable|string|max:255',
        'transaction_type' => 'sometimes|nullable|in:receive,pay',
        'party_type' => 'sometimes|nullable|array',
        'party_type.*' => 'in:customer,supplier,broker',
        'party_group' => 'sometimes|nullable|string|max:100',
        'is_active' => 'sometimes|nullable|boolean',
    ]);
    if (array_key_exists('party_type', $data)) {
        $data['party_type'] = $this->normalizePartyType($data['party_type'] ?? []);
    }
    if (array_key_exists('credit_limit_enabled', $data) && empty($data['credit_limit_enabled'])) {
        $data['credit_limit_amount'] = null;
    }
    if (array_key_exists('custom_fields', $data)) {
        $data['custom_fields'] = $this->normalizePartyCustomFields($data['custom_fields']);
    }

    $party->update($data);

    if (!empty($data['party_group'])) {
        PartyGroup::firstOrCreate(['name' => trim($data['party_group'])]);
    }

    // ✅ Transaction update
    $openingTransaction = Transaction::query()
        ->where('party_id', $party->id)
        ->whereNull('transfer_group')
        ->where(function ($query) {
            $query->whereIn('type', ['receive', 'pay'])
                ->orWhere('number', 'like', 'TXN%');
        })
        ->orderBy('id')
        ->first();

    if ($openingTransaction) {
        $openingTransaction->update([
            'type' => $request->input('transaction_type', $openingTransaction->type),
            'date' => $request->input('as_of_date', $openingTransaction->date),
            'total' => $request->input('opening_balance', $openingTransaction->total),
            'debit' => $request->input('transaction_type', $openingTransaction->type) === 'receive'
                ? $request->input('opening_balance', $openingTransaction->total)
                : 0,
            'credit' => $request->input('transaction_type', $openingTransaction->type) === 'pay'
                ? $request->input('opening_balance', $openingTransaction->total)
                : 0,
            'balance' => $request->input('opening_balance', $openingTransaction->balance),
            'running_balance' => $request->input('opening_balance', $openingTransaction->running_balance ?? $openingTransaction->balance),
            'status' => $request->input('transaction_type', $openingTransaction->status),
        ]);
    } elseif ($request->filled('opening_balance') || $request->filled('transaction_type')) {
        Transaction::create([
            'party_id' => $party->id,
            'type' => $request->input('transaction_type'),
            'number' => 'TXN' . time(),
            'date' => $request->input('as_of_date') ?? now(),
            'total' => $request->input('opening_balance') ?? 0,
            'debit' => $request->input('transaction_type') === 'receive' ? ($request->input('opening_balance') ?? 0) : 0,
            'credit' => $request->input('transaction_type') === 'pay' ? ($request->input('opening_balance') ?? 0) : 0,
            'balance' => $request->input('opening_balance') ?? 0,
            'running_balance' => $request->input('opening_balance') ?? 0,
            'status' => $request->input('transaction_type') ?? 'unpaid',
        ]);
    }

    $this->syncPartyCurrentBalance($party->id);

    $party = $party->fresh();
    $party->load(['transactions', 'sales']);

    $transactions = $party->transactions
        ->sortByDesc('date')
        ->map(function ($txn) {
            return [
                'id'      => $txn->id,
                'type'    => $txn->type,
                'number'  => $txn->number,
                'date'    => $txn->date->format('d/m/Y'),
                'total'   => number_format($txn->total, 2),
                'balance' => number_format($txn->balance, 2),
                'status'  => $txn->status,
            ];
        });

    return response()->json([
        'success'       => true,
        'party'         => $party,
        'transactions'  => $transactions,
        'total_balance' => number_format((float) $party->current_balance, 2),
    ]);
}

    public function moveGroups(Request $request)
    {
        $data = $request->validate([
            'party_ids' => 'required|array|min:1',
            'party_ids.*' => 'required|exists:parties,id',
            'party_group' => 'nullable|string|max:100',
        ]);

        $groupName = trim((string) ($data['party_group'] ?? ''));

        Party::whereIn('id', $data['party_ids'])->update([
            'party_group' => $groupName !== '' ? $groupName : null,
        ]);

        if ($groupName !== '') {
            PartyGroup::firstOrCreate(['name' => $groupName]);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'party_grouping' => 'nullable|boolean',
            'shipping_address' => 'nullable|boolean',
            'print_shipping_address' => 'nullable|boolean',
            'party_status' => 'nullable|boolean',
            'payment_reminder' => 'nullable|boolean',
            'payment_reminder_days' => 'nullable|integer|min:1|max:365',
            'payment_reminder_message' => 'nullable|string|max:5000',
            'additional_field_1' => 'nullable|boolean',
            'additional_field_1_name' => 'nullable|string|max:100',
            'additional_field_1_print' => 'nullable|boolean',
            'additional_field_2' => 'nullable|boolean',
            'additional_field_2_name' => 'nullable|string|max:100',
            'additional_field_2_print' => 'nullable|boolean',
        ]);

        $settings = $this->getPartySettings();
        $settings['party_grouping'] = array_key_exists('party_grouping', $data) ? (bool) $data['party_grouping'] : $settings['party_grouping'];
        $settings['shipping_address'] = array_key_exists('shipping_address', $data) ? (bool) $data['shipping_address'] : $settings['shipping_address'];
        $settings['print_shipping_address'] = array_key_exists('print_shipping_address', $data) ? (bool) $data['print_shipping_address'] : $settings['print_shipping_address'];
        $settings['party_status'] = array_key_exists('party_status', $data) ? (bool) $data['party_status'] : $settings['party_status'];
        $settings['payment_reminder'] = array_key_exists('payment_reminder', $data) ? (bool) $data['payment_reminder'] : $settings['payment_reminder'];
        $settings['payment_reminder_days'] = array_key_exists('payment_reminder_days', $data) ? (int) $data['payment_reminder_days'] : $settings['payment_reminder_days'];
        $settings['payment_reminder_message'] = trim((string) ($data['payment_reminder_message'] ?? $settings['payment_reminder_message']));
        $settings['additional_field_1'] = array_key_exists('additional_field_1', $data) ? (bool) $data['additional_field_1'] : $settings['additional_field_1'];
        $settings['additional_field_1_name'] = (string) ($data['additional_field_1_name'] ?? $settings['additional_field_1_name']);
        $settings['additional_field_1_print'] = array_key_exists('additional_field_1_print', $data) ? (bool) $data['additional_field_1_print'] : $settings['additional_field_1_print'];
        $settings['additional_field_2'] = array_key_exists('additional_field_2', $data) ? (bool) $data['additional_field_2'] : $settings['additional_field_2'];
        $settings['additional_field_2_name'] = (string) ($data['additional_field_2_name'] ?? $settings['additional_field_2_name']);
        $settings['additional_field_2_print'] = array_key_exists('additional_field_2_print', $data) ? (bool) $data['additional_field_2_print'] : $settings['additional_field_2_print'];

        AppSetting::setValue('party_grouping', $settings['party_grouping'] ? '1' : '0');
        AppSetting::setValue('shipping_address', $settings['shipping_address'] ? '1' : '0');
        AppSetting::setValue('print_shipping_address', $settings['print_shipping_address'] ? '1' : '0');
        AppSetting::setValue('party_status', $settings['party_status'] ? '1' : '0');
        AppSetting::setValue('payment_reminder', $settings['payment_reminder'] ? '1' : '0');
        AppSetting::setValue('payment_reminder_days', (string) max(1, (int) $settings['payment_reminder_days']));
        AppSetting::setValue('payment_reminder_message', $settings['payment_reminder_message']);
        AppSetting::setValue('party_additional_field_1', $settings['additional_field_1'] ? '1' : '0');
        AppSetting::setValue('party_additional_field_1_name', $settings['additional_field_1_name']);
        AppSetting::setValue('party_additional_field_1_print', $settings['additional_field_1_print'] ? '1' : '0');
        AppSetting::setValue('party_additional_field_2', $settings['additional_field_2'] ? '1' : '0');
        AppSetting::setValue('party_additional_field_2_name', $settings['additional_field_2_name']);
        AppSetting::setValue('party_additional_field_2_print', $settings['additional_field_2_print'] ? '1' : '0');

        return response()->json([
            'success' => true,
            'settings' => $settings,
        ]);
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

    public function storeReminder(Request $request, Party $party)
    {
        $data = $request->validate([
            'enabled' => 'required|boolean',
            'phone' => 'nullable|string|max:30',
            'reminder_date' => 'nullable|date',
            'message' => 'nullable|string|max:5000',
        ]);

        $party->forceFill([
            'payment_reminder_enabled' => (bool) $data['enabled'],
            'payment_reminder_phone' => trim((string) ($data['phone'] ?? '')),
            'payment_reminder_date' => $data['reminder_date'] ?? null,
            'payment_reminder_message' => trim((string) ($data['message'] ?? '')),
            'payment_reminder_sent_at' => null,
        ])->save();

        return response()->json([
            'success' => true,
            'reminder' => [
                'enabled' => (bool) $party->payment_reminder_enabled,
                'phone' => (string) ($party->payment_reminder_phone ?? ''),
                'reminder_date' => optional($party->payment_reminder_date)->format('Y-m-d'),
                'message' => (string) ($party->payment_reminder_message ?? ''),
            ],
        ]);
    }

    public function reminderNotifications()
    {
        $reminders = Party::query()
            ->where('payment_reminder_enabled', true)
            ->whereNotNull('payment_reminder_date')
            ->whereDate('payment_reminder_date', '<=', today())
            ->orderBy('payment_reminder_date')
            ->get(['id', 'name', 'phone', 'current_balance', 'payment_reminder_date', 'payment_reminder_sent_at']);

        return response()->json([
            'success' => true,
            'items' => $reminders->map(function (Party $party) {
                return [
                    'id' => $party->id,
                    'name' => $party->name,
                    'phone' => $party->payment_reminder_phone ?: $party->phone,
                    'amount' => (float) $party->current_balance,
                    'reminder_date' => optional($party->payment_reminder_date)->format('Y-m-d'),
                    'sent_at' => optional($party->payment_reminder_sent_at)->toDateTimeString(),
                    'whatsapp_url' => ($party->payment_reminder_phone ?: $party->phone) ? 'https://wa.me/' . preg_replace('/\D+/', '', (string) ($party->payment_reminder_phone ?: $party->phone)) : '',
                ];
            })->values(),
        ]);
    }

    public function statusList()
    {
        $parties = Party::query()
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'is_active']);

        return response()->json([
            'success' => true,
            'parties' => $parties->map(function (Party $party) {
                return [
                    'id' => $party->id,
                    'name' => $party->name,
                    'phone' => $party->phone,
                    'is_active' => (bool) $party->is_active,
                ];
            })->values(),
        ]);
    }

    public function updateStatuses(Request $request)
    {
        $data = $request->validate([
            'parties' => 'required|array|min:1',
            'parties.*.id' => 'required|integer|exists:parties,id',
            'parties.*.is_active' => 'required|boolean',
        ]);

        foreach ($data['parties'] as $entry) {
            Party::query()
                ->whereKey($entry['id'])
                ->update(['is_active' => (bool) $entry['is_active']]);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    // Delete party
    public function destroy($id)
    {
        $party = Party::findOrFail($id);
        $party->delete();

        return response()->json(['success' => true]);
    }

    private function normalizePartyType(array $partyTypes): ?string
    {
        $allowedTypes = ['customer', 'supplier', 'broker'];

        $normalizedTypes = collect($partyTypes)
            ->filter(fn ($type) => in_array($type, $allowedTypes, true))
            ->unique()
            ->values()
            ->all();

        return $normalizedTypes ? implode(',', $normalizedTypes) : null;
    }

public function transactions(Party $party)
{
    if (!$party->is_active) {
        return response()->json([
            'success' => false,
            'inactive' => true,
            'message' => 'Inactive party transactions cannot be viewed.',
        ], 423);
    }

    $statement = $this->buildPartyStatementData(
        $party,
        request('from'),
        request('to')
    );

    return response()->json([
        'success' => true,
        'transactions' => $statement['transactions'],
        'party_name' => $party->name,
        'total_balance' => $statement['total_balance'],
        'overdue_transactions' => collect($statement['transactions'])
            ->filter(fn ($entry) => (float) str_replace(',', '', (string) ($entry['running_balance'] ?? 0)) > 0 && !empty($entry['due_date']) && $entry['due_date'] < now()->toDateString())
            ->values(),
    ]);
}

public function statementPdf(Request $request, Party $party)
{
    if (!$party->is_active) {
        abort(423, 'Inactive party statement is not available.');
    }

    $statement = $this->buildPartyStatementData(
        $party,
        $request->query('from'),
        $request->query('to')
    );

    $from = $request->query('from');
    $to = $request->query('to');

    $options = [
        'item_details' => $request->boolean('item_details'),
        'description' => $request->boolean('description'),
        'payment_status' => $request->boolean('payment_status'),
        'payment_information' => $request->boolean('payment_information'),
    ];

    $pdf = Pdf::loadView('parties.statement-pdf', [
        'party' => $party->fresh(),
        'transactions' => $statement['transactions'],
        'dateFrom' => $from,
        'dateTo' => $to,
        'options' => $options,
        'summary' => $statement['summary'],
    ])->setPaper('a4', 'portrait');

    $fileName = Str::slug($party->name ?: 'party') . '_' . ($from ?: 'start') . '_to_' . ($to ?: 'end') . '.pdf';

    if ($request->boolean('download')) {
        return $pdf->download($fileName);
    }

    return $pdf->stream($fileName);
}

public function statementEmail(Request $request, Party $party)
{
    if (!$party->is_active) {
        abort(423, 'Inactive party statement is not available.');
    }

    $data = $request->validate([
        'email' => 'required|email',
        'subject' => 'nullable|string|max:255',
        'message' => 'nullable|string|max:5000',
    ]);

    $statement = $this->buildPartyStatementData(
        $party,
        $request->query('from'),
        $request->query('to')
    );

    $options = [
        'item_details' => $request->boolean('item_details'),
        'description' => $request->boolean('description'),
        'payment_status' => $request->boolean('payment_status'),
        'payment_information' => $request->boolean('payment_information'),
    ];

    $pdf = Pdf::loadView('parties.statement-pdf', [
        'party' => $party->fresh(),
        'transactions' => $statement['transactions'],
        'dateFrom' => $request->query('from'),
        'dateTo' => $request->query('to'),
        'options' => $options,
        'summary' => $statement['summary'],
    ])->setPaper('a4', 'portrait');

    $subject = trim((string) ($data['subject'] ?? ''));
    if ($subject === '') {
        $subject = 'Party Statement - ' . ($party->name ?: 'Party');
    }

    $message = trim((string) ($data['message'] ?? ''));
    if ($message === '') {
        $statementUrl = route('parties.statement-pdf', array_filter([
            'party' => $party->id,
            'from' => $request->query('from'),
            'to' => $request->query('to'),
        ]));
        $message = "Dear {$party->name},\n\nPlease find the party statement attached below.\nPDF Link: {$statementUrl}\n\nThank you for doing business with us.\nThanks and regards.";
    }

    $fileName = Str::slug($party->name ?: 'party') . '_statement.pdf';

    try {
        Mail::raw($message, function ($mail) use ($data, $subject, $pdf, $fileName) {
            $mail->to($data['email'])
                ->subject($subject)
                ->attachData($pdf->output(), $fileName, ['mime' => 'application/pdf']);

            $fromAddress = config('mail.from.address');
            if (!empty($fromAddress)) {
                $mail->from($fromAddress, config('mail.from.name'));
            }
        });
    } catch (\Throwable $e) {
        report($e);

        return response()->json([
            'success' => false,
            'message' => 'Unable to send email right now.',
        ], 500);
    }

    return response()->json([
        'success' => true,
        'message' => 'Email sent successfully.',
    ]);
}

private function buildPartyStatementData(Party $party, ?string $from = null, ?string $to = null): array
{
    $this->syncPartyCurrentBalance($party->id);
    $party->refresh();
    $party->loadMissing([
        'transactions.counterParty',
        'sales.items',
        'sales.payments.bankAccount',
        'sales.party',
        'purchases',
    ]);

    $fromDate = $from ? \Illuminate\Support\Carbon::parse($from)->startOfDay() : null;
    $toDate = $to ? \Illuminate\Support\Carbon::parse($to)->endOfDay() : null;

    $salesTransactions = $party->sales
        ->map(function (Sale $sale) {
            $rawType = strtolower((string) $sale->type);
            $amount = (float) ($sale->grand_total ?? $sale->total_amount ?? 0);
            $effect = $rawType === 'sale_return' ? -1 * $amount : $amount;
            $typeLabel = match ($rawType) {
                'invoice', 'pos' => 'Sale',
                'sale_return' => 'Sale Return',
                'sale_order' => 'Sale Order',
                'proforma' => 'Proforma Invoice',
                'delivery_challan' => 'Delivery Challan',
                default => ucwords(str_replace('_', ' ', $rawType)),
            };

            return [
                'id' => 'sale-' . $sale->id,
                'type' => $typeLabel,
                'raw_type' => (string) $sale->type,
                'source' => 'sale',
                'number' => $sale->bill_number ?: (string) $sale->id,
                'date' => !empty($sale->invoice_date)
                    ? \Illuminate\Support\Carbon::parse($sale->invoice_date)
                    : (!empty($sale->created_at) ? \Illuminate\Support\Carbon::parse($sale->created_at) : null),
                'description' => (string) ($sale->description ?? ''),
                'debit' => $effect > 0 ? $effect : 0,
                'credit' => $effect < 0 ? abs($effect) : 0,
                'effect' => $effect,
                'row_balance' => (float) ($sale->balance ?? 0),
                'display_total' => $amount,
                'due_date' => !empty($sale->due_date) ? \Illuminate\Support\Carbon::parse($sale->due_date) : null,
                'status' => (string) ($sale->status ?? ''),
                'received_amount' => (float) ($sale->received_amount ?? 0),
                'row_left_balance' => (float) ($sale->balance ?? 0),
                'item_details' => $sale->items->map(function ($item) use ($amount) {
                    return [
                        'name' => (string) ($item->item_name ?: 'Item'),
                        'tadaat' => (float) ($item->quantity ?? 0),
                        'net_w' => (float) ($item->net_w ?? 0),
                        'unit' => (string) ($item->unit ?? ''),
                        'price' => (float) ($item->unit_price ?? 0),
                        'amount' => (float) ($item->amount ?? 0),
                        'grand_total' => $amount,
                    ];
                })->values()->all(),
                'payment_information' => $sale->payments->map(function ($payment) {
                    return [
                        'payment_type' => (string) ($payment->payment_type ?: '-'),
                        'bank_name' => (string) ($payment->bankAccount?->display_name ?: ($payment->bankAccount?->bank_name ?: 'Cash')),
                        'amount' => (float) ($payment->amount ?? 0),
                        'reference' => (string) ($payment->reference ?: '-'),
                    ];
                })->values()->all(),
                'sort_order' => 20,
                'actions' => $this->saleActionUrls($sale),
            ];
        });

    $purchaseTransactions = Purchase::query()
        ->where('party_id', $party->id)
        ->get()
        ->map(function ($purchase) {
            $amount = (float) ($purchase->grand_total ?? $purchase->total_amount ?? 0);
            $date = $purchase->bill_date ?? $purchase->due_date ?? $purchase->created_at;
            $effect = $purchase->type === 'purchase_return' ? $amount : -1 * $amount;

            return [
                'id' => 'purchase-' . $purchase->id,
                'type' => $purchase->type === 'purchase_return' ? 'Purchase Return' : 'Purchase',
                'raw_type' => (string) $purchase->type,
                'source' => 'purchase',
                'number' => $purchase->bill_number ?: (string) $purchase->id,
                'date' => optional($date),
                'description' => (string) ($purchase->description ?? ''),
                'debit' => $effect > 0 ? $effect : 0,
                'credit' => $effect < 0 ? abs($effect) : 0,
                'effect' => $effect,
                'row_balance' => (float) ($purchase->balance ?? 0),
                'due_date' => optional($purchase->due_date),
                'status' => (string) ($purchase->balance > 0 ? 'unpaid' : 'paid'),
                'sort_order' => 30,
                'actions' => $this->purchaseActionUrls($purchase),
            ];
        });

    $manualLedgerTransactions = $party->transactions
        ->reject(function ($txn) {
            return in_array(strtolower((string) $txn->type), [
                'sale',
                'invoice',
                'pos',
                'sale_return',
                'purchase',
                'purchase_return',
            ], true);
        })
        ->map(function ($txn) {
        $effect = $txn->ledgerEffectValue();
        $rawType = strtolower((string) $txn->type);
        $txnNumber = strtoupper(trim((string) ($txn->number ?? '')));
        $isOpeningBalance = in_array($rawType, ['receive', 'pay'], true)
            && str_starts_with($txnNumber, 'TXN');
        $isExpensePayment = $rawType === 'payment_out'
            && str_starts_with(strtolower(trim((string) ($txn->description ?? ''))), 'expense:');
        $isSaleAdjustmentTransfer = in_array($rawType, ['party to party[received]', 'party to party[paid]'], true)
            && str_starts_with((string) ($txn->transfer_group ?? ''), 'sale-ledger-');

        return [
            'id' => 'txn-' . $txn->id,
            'type' => $isSaleAdjustmentTransfer
                ? ''
                : ($isExpensePayment
                    ? 'Expense'
                    : ($isOpeningBalance
                        ? $this->formatLedgerTypeLabel((string) $txn->type)
                        : match ($rawType) {
                            'receive' => 'Payment In',
                            'pay' => 'Payment Out',
                            default => $this->formatLedgerTypeLabel((string) $txn->type),
                        })),
            'raw_type' => (string) $txn->type,
            'source' => !empty($txn->transfer_group) ? 'transfer' : 'ledger',
            'number' => $txn->number ?: '-',
            'date' => optional($txn->date),
            'description' => (string) ($txn->description ?? ''),
            'debit' => $effect > 0 ? $effect : 0,
            'credit' => $effect < 0 ? abs($effect) : 0,
            'effect' => $effect,
            'row_balance' => (float) ($txn->total ?? 0),
            'due_date' => optional($txn->due_date),
            'status' => (string) ($txn->status ?? $txn->type ?? ''),
            'status_display' => $isSaleAdjustmentTransfer ? (string) ($txn->description ?? '-') : null,
            'counter_party_name' => $txn->counterParty?->name,
            'received_amount' => 0,
            'row_left_balance' => (float) ($txn->total ?? 0),
            'item_details' => [],
            'payment_information' => [],
            'sort_order' => in_array(strtolower((string) $txn->type), ['receive', 'pay'], true) ? 10 : 40,
            'actions' => [],
        ];
    })
    ->values();

    $seenOpeningBalanceRow = false;
    $manualLedgerTransactions = $manualLedgerTransactions->filter(function (array $entry) use (&$seenOpeningBalanceRow) {
        $rawType = strtolower((string) ($entry['raw_type'] ?? ''));
        $number = strtoupper(trim((string) ($entry['number'] ?? '')));
        $isOpeningBalance = in_array($rawType, ['receive', 'pay'], true)
            && str_starts_with($number, 'TXN')
            && (($entry['source'] ?? '') === 'ledger');

        if (!$isOpeningBalance) {
            return true;
        }

        if ($seenOpeningBalanceRow) {
            return false;
        }

        $seenOpeningBalanceRow = true;
        return true;
    })->values();

    $transactions = $salesTransactions
        ->concat($purchaseTransactions)
        ->concat($manualLedgerTransactions)
        ->sortBy(function ($entry) {
            return sprintf(
                '%012d-%03d-%s',
                (int) ($entry['date']?->timestamp ?? 0),
                (int) ($entry['sort_order'] ?? 999),
                (string) $entry['id']
            );
        })
        ->values();

    $openingBalanceSeen = false;
    $transactions = $transactions->filter(function (array $entry) use (&$openingBalanceSeen) {
        $rawType = strtolower((string) ($entry['raw_type'] ?? ''));
        $typeLabel = (string) ($entry['type'] ?? '');
        $number = strtoupper(trim((string) ($entry['number'] ?? '')));
        $isOpeningBalance = in_array($rawType, ['receive', 'pay'], true)
            && str_starts_with($number, 'TXN');

        if (!$isOpeningBalance) {
            return true;
        }

        if ($openingBalanceSeen) {
            return false;
        }

        $openingBalanceSeen = true;
        return true;
    })->values();

    $runningBalance = 0.0;
    $transactions = $transactions->map(function ($entry) use (&$runningBalance, $fromDate, $toDate) {
        $runningBalance += (float) ($entry['effect'] ?? 0);
        $entryDate = $entry['date'];

        if (($fromDate && (!$entryDate || $entryDate->lt($fromDate))) || ($toDate && (!$entryDate || $entryDate->gt($toDate)))) {
            return null;
        }

        $entry['date'] = $entry['date']?->format('d/m/Y');
        $entry['due_date'] = $entry['due_date']?->format('Y-m-d');
        $entry['total'] = number_format((float) ($entry['display_total'] ?? (($entry['debit'] ?? 0) + ($entry['credit'] ?? 0))), 2);
        $entry['balance'] = number_format((float) ($entry['row_balance'] ?? 0), 2);
        $entry['debit'] = number_format((float) ($entry['debit'] ?? 0), 2);
        $entry['credit'] = number_format((float) ($entry['credit'] ?? 0), 2);
        $entry['running_balance'] = number_format($runningBalance, 2);
        $entry['payment_status_text'] = 'Status: ' . ((string) ($entry['status'] ?: 'Open'))
            . ' | Paid: Rs ' . number_format((float) ($entry['received_amount'] ?? 0), 2)
            . ' | Left: Rs ' . number_format((float) ($entry['row_left_balance'] ?? 0), 2);
        unset($entry['effect'], $entry['row_balance'], $entry['sort_order'], $entry['display_total']);
        return $entry;
    })->filter()->values();

    $closingBalance = $transactions->isNotEmpty()
        ? (string) ($transactions->last()['running_balance'] ?? number_format((float) $party->current_balance, 2))
        : number_format((float) $party->current_balance, 2);

    $summary = [
        'total_debit' => number_format($transactions->sum(fn ($entry) => (float) str_replace(',', '', (string) ($entry['debit'] ?? 0))), 2),
        'total_credit' => number_format($transactions->sum(fn ($entry) => (float) str_replace(',', '', (string) ($entry['credit'] ?? 0))), 2),
        'closing_balance' => $closingBalance,
    ];

    return [
        'transactions' => $transactions->values()->all(),
        'total_balance' => number_format((float) $party->current_balance, 2),
        'summary' => $summary,
    ];
}

public function transferHistory(Party $party)
{
    $transfers = $party->transactions()
        ->with('counterParty')
        ->whereNotNull('transfer_group')
        ->where('transfer_group', 'like', 'PTP-%')
        ->orderByDesc('date')
        ->orderByDesc('id')
        ->get()
        ->map(function (Transaction $transaction) {
            return [
                'id' => $transaction->id,
                'date' => optional($transaction->date)?->format('d-M-Y'),
                'ref_no' => $transaction->number ?: '-',
                'type' => (string) $transaction->type,
                'counter_party' => $transaction->counterParty?->name ?: '-',
                'amount' => number_format((float) ($transaction->total ?? 0), 2),
                'description' => (string) ($transaction->description ?? '-'),
                'status' => (string) ($transaction->status ?? '-'),
            ];
        })
        ->values();

    return response()->json([
        'success' => true,
        'party_name' => $party->name,
        'transfers' => $transfers,
    ]);
}

public function ledger(Party $party)
{
    $party->loadMissing(['transactions.counterParty']);

    $runningBalance = 0.0;

    $ledger = $party->transactions()
        ->whereNull('transfer_group')
        ->whereIn('type', ['sale', 'sale_return', 'payment_in', 'payment_out', 'receive', 'pay'])
        ->orderBy('date')
        ->orderBy('id')
        ->get()
        ->map(function (Transaction $transaction) use (&$runningBalance) {
            $credit = (float) $transaction->ledgerCreditValue();
            $debit = (float) $transaction->ledgerDebitValue();
            $runningBalance += Transaction::normalizeLedgerAmount($debit);
            $runningBalance -= Transaction::normalizeLedgerAmount($credit);
            $runningBalance = Transaction::normalizeLedgerAmount($runningBalance);
            $rawType = strtolower((string) $transaction->type);
            $isExpensePayment = $rawType === 'payment_out'
                && str_starts_with(strtolower(trim((string) ($transaction->description ?? ''))), 'expense:');

            return [
                'id' => $transaction->id,
                'number' => $transaction->number ?: '-',
                'date' => optional($transaction->date)?->format('d-M-Y'),
                'type' => $isExpensePayment ? 'Expense' : $this->formatLedgerTypeLabel((string) $transaction->type),
                'description' => (string) ($transaction->description ?: ($transaction->counterParty?->name ? 'Counter Party: ' . $transaction->counterParty->name : '')),
                'credit' => number_format($credit, 2),
                'debit' => number_format($debit, 2),
                'balance' => number_format($runningBalance, 2),
                'running_balance' => number_format($runningBalance, 2),
            ];
        })
        ->values();

    return response()->json([
        'success' => true,
        'party_name' => $party->name,
        'ledger' => $ledger,
    ]);
}

private function formatLedgerTypeLabel(string $type): string
{
    return match (strtolower($type)) {
        'pay' => 'Payable Opening Balance',
        'receive' => 'Receivable Opening Balance',
        'payment_in' => 'Payment In',
        'payment_out' => 'Payment Out',
        'party to party[received]' => 'Party to Party[Received]',
        'party to party[paid]' => 'Party to Party[Paid]',
        default => ucwords(str_replace(['_', '-'], ' ', $type)),
    };
}

private function buildLedgerSourceKey(string $type, string $number): string
{
    return strtolower(trim($type)) . '|' . strtolower(trim($number));
}

    private function saleActionUrls(Sale $sale): array
    {
    $modalPreviewUrl = route('sale.invoice-preview', $sale);
    $modalPdfUrl = route('sale.invoice-pdf', ['sale' => $sale->id, 'inline' => 1]);
    $modalPrintUrl = route('sale.invoice-preview', ['sale' => $sale->id, 'print' => 1]);
    $modalDeliveryPreviewUrl = route('sale.invoice-preview', ['sale' => $sale->id, 'doc' => 'delivery_challan']);
    $modalDeliveryPdfUrl = route('sale.invoice-pdf', ['sale' => $sale->id, 'doc' => 'delivery_challan', 'inline' => 1]);
    $modalDeliveryPrintUrl = route('sale.invoice-preview', ['sale' => $sale->id, 'doc' => 'delivery_challan', 'print' => 1]);

    return match ($sale->type) {
        'invoice', 'pos' => [
            'view' => route('sale.edit', $sale),
            'delete' => route('sale.destroy', $sale),
            'cancel' => route('sale.cancel', $sale),
            'duplicate' => route('sale.create', ['type' => $sale->type === 'pos' ? 'pos' : 'invoice']) . '?duplicate_sale_id=' . $sale->id,
            'pdf' => $modalPdfUrl,
            'preview' => $modalPreviewUrl,
            'print' => $modalPrintUrl,
            'preview_delivery' => $modalDeliveryPreviewUrl,
            'convert_return' => route('sale-return.create', ['sale_id' => $sale->id]),
            'history' => route('sale.bank-history', $sale),
        ],
        'estimate' => [
            'view' => route('estimates.create') . '?edit_sale_id=' . $sale->id,
            'delete' => route('estimates.destroy', $sale),
            'cancel' => null,
            'duplicate' => route('estimates.create') . '?duplicate_sale_id=' . $sale->id,
            'pdf' => $modalPdfUrl,
            'preview' => $modalPreviewUrl,
            'print' => $modalPrintUrl,
            'preview_delivery' => null,
            'convert_return' => null,
            'history' => null,
        ],
        'sale_return' => [
            'view' => route('sale-return.edit', $sale),
            'delete' => route('sale-return.destroy', $sale),
            'cancel' => null,
            'duplicate' => route('sale-return.duplicate', $sale),
            'pdf' => $modalPdfUrl,
            'preview' => $modalPreviewUrl,
            'print' => $modalPrintUrl,
            'preview_delivery' => null,
            'convert_return' => null,
            'history' => null,
        ],
        'proforma' => [
            'view' => route('proforma-invoice.edit', $sale),
            'delete' => route('proforma-invoice.destroy', $sale),
            'cancel' => null,
            'duplicate' => route('proforma-invoice.duplicate', $sale),
            'pdf' => $modalPdfUrl,
            'preview' => $modalPreviewUrl,
            'print' => $modalPrintUrl,
            'preview_delivery' => null,
            'convert_return' => null,
            'history' => null,
        ],
        'delivery_challan' => [
            'view' => route('delivery-challan.edit', $sale),
            'delete' => route('delivery-challan.destroy', $sale),
            'cancel' => null,
            'duplicate' => route('delivery-challan.duplicate', $sale),
            'pdf' => $modalDeliveryPdfUrl,
            'preview' => $modalDeliveryPreviewUrl,
            'print' => $modalDeliveryPrintUrl,
            'preview_delivery' => $modalDeliveryPreviewUrl,
            'convert_return' => null,
            'history' => null,
        ],
        'sale_order' => [
            'view' => route('sale-order.create') . '?edit_sale_id=' . $sale->id,
            'delete' => null,
            'cancel' => null,
            'duplicate' => route('sale-order.create') . '?duplicate_sale_id=' . $sale->id,
            'pdf' => $modalPdfUrl,
            'preview' => $modalPreviewUrl,
            'print' => $modalPrintUrl,
            'preview_delivery' => null,
            'convert_return' => null,
            'history' => null,
        ],
        default => [
            'view' => route('sale.edit', $sale),
            'delete' => route('sale.destroy', $sale),
            'cancel' => null,
            'duplicate' => null,
            'pdf' => null,
            'preview' => null,
            'print' => null,
            'preview_delivery' => null,
            'convert_return' => null,
            'history' => null,
        ],
    };
}

private function purchaseActionUrls(Purchase $purchase): array
{
    return match ((string) $purchase->type) {
        'purchase_return' => [
            'view' => route('purchase-return.edit', $purchase),
            'delete' => route('purchase-return.destroy', $purchase),
            'cancel' => null,
            'duplicate' => route('purchase-return.duplicate', $purchase),
            'pdf' => route('purchase-return.pdf', $purchase),
            'preview' => route('purchase-return.preview', $purchase),
            'print' => route('purchase-return.print', $purchase),
            'preview_delivery' => null,
            'convert_return' => null,
            'history' => null,
        ],
        'purchase_order' => [
            'view' => route('purchase-orders.edit', $purchase),
            'delete' => route('purchase-orders.destroy', $purchase),
            'cancel' => null,
            'duplicate' => null,
            'pdf' => route('purchase-orders.pdf', $purchase),
            'preview' => route('purchase-orders.preview', $purchase),
            'print' => route('purchase-orders.print', $purchase),
            'preview_delivery' => null,
            'convert_return' => null,
            'history' => route('purchase-orders.history', $purchase),
        ],
        default => [
            'view' => route('purchase-bills.edit', $purchase),
            'delete' => route('purchase-bills.destroy', $purchase),
            'cancel' => null,
            'duplicate' => null,
            'pdf' => route('purchase-bills.pdf', $purchase),
            'preview' => route('purchase-bills.preview', $purchase),
            'print' => route('purchase-bills.print', $purchase),
            'preview_delivery' => null,
            'convert_return' => null,
            'history' => null,
        ],
    };
}

public function storeTransfer(Request $request)
{
    if (is_string($request->input('rows'))) {
        $request->merge([
            'rows' => json_decode($request->input('rows'), true) ?? [],
        ]);
    }

    $data = $request->validate([
        'transfer_date' => 'required|date',
        'description' => 'nullable|string',
        'attachment' => 'nullable|image|max:4096',
        'rows' => 'required|array|size:2',
        'rows.*.party_id' => 'required|exists:parties,id',
        'rows.*.type' => 'required|in:received,paid',
        'rows.*.amount' => 'required|numeric|min:0.01',
    ]);
    $paidRow = collect($data['rows'])->firstWhere('type', 'paid');
    $receivedRow = collect($data['rows'])->firstWhere('type', 'received');

    if (!$paidRow || !$receivedRow) {
        return response()->json([
            'success' => false,
            'message' => 'One paid row and one received row are required.',
        ], 422);
    }

    if ((int) $paidRow['party_id'] === (int) $receivedRow['party_id']) {
        return response()->json([
            'success' => false,
            'message' => 'Paid party and received party cannot be same.',
        ], 422);
    }

    if ((float) $paidRow['amount'] !== (float) $receivedRow['amount']) {
        return response()->json([
            'success' => false,
            'message' => 'Paid and received amount must be equal.',
        ], 422);
    }

    $sourceParty = Party::findOrFail($paidRow['party_id']);
    $targetParty = Party::findOrFail($receivedRow['party_id']);
    $amount = (float) $paidRow['amount'];
    $transferGroup = 'PTP-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(5));
    $savedRows = [];
    $attachmentPath = $request->hasFile('attachment')
        ? $request->file('attachment')->store('party-transfers', 'public')
        : null;

    DB::transaction(function () use ($data, $sourceParty, $targetParty, $amount, $transferGroup, $attachmentPath, &$savedRows) {
        $description = $data['description'] ?? null;
        $number = $transferGroup . '-1';

        $targetTransaction = Transaction::create([
            'party_id' => $targetParty->id,
            'counter_party_id' => $sourceParty->id,
            'type' => 'Party to Party[Received]',
            'number' => $number,
            'transfer_group' => $transferGroup,
            'date' => $data['transfer_date'],
            'total' => $amount,
            'debit' => $amount,
            'credit' => 0,
            'balance' => $amount,
            'running_balance' => $amount,
            'status' => 'receive',
            'description' => $description,
            'attachment' => $attachmentPath,
        ]);

        $sourceTransaction = Transaction::create([
            'party_id' => $sourceParty->id,
            'counter_party_id' => $targetParty->id,
            'type' => 'Party to Party[Paid]',
            'number' => $number,
            'transfer_group' => $transferGroup,
            'date' => $data['transfer_date'],
            'total' => $amount,
            'debit' => 0,
            'credit' => $amount,
            'balance' => $amount,
            'running_balance' => $amount,
            'status' => 'pay',
            'description' => $description,
            'attachment' => $attachmentPath,
        ]);

        $savedRows[] = [
            'row' => 1,
            'source_transaction_id' => $sourceTransaction->id,
            'target_transaction_id' => $targetTransaction->id,
            'paid_party_name' => $sourceParty->name,
            'received_party_name' => $targetParty->name,
            'amount' => number_format($amount, 2, '.', ''),
            'type' => 'cross_transfer',
        ];
    });

    $this->syncPartyCurrentBalance($sourceParty->id);
    $this->syncPartyCurrentBalance($targetParty->id);

    return response()->json([
        'success' => true,
        'message' => 'Party transfer saved successfully.',
        'transfer_group' => $transferGroup,
        'rows' => $savedRows,
    ]);
}

    private function syncPartyCurrentBalance(int $partyId): void
    {
        Transaction::syncPartyCurrentBalance($partyId);
    }

    private function normalizePartyCustomFields($fields): array
    {
        if (!is_array($fields)) {
            return [];
        }

        return collect($fields)
            ->map(function ($field) {
                if (is_array($field)) {
                    $field = $field['label'] ?? $field['value'] ?? $field['name'] ?? '';
                }

                return trim((string) $field);
            })
            ->filter(fn ($value) => $value !== '')
            ->values()
            ->all();
    }
}
