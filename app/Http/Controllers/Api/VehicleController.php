<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Arac\Models\Vehicle;
use Illuminate\Http\JsonResponse;

class VehicleController extends Controller
{
    public function index(): JsonResponse
    {
        $vehicles = Vehicle::forCompany(auth()->user()->company_id)->latest()->paginate(15);
        return response()->json($vehicles);
    }

    public function store(): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function show(int $id): JsonResponse
    {
        $vehicle = Vehicle::forCompany(auth()->user()->company_id)->findOrFail($id);
        return response()->json(['data' => $vehicle]);
    }

    public function update(int $id): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function destroy(int $id): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function fuelIndex(): JsonResponse
    {
        return response()->json(['data' => []]);
    }

    public function fuelStore(): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function fuelUpdate(int $id): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function fuelDestroy(int $id): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function usageIndex(): JsonResponse
    {
        return response()->json(['data' => []]);
    }

    public function usageStore(): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function usageUpdate(int $id): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function usageComplete(int $id): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function usageDestroy(int $id): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }
}
