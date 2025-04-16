<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CurrencyService;
use Illuminate\Http\JsonResponse;

class CurrenciesController extends Controller
{
    /**
     *A instância do serviço de moeda.
     *
     * @var CurrencyService
     */
    protected CurrencyService $currencyService;

    /**
     *
     * @param CurrencyService $currencyService
     */
    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * Liste todas as moedas disponíveis
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $currencies = $this->currencyService->getAllCurrencies();

        return response()->json([
            'success' => true,
            'data' => $currencies
        ]);
    }

    /**
     * Mostre detalhes para uma moeda específica
     *
     * @param string $code
     * @return JsonResponse
     */
    public function show(string $code): JsonResponse
    {
        $exchangeRate = $this->currencyService->getLatestExchangeRate($code);

        $currency = $this->currencyService->getCurrencyByCode($code);

        if (!$currency) {
            return response()->json([
                'success' => false,
                'message' => 'Moeda não encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $currency,
            'exchange_rate' => $exchangeRate ?? $currency->exchange_rate
        ]);
    }
}
