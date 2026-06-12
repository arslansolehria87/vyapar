<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class ExpenseCreateControllerTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    public function test_store_expense_persists_items_and_attachments(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::create([
            'user_id' => $user->id,
            'name' => 'Office',
            'type' => 'Indirect Expense',
        ]);

        $response = $this->actingAs($user)->postJson(route('expense.save'), [
            'expense_category_id' => $category->id,
            'expense_date' => now()->toDateString(),
            'total_amount' => 100,
            'payment_type' => 'Cash',
            'tax_enabled' => false,
            'items_json' => [[
                'name' => 'Paper',
                'qty' => 2,
                'price' => 50,
                'taxRateId' => null,
                'taxRateName' => null,
                'taxRateValue' => 0,
                'taxAmount' => 0,
                'amount' => 100,
            ]],
            'additional_charges' => [],
            'transportation_details' => [],
            'description' => 'Test description',
            'attachments' => [
                'images' => ['/storage/test-image.png'],
                'documents' => [],
            ],
        ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $expense = Expense::where('user_id', $user->id)->latest()->first();
        $this->assertNotNull($expense);
        $this->assertSame('Test description', $expense->description);
        $this->assertSame([
            'images' => ['/storage/test-image.png'],
            'documents' => [],
        ], $expense->attachments);

        $this->assertDatabaseHas('expense_items', [
            'expense_id' => $expense->id,
            'user_id' => $user->id,
            'name' => 'Paper',
        ]);

        $this->assertSame(2, ExpenseItem::where('expense_id', $expense->id)->value('quantity'));
    }
}
