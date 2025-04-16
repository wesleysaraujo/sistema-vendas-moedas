<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TransactionEndpointsTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    protected User $user;

    protected Currency $currency;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create();

        // Create a test currency
        $this->currency = Currency::create([
            'code' => 'USD',
            'name' => 'United States Dollar',
            'symbol' => '$',
            'exchange_rate' => 5.1234,
        ]);

        // Authenticate user for all tests
        Sanctum::actingAs($this->user);
    }

    public function test_can_simulate_transaction(): void
    {
        $data = [
            'currency_code' => 'USD',
            'amount' => 100.0,
        ];

        $response = $this->postJson('/api/transactions/simulate', $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'amount',
                    'exchange_rate',
                    'fee_percentage',
                    'fee_amount',
                    'foreign_amount',
                    'total_amount',
                ]
            ])
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_cannot_simulate_transaction_with_invalid_currency(): void
    {
        $data = [
            'currency_code' => 'XYZ',
            'amount' => 100.0,
        ];

        $response = $this->postJson('/api/transactions/simulate', $data);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'errors' => [
                    'currency_code'
                ]
            ])
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_cannot_simulate_transaction_with_amount_too_small(): void
    {
        $data = [
            'currency_code' => 'USD',
            'amount' => 49.99,
        ];

        $response = $this->postJson('/api/transactions/simulate', $data);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'errors' => [
                    'amount'
                ]
            ])
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_can_create_transaction(): void
    {
        $data = [
            'currency_code' => 'USD',
            'amount' => 100.0,
            'notes' => 'Test transaction',
        ];

        $response = $this->postJson('/api/transactions', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'amount',
                    'amount_paid',
                    'foreign_amount',
                    'exchange_rate',
                    'fee_percentage',
                    'fee_amount',
                    'status',
                    'notes',
                    'currency' => [
                        'id',
                        'code',
                        'name',
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Transação realizada com sucesso',
                'data' => [
                    'user_id' => $this->user->id,
                    'status' => 'completed',
                    'notes' => 'Test transaction',
                ]
            ]);
    }

    public function test_cannot_create_transaction_with_invalid_data(): void
    {
        $data = [
            'currency_code' => 'XYZ',
            'amount' => 30.0,
        ];

        $response = $this->postJson('/api/transactions', $data);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'errors'
            ])
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_can_list_user_transactions(): void
    {
        // Create some transactions for the user
        $this->postJson('/api/transactions', [
            'currency_code' => 'USD',
            'amount' => 100.0,
        ]);

        $this->postJson('/api/transactions', [
            'currency_code' => 'USD',
            'amount' => 200.0,
            'notes' => 'Second transaction',
        ]);

        $response = $this->getJson('/api/transactions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'user_id',
                            'amount',
                            'status',
                        ]
                    ],
                    'current_page',
                    'total'
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'total' => 2
                ]
            ]);
    }

    public function test_can_filter_transactions_by_currency(): void
    {
        // Create a second currency
        Currency::create([
            'code' => 'EUR',
            'name' => 'Euro',
            'symbol' => '€',
            'exchange_rate' => 6.2345,
        ]);

        $this->postJson('/api/transactions', [
            'currency_code' => 'USD',
            'amount' => 100.0,
        ]);

        $this->postJson('/api/transactions', [
            'currency_code' => 'EUR',
            'amount' => 200.0,
        ]);

        $response = $this->getJson('/api/transactions?currency_code=USD');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total' => 1
                ]
            ]);

        $response = $this->getJson('/api/transactions?currency_code=EUR');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total' => 1
                ]
            ]);
    }

    public function test_can_view_transaction_details(): void
    {
        $createResponse = $this->postJson('/api/transactions', [
            'currency_code' => 'USD',
            'amount' => 100.0,
            'notes' => 'Test transaction',
        ]);

        $transactionId = $createResponse->json('data.id');

        $response = $this->getJson("/api/transactions/{$transactionId}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'user_id',
                    'amount',
                    'amount_paid',
                    'foreign_amount',
                    'exchange_rate',
                    'fee_percentage',
                    'fee_amount',
                    'status',
                    'notes',
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $transactionId,
                    'user_id' => $this->user->id,
                    'status' => 'completed',
                    'notes' => 'Test transaction',
                ]
            ]);
    }

    public function test_cannot_view_nonexistent_transaction(): void
    {
        $response = $this->getJson('/api/transactions/9999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Transação não encontrada',
            ]);
    }

    public function test_cannot_view_another_users_transaction(): void
    {
        $createResponse = $this->postJson('/api/transactions', [
            'currency_code' => 'USD',
            'amount' => 100.0,
        ]);

        $transactionId = $createResponse->json('data.id');

        $anotherUser = User::factory()->create();
        Sanctum::actingAs($anotherUser);

        $response = $this->getJson("/api/transactions/{$transactionId}");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Transação não encontrada',
            ]);
    }
}
