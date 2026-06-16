<?php

namespace Tests\Feature;

use App\Models\Party;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentInControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_linkable_sales_when_party_name_matches_and_balance_is_missing(): void
    {
        $party = Party::create([
            'name' => 'Customer One',
            'phone' => '9999999999',
            'opening_balance' => 0,
            'current_balance' => 0,
        ]);

        $sale = Sale::create([
            'type' => 'invoice',
            'party_id' => null,
            'party_name' => $party->name,
            'bill_number' => 'INV-1001',
            'invoice_date' => now()->subDay()->toDateString(),
            'total_amount' => 500,
            'grand_total' => 500,
            'received_amount' => 0,
            'balance' => null,
            'status' => 'pending',
        ]);

        $response = $this->getJson(route('payments-in.linkable-sales', ['party' => $party->id]));

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(1, 'rows');
        $response->assertJsonPath('rows.0.sale_id', $sale->id);
    }
}
