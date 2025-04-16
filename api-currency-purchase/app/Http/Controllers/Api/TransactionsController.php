<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionsController extends Controller
{
    /**
     * The transaction service instance.
     *
     * @var TransactionService
     */
    protected TransactionService $transactionService;

    /**
     * Create a new controller instance.
     *
     * @param TransactionService $transactionService
     */
    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Simulate a currency purchase transaction
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function simulate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'currency_code' => 'required|string|exists:currencies,code',
            'amount' => 'required|numeric|min:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $simulation = $this->transactionService->simulateTransaction(
            $request->currency_code,
            (float) $request->amount
        );

        if (!$simulation) {
            return response()->json([
                'success' => false,
                'message' => 'Moeda não encontrada ou cotação indisponível',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $simulation,
        ]);
    }

    /**
     * Salvar uma nova transação
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'currency_code' => 'required|string|exists:currencies,code',
            'amount' => 'required|numeric|min:50',
            'notes' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $transaction = $this->transactionService->processPurchase(
            $request->user(),
            $request->currency_code,
            (float) $request->amount,
            $request->notes
        );

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Falha ao processar a transação',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Transação realizada com sucesso',
            'data' => $transaction->load('currency'),
        ], 201);
    }

    /**
     * Listar transações de usuário
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['currency_code', 'status', 'from_date', 'to_date']);
        $perPage = (int) $request->get('per_page', 10);

        $transactions = $this->transactionService->getUserTransactions(
            $request->user(),
            $filters,
            $perPage
        );

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    /**
     * Mostrar detalhes da transação
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $transaction = $this->transactionService->getTransaction($id, $request->user());

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transação não encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $transaction,
        ]);
    }
}
