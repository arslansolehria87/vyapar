<?php

namespace Tests\Feature;

use App\Models\Broker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrokerStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_broker_can_be_created_with_form_fields(): void
    {
        $broker = Broker::create([
            'name' => 'Ali Broker',
            'phone' => '03001234567',
            'city' => 'Lahore',
            'address' => 'Main Road',
            'commission_type' => 'percent',
            'commission_rate' => 2.5,
            'total_brokerage' => 1000,
            'paid_brokerage' => 250,
            'notes' => 'Test notes',
            'status' => true,
        ]);

        $this->assertDatabaseHas('brokers', [
            'name' => 'Ali Broker',
            'commission_type' => 'percent',
            'commission_rate' => '2.50',
            'total_brokerage' => '1000.00',
            'paid_brokerage' => '250.00',
            'notes' => 'Test notes',
            'status' => true,
        ]);

        $this->assertSame('Ali Broker', $broker->name);
        $this->assertTrue($broker->status);
    }
}
