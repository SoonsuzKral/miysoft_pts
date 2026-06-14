<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Ziyaretci\Models\Visitor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisitorController extends Controller
{
    public function index(): JsonResponse
    {
        $visitors = Visitor::forCompany(auth()->user()->company_id)->latest()->paginate(15);
        return response()->json($visitors);
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function show(int $id): JsonResponse
    {
        $visitor = Visitor::forCompany(auth()->user()->company_id)->findOrFail($id);
        return response()->json(['data' => $visitor]);
    }

    public function update(int $id): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function destroy(int $id): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function checkin(int $id): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function checkout(int $id): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }

    public function badge(int $id): JsonResponse
    {
        return response()->json(['message' => 'Henüz uygulanmadı.'], 501);
    }
}
