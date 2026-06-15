<?php

namespace Tests\Feature;

use App\Models\Broker;
use App\Models\Item;
use App\Models\Party;
use App\Models\Purchase;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseExpenseControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_broker_payment_out_with_the_selected_broker_id(): void
    {
        $broker = Broker::create([
            'name' => 'Broker One',
            'phone' => '1234567890',
            'status' => true,
        ]);

        $response = $this->postJson(route('payment-out.store'), [
            'broker_id' => $broker->id,
            'payment_type' => 'Cash',
            'amount' => '120.50',
            'discount' => '0',
            'total' => '120.50',
            'reference' => 'REF-001',
            'receipt_no' => 'RCPT-001',
            'payment_date' => now()->toDateString(),
            'description' => 'Broker payment',
            'entity_type' => 'broker',
            'entity_name' => $broker->name,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $transaction = Transaction::query()->latest('id')->first();
        $this->assertNotNull($transaction);
        $this->assertSame($broker->id, $transaction->broker_id);
        $this->assertNull($transaction->party_id);
    }

    public function test_it_creates_a_transaction_item_when_an_item_is_selected_for_payment_out(): void
    {
        $item = Item::create([
            'name' => 'Test Item',
            'type' => 'goods',
            'item_code' => 'TI-001',
            'is_active' => true,
        ]);

        $response = $this->postJson(route('payment-out.store'), [
            'payment_type' => 'Cash',
            'amount' => '75.00',
            'discount' => '0',
            'total' => '75.00',
            'reference' => 'REF-002',
            'receipt_no' => 'RCPT-002',
            'payment_date' => now()->toDateString(),
            'description' => 'Item payment',
            'entity_type' => 'item',
            'entity_name' => $item->name,
            'item_id' => $item->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $transaction = Transaction::query()->latest('id')->first();
        $this->assertNotNull($transaction);
        $this->assertCount(1, $transaction->items);
        $this->assertSame($item->id, $transaction->items->first()->item_id);
    }

    public function test_it_returns_linkable_purchase_rows_for_a_selected_party(): void
    {
        $this->actingAs(User::factory()->create());

        $party = Party::create([
            'name' => 'Party One',
            'phone' => '9999999999',
            'opening_balance' => 0,
            'current_balance' => 0,
        ]);

        $purchase = Purchase::create([
            'type' => 'purchase',
            'party_id' => $party->id,
            'party_name' => $party->name,
            'bill_number' => 'PB-1001',
            'bill_date' => now()->subDay()->toDateString(),
            'total_amount' => 500,
            'grand_total' => 500,
            'paid_amount' => 0,
            'balance' => 0,
        ]);

        $response = $this->getJson(route('payment-out.linkable-purchases', ['party' => $party->id]));

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(1, 'rows');
        $response->assertJsonPath('rows.0.purchase_id', $purchase->id);
    }
}
