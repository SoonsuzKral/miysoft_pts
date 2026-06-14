<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Seyahat\Models\TravelRequest;
use Illuminate\Http\JsonResponse;

class TravelController extends Controller
{
    public function index(): JsonResponse
    {
        $travel = TravelRequest::forCompany(auth()->user()->company_id)->latest()->paginate(15);
        return response()->json($travel);
    }

    public function store(): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function show(int $id): JsonResponse
    {
        $travel = TravelRequest::forCompany(auth()->user()->company_id)->findOrFail($id);
        return response()->json(['data' => $travel]);
    }

    public function update(int $id): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function destroy(int $id): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function approve(int $id): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function reject(int $id): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function cancel(int $id): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function complete(int $id): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }
}
