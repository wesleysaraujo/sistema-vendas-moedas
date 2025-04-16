<?php

namespace Tests\Unit;

use App\Models\Currency;
use App\Models\Transaction;
use App\Models\User;
use App\Services\CurrencyService;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TransactionService $transactionService;

    protected CurrencyService $currencyService;

    protected User $user;

    protected Currency $currency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->currencyService = $this->createMock(CurrencyService::class);

        $this->transactionService = new TransactionService($this->currencyService);

        $this->user = User::factory()->create();

        $this->currency = Currency::create([
            'code' => 'USD',
            'name' => 'United States Dollar',
            'symbol' => '$',
            'exchange_rate' => 5.25,
        ]);
    }

    public function test_calculate_transaction_details(): void
    {
        $amount = 100.0;
        $exchangeRate = 5.25;
        $feePercentage = config('currency.fee_percentage', 2.5);

        $details = $this->transactionService->calculateTransactionDetails($amount, $exchangeRate);

        $this->assertEquals($amount, $details['amount']);
        $this->assertEquals($exchangeRate, $details['exchange_rate']);
        $this->assertEquals($feePercentage, $details['fee_percentage']);
        $this->assertEquals(round($amount * ($feePercentage / 100), 2), $details['fee_amount']);
        $this->assertEquals(round($amount / $exchangeRate, 2), $details['foreign_amount']);
        $this->assertEquals(round($amount + ($amount * ($feePercentage / 100)), 2), $details['total_amount']);
    }

    public function test_simulate_transaction(): void
    {
        $currencyCode = 'USD';
        $amount = 100.0;
        $exchangeRate = 5.25;

        $this->currencyService->expects($this->once())
            ->method('getCurrencyByCode')
            ->with($currencyCode)
            ->willReturn($this->currency);

        $this->currencyService->expects($this->once())
            ->method('getLatestExchangeRate')
            ->with($currencyCode)
            ->willReturn($exchangeRate);

        $simulation = $this->transactionService->simulateTransaction($currencyCode, $amount);

        $this->assertIsArray($simulation);
        $this->assertEquals($amount, $simulation['amount']);
        $this->assertEquals($exchangeRate, $simulation['exchange_rate']);
        $this->assertEquals(config('currency.fee_percentage', 2.5), $simulation['fee_percentage']);
    }

    public function test_simulate_transaction_with_invalid_currency(): void
    {
        $currencyCode = 'INVALID';
        $amount = 100.0;

        $this->currencyService->expects($this->once())
            ->method('getCurrencyByCode')
            ->with($currencyCode)
            ->willReturn(null);

        $simulation = $this->transactionService->simulateTransaction($currencyCode, $amount);

        $this->assertNull($simulation);
    }

    public function test_process_purchase(): void
    {
        $currencyCode = 'USD';
        $amount = 100.0;
        $notes = 'Test purchase';
        $exchangeRate = 5.25;

        $this->currencyService->expects($this->once())
            ->method('getCurrencyByCode')
            ->with($currencyCode)
            ->willReturn($this->currency);

        $this->currencyService->expects($this->once())
            ->method('getLatestExchangeRate')
            ->with($currencyCode)
            ->willReturn($exchangeRate);

        $transaction = $this->transactionService->processPurchase($this->user, $currencyCode, $amount, $notes);

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertEquals($this->user->id, $transaction->user_id);
        $this->assertEquals($this->currency->id, $transaction->currency_id);
        $this->assertEquals($amount, $transaction->amount);
        $this->assertEquals('completed', $transaction->status);
        $this->assertEquals($notes, $transaction->notes);
    }

    public function test_process_purchase_with_invalid_currency(): void
    {
        $currencyCode = 'INVALID';
        $amount = 100.0;

        $this->currencyService->expects($this->once())
            ->method('getCurrencyByCode')
            ->with($currencyCode)
            ->willReturn(null);

        $transaction = $this->transactionService->processPurchase($this->user, $currencyCode, $amount);

        $this->assertNull($transaction);
    }

    public function test_get_user_transactions(): void
    {
        $this->user->transactions()->create([
            'currency_id' => $this->currency->id,
            'amount' => 100.0,
            'amount_paid' => 102.5,
            'foreign_amount' => 19.05,
            'exchange_rate' => 5.25,
            'fee_percentage' => 2.5,
            'fee_amount' => 2.5,
            'status' => 'completed',
        ]);

        $this->user->transactions()->create([
            'currency_id' => $this->currency->id,
            'amount' => 200.0,
            'amount_paid' => 205.0,
            'foreign_amount' => 38.1,
            'exchange_rate' => 5.25,
            'fee_percentage' => 2.5,
            'fee_amount' => 5.0,
            'status' => 'completed',
        ]);

        $transactions = $this->transactionService->getUserTransactions($this->user);

        $this->assertEquals(2, $transactions->total());

        $transactions = $this->transactionService->getUserTransactions($this->user, ['currency_code' => 'USD']);
        $this->assertEquals(2, $transactions->total());

        $transactions = $this->transactionService->getUserTransactions($this->user, ['status' => 'completed']);
        $this->assertEquals(2, $transactions->total());

        $transactions = $this->transactionService->getUserTransactions($this->user, ['status' => 'failed']);
        $this->assertEquals(0, $transactions->total());
    }

    public function test_get_transaction(): void
    {
        $transaction = $this->user->transactions()->create([
            'currency_id' => $this->currency->id,
            'amount' => 100.0,
            'amount_paid' => 102.5,
            'foreign_amount' => 19.05,
            'exchange_rate' => 5.25,
            'fee_percentage' => 2.5,
            'fee_amount' => 2.5,
            'status' => 'completed',
        ]);

        $foundTransaction = $this->transactionService->getTransaction($transaction->id);
        $this->assertInstanceOf(Transaction::class, $foundTransaction);
        $this->assertEquals($transaction->id, $foundTransaction->id);

        $foundTransaction = $this->transactionService->getTransaction($transaction->id, $this->user);
        $this->assertInstanceOf(Transaction::class, $foundTransaction);
        $this->assertEquals($transaction->id, $foundTransaction->id);

        $anotherUser = User::factory()->create();

        $notFoundTransaction = $this->transactionService->getTransaction($transaction->id, $anotherUser);
        $this->assertNull($notFoundTransaction);
    }

    public function test_update_transaction_status(): void
    {
        // Create a transaction
        $transaction = $this->user->transactions()->create([
            'currency_id' => $this->currency->id,
            'amount' => 100.0,
            'amount_paid' => 102.5,
            'foreign_amount' => 19.05,
            'exchange_rate' => 5.25,
            'fee_percentage' => 2.5,
            'fee_amount' => 2.5,
            'status' => 'pending',
        ]);

        $result = $this->transactionService->updateTransactionStatus($transaction, 'completed');
        $this->assertTrue($result);

        $transaction->refresh();
        $this->assertEquals('completed', $transaction->status);
        $this->assertEquals('pending', $transaction->metadata['previous_status']);

        $result = $this->transactionService->updateTransactionStatus($transaction, 'invalid_status');
        $this->assertFalse($result);

        $transaction->refresh();
        $this->assertEquals('completed', $transaction->status);
    }
}
