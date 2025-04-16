<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * A instância do CurrencyService.
     *
     * @var CurrencyService
     */
    protected CurrencyService $currencyService;

    /**
     * Porcentagem de taxa de serviço
     *
     * @var float
     */
    protected float $serviceFeePercentage;

    /**
     * Constructor
     *
     * @param CurrencyService $currencyService
     */
    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
        $this->serviceFeePercentage = config('currency.fee_percentage', 2);
    }

    /**
     * Simular uma transação de compra em moeda
     *
     * @param string $currencyCode
     * @param float $amount
     * @return array|null
     */
    public function simulateTransaction(string $currencyCode, float $amount): ?array
    {
        $currency = $this->currencyService->getCurrencyByCode($currencyCode);
        if (!$currency) {
            return null;
        }

        $exchangeRate = $this->currencyService->getLatestExchangeRate($currencyCode) ?? $currency->exchange_rate;

        return $this->calculateTransactionDetails($amount, $exchangeRate);
    }

    /**
     * Calcule detalhes da transação, incluindo taxas
     *
     * @param float $amount
     * @param float $exchangeRate
     * @return array
     */
    public function calculateTransactionDetails(float $amount, float $exchangeRate): array
    {
        $feeAmount = $amount * ($this->serviceFeePercentage / 100);
        $totalAmount = $amount + $feeAmount;
        $foreignAmount = ($amount / $exchangeRate);

        return [
            'amount' => $amount,
            'exchange_rate' => $exchangeRate,
            'fee_percentage' => $this->serviceFeePercentage,
            'fee_amount' => round($feeAmount, 2),
            'foreign_amount' => round($foreignAmount, 2),
            'total_amount' => round($totalAmount, 2),
        ];
    }

    /**
     * Processar uma transação de compra de moeda
     *
     * @param User $user
     * @param string $currencyCode
     * @param float $amount
     * @param string|null $notes
     * @return Transaction|null
     */
    public function processPurchase(User $user, string $currencyCode, float $amount, ?string $notes = null): ?Transaction
    {
        $currency = $this->currencyService->getCurrencyByCode($currencyCode);
        if (!$currency) {
            return null;
        }

        $exchangeRate = $this->currencyService->getLatestExchangeRate($currencyCode) ?? $currency->exchange_rate;

        $details = $this->calculateTransactionDetails($amount, $exchangeRate);

        return DB::transaction(function () use ($user, $currency, $amount, $details, $notes) {
            // Create transaction record
            return $user->transactions()->create([
                'currency_id' => $currency->id,
                'amount' => $amount,
                'amount_paid' => $details['total_amount'],
                'foreign_amount' => $details['foreign_amount'],
                'exchange_rate' => $details['exchange_rate'],
                'fee_percentage' => $details['fee_percentage'],
                'fee_amount' => $details['fee_amount'],
                'status' => 'completed',
                'notes' => $notes,
                'metadata' => [
                    'calculated_at' => now()->toIso8601String(),
                    'currency_code' => $currency->code,
                    'currency_name' => $currency->name,
                ],
            ]);
        });
    }

    /**
     * Pegar transações de usuário com filtros opcionais
     *
     * @param User $user
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUserTransactions(User $user, array $filters = [], int $perPage = 2): LengthAwarePaginator
    {
        $query = $user->transactions()
            ->latest();

        if (!empty($filters['currency_code'])) {
            $query->whereHas('currency', function ($q) use ($filters) {
                $q->where('code', $filters['currency_code']);
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Pegar a transação por id
     *
     * @param int $id
     * @param User|null $user
     * @return Transaction|null
     */
    public function getTransaction(int $id, ?User $user = null): ?Transaction
    {
        $query = Transaction::with('currency', 'user');

        if ($user) {
            $query->where('user_id', $user->id);
        }

        return $query->find($id);
    }

    /**
     * Atualizar status da transação
     *
     * @param Transaction $transaction
     * @param string $status
     * @param array $metadata
     * @return bool
     */
    public function updateTransactionStatus(Transaction $transaction, string $status, array $metadata = []): bool
    {
        if (!in_array($status, ['pending', 'completed', 'failed', 'refunded'])) {
            return false;
        }

        $existingMetadata = $transaction->metadata ?: [];
        $updatedMetadata = array_merge($existingMetadata, $metadata, [
            'status_updated_at' => now()->toIso8601String(),
            'previous_status' => $transaction->status,
        ]);

        $transaction->status = $status;
        $transaction->metadata = $updatedMetadata;

        return $transaction->save();
    }
}
