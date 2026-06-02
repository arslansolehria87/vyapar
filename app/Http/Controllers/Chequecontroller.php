<?php

namespace App\Http\Controllers;

use App\Models\Cheque;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChequeController extends Controller
{
    public function index()
    {
        $cheques = Cheque::orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

       $bankAccounts = BankAccount::orderBy('id')->get();

        $summary = [
            'total'     => $cheques->sum('amount'),
            'open'      => $cheques->where('status', 'open')->sum('amount'),
            'deposited' => $cheques->where('status', 'deposited')->sum('amount'),
            'bounced'   => $cheques->where('status', 'bounced')->sum('amount'),
        ];

        return view('dashboard.accounts.cheques', compact('cheques', 'bankAccounts', 'summary'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type'             => 'required|string|max:50',
            'name'             => 'required|string|max:255',
            'ref_no'           => 'nullable|string|max:100',
            'transaction_date' => 'required|date',
            'cheque_date'      => 'nullable|date',
            'amount'           => 'required|numeric|min:0.01',
            'status'           => 'nullable|in:open,deposited,bounced,cancelled',
            'bank_account_id'  => 'nullable|exists:bank_accounts,id',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $validated['status']     = $validated['status'] ?? 'open';
        $validated['created_by'] = Auth::id();

        $cheque = Cheque::create($validated);

        return response()->json(['success' => true, 'cheque' => $this->formatCheque($cheque)]);
    }

    public function show(Cheque $cheque): JsonResponse
    {
        return response()->json(['success' => true, 'cheque' => $this->formatCheque($cheque)]);
    }

    public function update(Request $request, Cheque $cheque): JsonResponse
    {
        $validated = $request->validate([
            'type'             => 'sometimes|string|max:50',
            'name'             => 'sometimes|string|max:255',
            'ref_no'           => 'nullable|string|max:100',
            'transaction_date' => 'sometimes|date',
            'cheque_date'      => 'nullable|date',
            'amount'           => 'sometimes|numeric|min:0.01',
            'status'           => 'nullable|in:open,deposited,bounced,cancelled',
            'bank_account_id'  => 'nullable|exists:bank_accounts,id',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $cheque->update($validated);

        return response()->json(['success' => true, 'cheque' => $this->formatCheque($cheque->fresh())]);
    }

    public function destroy(Cheque $cheque): JsonResponse
    {
        $cheque->delete();
        return response()->json(['success' => true, 'message' => 'Cheque deleted.']);
    }

    public function deposit(Request $request, Cheque $cheque): JsonResponse
    {
        if (!$cheque->isOpen()) {
            return response()->json(['success' => false, 'message' => 'Only open cheques can be deposited.'], 422);
        }

        $request->validate(['bank_account_id' => 'nullable|exists:bank_accounts,id']);

        DB::transaction(function () use ($cheque, $request) {
            $cashAccount = BankAccount::cashAccount();
            $amount = (float) $cheque->amount;

            $cheque->update([
                'status'          => 'deposited',
                'deposited_at'    => now(),
                'bank_account_id' => $request->bank_account_id ?? $cheque->bank_account_id,
            ]);

            $cashAccount->opening_balance = (float) ($cashAccount->opening_balance ?? 0) - $amount;
            $cashAccount->save();

            BankTransaction::create([
                'from_bank_account_id' => $cashAccount->id,
                'to_bank_account_id'   => null,
                'type'                 => 'cheque_payment',
                'amount'               => $amount,
                'transaction_date'     => $cheque->cheque_date ?? $cheque->transaction_date ?? now()->toDateString(),
                'reference_type'       => 'cheque',
                'reference_id'         => $cheque->id,
                'description'          => 'Cheque payment: ' . ($cheque->ref_no ?: $cheque->name),
                'meta'                 => [
                    'cheque_status' => 'deposited',
                    'party_name' => $cheque->name,
                    'cheque_ref_no' => $cheque->ref_no,
                ],
            ]);

            if ($cheque->bank_account_id) {
                try {
                    BankTransaction::create([
                        'to_bank_account_id' => $cheque->bank_account_id,
                        'type'               => 'cheque_deposit',
                        'amount'             => $amount,
                        'transaction_date'   => now()->toDateString(),
                        'description'        => 'Cheque deposit: ' . ($cheque->ref_no ?? $cheque->name),
                        'reference_id'       => $cheque->id,
                        'reference_type'     => 'cheque',
                    ]);
                } catch (\Exception $e) {
                    // skip if columns differ
                }
            }
        });

        return response()->json(['success' => true, 'cheque' => $this->formatCheque($cheque->fresh())]);
    }

    public function updateStatus(Request $request, Cheque $cheque): JsonResponse
    {
        $request->validate(['status' => 'required|in:open,bounced,cancelled']);
        $cheque->update(['status' => $request->status]);
        return response()->json(['success' => true, 'cheque' => $this->formatCheque($cheque->fresh())]);
    }

    public function history(Cheque $cheque): JsonResponse
    {
        $history = [];

        $history[] = ['action' => 'Created', 'created_at' => $cheque->created_at->format('d/m/Y H:i'), 'amount' => $cheque->amount];

        if ($cheque->deposited_at) {
            $history[] = ['action' => 'Deposited', 'created_at' => \Carbon\Carbon::parse($cheque->deposited_at)->format('d/m/Y H:i'), 'amount' => $cheque->amount];
        }

        if ($cheque->updated_at && $cheque->updated_at->ne($cheque->created_at)) {
            $history[] = ['action' => 'Updated', 'created_at' => $cheque->updated_at->format('d/m/Y H:i'), 'amount' => $cheque->amount];
        }

        return response()->json(['success' => true, 'history' => $history]);
    }

    private function formatCheque(Cheque $c): array
    {
        return [
            'id'               => $c->id,
            'type'             => $c->type,
            'name'             => $c->name,
            'ref_no'           => $c->ref_no,
            'transaction_date' => $c->transaction_date?->format('d/m/Y'),
            'cheque_date'      => $c->cheque_date?->format('d/m/Y'),
            'amount'           => $c->amount,
            'status'           => $c->status,
            'status_badge'     => $c->statusBadge(),
            'bank_account_id'  => $c->bank_account_id,
            'notes'            => $c->notes,
        ];
    }
}
