<?php

namespace Tests\Feature;

use App\Models\Currency;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CurrencyEndpointsTest extends TestCase
{
    use WithFaker;

    /**
     * Setup
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Adicionar algumas moedas para testes
        Currency::create([
            'code' => 'USD',
            'name' => 'United States Dollar',
            'symbol' => '$',
            'exchange_rate' => 5.1234,
        ]);

        Currency::create([
            'code' => 'EUR',
            'name' => 'Euro',
            'symbol' => '€',
            'exchange_rate' => 6.2345,
        ]);

        Currency::create([
            'code' => 'GBP',
            'name' => 'British Pound',
            'symbol' => '£',
            'exchange_rate' => 7.3456,
        ]);

        Currency::create([
            'code' => 'JPY',
            'name' => 'Japanese Yen',
            'symbol' => '¥',
            'exchange_rate' => 0.0456,
        ]);

        Currency::create([
            'code' => 'AUD',
            'name' => 'Australian Dollar',
            'symbol' => 'A$',
            'exchange_rate' => 3.5678,
        ]);
    }

    /**
     * Teste de listagem de moedas
     */
    public function test_can_list_currencies(): void
    {
        $response = $this->getJson('/api/currencies');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'code',
                        'name',
                        'exchange_rate'
                    ]
                ]
            ])
            ->assertJsonCount(5, 'data')
            ->assertJson([
                'success' => true,
            ]);
    }


    /**
     * Teste de exibição de detalhes de uma moeda
     */
    public function test_can_view_currency_details(): void
    {
        $currency = Currency::where('code', 'USD')->first();

        $response = $this->getJson('/api/currencies/show/' . $currency->code);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'code',
                    'name',
                    'exchange_rate'
                ],
                'exchange_rate'
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'code' => 'USD',
                    'name' => 'United States Dollar',
                ],
            ]);
    }

    /**
     * Teste de exibição de moeda inexistente
     */
    public function test_receives_404_for_nonexistent_currency(): void
    {
        $response = $this->getJson('/api/currencies/show/XYZ');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Moeda não encontrada',
            ]);
    }

    /**
     * Teste de atualização de taxa de câmbio via API externa
     */
    public function test_currency_controller_fetches_latest_exchange_rate(): void
    {
        // Mock da resposta da API
        Http::fake([
            '*last/USD-BRL*' => Http::response([
                'USDBRL' => [
                    'code' => 'USD',
                    'codein' => 'BRL',
                    'name' => 'Dólar Americano/Real Brasileiro',
                    'high' => '5.3815',
                    'low' => '5.3815',
                    'varBid' => '0.0096',
                    'pctChange' => '0.18',
                    'bid' => '5.3789',
                    'ask' => '5.3795',
                    'timestamp' => '1634244000',
                    'create_date' => '2021-10-14 13:00:00',
                ]
            ], 200),
        ]);

        $response = $this->getJson('/api/currencies/show/USD');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'exchange_rate'
            ]);

        // Verifica se a chamada HTTP foi feita
        Http::assertSent(function ($request) {
            return strpos($request->url(), 'last/USD-BRL') !== false;
        });
    }

    /**
     * Teste de recuperação de câmbio quando a API está indisponível
     */
    public function test_api_failure_returns_fallback_exchange_rate(): void
    {
        // Configura o mock para simular falha na API
        Http::fake([
            '*last/EUR-BRL*' => Http::response([], 500),
        ]);

        // Atualizamos a taxa de câmbio no banco de dados para um valor conhecido
        Currency::where('code', 'EUR')->update(['exchange_rate' => 6.2345]);

        $response = $this->getJson('/api/currencies/show/EUR');

        $response->assertStatus(200)
            ->assertJsonPath('data.code', 'EUR')
            ->assertJsonPath('data.exchange_rate', 6.2345);

        // Verifica se a chamada HTTP foi feita, mesmo que tenha falhado
        Http::assertSent(function ($request) {
            return strpos($request->url(), 'last/EUR-BRL') !== false;
        });
    }
}
