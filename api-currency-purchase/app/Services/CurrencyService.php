<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    /**
     * Base URL for the currency API
     *
     * @var string
     */
    protected string $apiBaseUrl;

    /**
     * Cache time for currency rates in minutes
     *
     * @var int
     */
    protected int $cacheTime;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->apiBaseUrl = config('services.awesomeapi.economy.base_url', 'https://economia.awesomeapi.com.br/json');
        $this->cacheTime = config('currency.cache_duration', 10);
    }

    /**
     * Get all available currencies
     *
     *return Collection
     */
    public function getAllCurrencies()
    {
        // Check if currencies are cached
        if (Cache::has('currencies')) {
            return Cache::get('currencies');
        }

        return Cache::remember('currencies', $this->cacheTime, function () {
            return Currency::orderBy('code')->select('code', 'name', 'exchange_rate')->get();
        });
    }

    /**
     * Get a specific currency by its code
     *
     * @param string $code
     * @return Currency|null
     */
    public function getCurrencyByCode(string $code): ?Currency
    {
        return Currency::where('code', $code)->first();
    }

    /**
     * Fetch latest exchange rates from the API and update the database
     *
     * @return bool
     */
    public function updateExchangeRates(): bool
    {
        try {
            $currencies = Currency::select('code', 'name')->get();

            foreach ($currencies as $currency) {
                $response = Http::get("{$this->apiBaseUrl}/last/{$currency->code}-BRL");

                if (!$response->successful()) {
                    Log::error('Failed to fetch exchange rate', [
                        'currency' => $currency->code,
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                    continue;
                }

                if ($response->status() !== 200) {
                    Log::error('Failed to fetch exchange rate', [
                        'currency' => $currency->code,
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                    continue;
                }

                // Parse the response
                $key = "{$currency->code}BRL";

                if ($response->successful()) {
                    $data = $response->json();

                    // Update or create the currency
                    Currency::updateOrCreate(
                        ['code' => $currency->code],
                        [
                            'code' => $currency->code,
                            'name' => $data['name'] ?? $currency->name,
                            'exchange_rate' => $data[$key]['bid'] ?? 1.00,
                        ]
                    );
                }
            }

            // Clear the cache
            Cache::forget('currencies');

            return true;
        } catch (ConnectionException $e) {
            Log::error('Connection error while fetching exchange rates', [
                'message' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Get the latest exchange rate for a specific currency
     *
     * @param string $code
     * @return float|null
     */
    public function getLatestExchangeRate(string $code): ?float
    {
        $cacheKey = "exchange_rate_{$code}";

        return Cache::remember($cacheKey, $this->cacheTime, function () use ($code) {
            try {
                $response = Http::get("{$this->apiBaseUrl}/last/{$code}-BRL");

                if ($response->successful()) {
                    $data = $response->json();
                    $key = "{$code}BRL";

                    if (isset($data[$key]['bid'])) {
                        $rate = (float) $data[$key]['bid'];

                        $currency = $this->getCurrencyByCode($code);
                        if ($currency) {
                            $currency->exchange_rate = $rate;
                            $currency->save();
                        }

                        return $rate;
                    }
                }

                $currency = $this->getCurrencyByCode($code);
                return $currency ? $currency->exchange_rate : null;

            } catch (ConnectionException $e) {
                Log::error('Erro de conexão ao buscar taxa de câmbio', [
                    'currency' => $code,
                    'message' => $e->getMessage()
                ]);

                $currency = $this->getCurrencyByCode($code);
                return $currency ? $currency->exchange_rate : null;
            }
        });
    }

    public function convert(string $currencyCode, float $amount): ?array
    {
        if (!$currencyCode) {
            return null;
        }

        $toRate = $this->getLatestExchangeRate($currencyCode);

        if ($toRate) {
            return [
                'from' => $currencyCode,
                'to' => 'BRL - Real Brasileiro',
                'amount' => $amount,
                'exchange_rate' => $toRate,
                'converted_amount' => $amount * $toRate
            ];
        }

        return null;
    }
}
