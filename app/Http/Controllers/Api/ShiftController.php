<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ShiftController extends Controller
{
    public function index(): JsonResponse
    {
        $shifts = \App\Modules\Vardiya\Models\Shift::forCompany(auth()->user()->company_id)->get();
        return response()->json(['data' => $shifts]);
    }

    public function roster(): JsonResponse
    {
        return response()->json(['data' => []]);
    }

    public function assign(): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function clockIn(): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function clockOut(): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function liveStatus(): JsonResponse
    {
        return response()->json(['data' => []]);
    }

    public function swapRequests(): JsonResponse
    {
        return response()->json(['data' => []]);
    }

    public function approveSwap(int $id): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function rejectSwap(int $id): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function store(): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function update(int $id): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function destroy(int $id): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }
}
