<?php

namespace Tests\Unit;

use App\Models\Currency;
use App\Services\CurrencyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CurrencyServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Instância do CurrencyService
     */
    protected CurrencyService $currencyService;

    /**
     * Setup
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Criar instância do serviço
        $this->currencyService = app(CurrencyService::class);

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
    }

    /**
     * Teste de obtenção de todas as moedas
     */
    public function test_can_get_all_currencies(): void
    {
        $currencies = $this->currencyService->getAllCurrencies();

        $this->assertCount(2, $currencies);
        $this->assertInstanceOf(Currency::class, $currencies->first());
    }

    /**
     * Teste de obtenção de moeda por código
     */
    public function test_can_get_currency_by_code(): void
    {
        $currency = $this->currencyService->getCurrencyByCode('USD');

        $this->assertInstanceOf(Currency::class, $currency);
        $this->assertEquals('USD', $currency->code);
        $this->assertEquals('United States Dollar', $currency->name);
    }

    /**
     * Teste de atualização de taxas de câmbio
     */
    public function test_can_update_exchange_rates(): void
    {
        Cache::forget('currencies');

        $result = $this->currencyService->updateExchangeRates();

        // Verificar se a atualização foi bem-sucedida
        $this->assertTrue($result);
    }

    /**
     * Teste de obtenção da taxa de câmbio mais recente
     */
    public function test_can_get_latest_exchange_rate(): void
    {
        // Mock da resposta da API
        Http::fake([
            '*last/USD-BRL*' => Http::response([
                'USDBRL' => [
                    'code' => 'USD',
                    'codein' => 'BRL',
                    'name' => 'Dólar Americano/Real Brasileiro',
                    'bid' => '5.6789',
                ],
            ], 200),
        ]);

        // Limpar o cache
        Cache::forget('exchange_rate_USD');

        $rate = $this->currencyService->getLatestExchangeRate('USD');

        $this->assertEquals(5.6789, $rate);

        $currency = Currency::where('code', 'USD')->first();
        $this->assertEquals(5.6789, $currency->exchange_rate);

        // Verificar se a chamada HTTP foi feita
        Http::assertSent(function ($request) {
            return strpos($request->url(), 'last/USD-BRL') !== false;
        });
    }

    /**
     * Teste de fallback para a taxa do banco quando API falha
     */
    public function test_falls_back_to_database_rate_when_api_fails(): void
    {
        // Configurar uma taxa conhecida no banco
        $currency = Currency::where('code', 'USD')->first();
        $currency->exchange_rate = 5.1234;
        $currency->save();

        // Mock da resposta da API com falha
        Http::fake([
            '*last/USD-BRL*' => Http::response([], 500),
        ]);

        // Limpar o cache
        Cache::forget('exchange_rate_USD');

        // Obter a taxa de câmbio
        $rate = $this->currencyService->getLatestExchangeRate('USD');

        // Verificar que retornou a taxa do banco
        $this->assertEquals(5.1234, $rate);

        // Verificar se a chamada HTTP foi feita, mesmo que tenha falhado
        Http::assertSent(function ($request) {
            return strpos($request->url(), 'last/USD-BRL') !== false;
        });
    }

    /**
     * Teste de expiração do cache
     */
    public function test_exchange_rate_is_cached(): void
    {
        $mockRate = 5.9876;
        Cache::put('exchange_rate_USD', $mockRate, 10); // 10 minutos

        Http::fake([
            '*' => function () {
                $this->fail('Não deveria ter chamado a API porque o valor está em cache');
            }
        ]);

        $rate = $this->currencyService->getLatestExchangeRate('USD');

        $this->assertEquals($mockRate, $rate);

        Http::assertNothingSent();
    }
}
