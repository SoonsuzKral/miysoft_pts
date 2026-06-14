<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class FinanceController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => []]);
    }

    public function store(): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['data' => []]);
    }

    public function update(int $id): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function destroy(int $id): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function approveAdvance(): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function rejectAdvance(): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function cancelAdvance(): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function markRepaid(): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function approveExpense(): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function rejectExpense(): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function markPaid(): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function categories(): JsonResponse
    {
        return response()->json(['data' => []]);
    }

    public function storeCategory(): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }
}
