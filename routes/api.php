<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PersonelController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\VisitorController;
use App\Http\Controllers\Api\TravelController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\FinanceController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes - MİYSOPT PTS
|--------------------------------------------------------------------------
| Bu dosya mobil uygulamalar ve 3. taraf entegrasyonlar için REST API sağlar.
| Kimlik doğrulama: Laravel Sanctum (token tabanlı)
| Format: JSON
| Versiyonlama: URL'de /v1/ prefix ile
*/

Route::prefix('v1')->group(function () {
    // ─── Public Auth Routes ─────────────────────────────────────────────
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/password/forgot', [AuthController::class, 'forgotPassword']);
    Route::post('/password/reset', [AuthController::class, 'resetPassword']);

    // ─── Protected Routes (Sanctum) ─────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::get('/user', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);

        // Dashboard
        Route::get('/dashboard/kpi', [DashboardController::class, 'kpi']);
        Route::get('/dashboard/chart', [DashboardController::class, 'chart']);
        Route::get('/dashboard/activity', [DashboardController::class, 'activity']);

        // ─── Personel ───────────────────────────────────────────────────
        Route::apiResource('personels', PersonelController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::get('personels/{personel}/card', [PersonelController::class, 'card']);
        Route::patch('personels/{personel}/toggle-active', [PersonelController::class, 'toggleActive']);
        Route::get('personels/export', [PersonelController::class, 'export']);

        // Personel Documents
        Route::prefix('personels/{personel}/documents')->group(function () {
            Route::get('/', [PersonelController::class, 'documents']);
            Route::post('/', [PersonelController::class, 'storeDocument']);
            Route::get('{document}/download', [PersonelController::class, 'downloadDocument']);
            Route::delete('{document}', [PersonelController::class, 'destroyDocument']);
        });

        // ─── Departman & Pozisyon ───────────────────────────────────────
        Route::get('departments', fn () => \App\Models\Department::forCompany(auth()->user()->company_id)->get());
        Route::get('positions', fn () => \App\Models\Position::forCompany(auth()->user()->company_id)->get());

        // ─── İzin Yönetimi ──────────────────────────────────────────────
        Route::apiResource('leave-types', LeaveController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::apiResource('leave-requests', LeaveController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::post('leave-requests/{leaveRequest}/approve', [LeaveController::class, 'approve']);
        Route::post('leave-requests/{leaveRequest}/reject', [LeaveController::class, 'reject']);
        Route::post('leave-requests/{leaveRequest}/cancel', [LeaveController::class, 'cancel']);
        Route::get('leave-balances', [LeaveController::class, 'balances']);
        Route::get('leave/calendar', [LeaveController::class, 'calendar']);

        // ─── Puantaj / Mesai ────────────────────────────────────────────
        Route::get('attendance/today', [AttendanceController::class, 'today']);
        Route::get('attendance/daily-summary', [AttendanceController::class, 'dailySummary']);
        Route::get('attendance/monthly-summary', [AttendanceController::class, 'monthlySummary']);
        Route::post('attendance/record', [AttendanceController::class, 'storeRecord']);
        Route::patch('attendance/{timeRecord}/correct', [AttendanceController::class, 'correct']);
        Route::delete('attendance/{timeRecord}', [AttendanceController::class, 'destroy']);

        // ─── Vardiya ────────────────────────────────────────────────────
        Route::apiResource('shifts', ShiftController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('shifts/roster', [ShiftController::class, 'roster']);
        Route::post('shifts/assign', [ShiftController::class, 'assign']);
        Route::post('shifts/clock-in', [ShiftController::class, 'clockIn']);
        Route::post('shifts/clock-out', [ShiftController::class, 'clockOut']);
        Route::get('shifts/live-status', [ShiftController::class, 'liveStatus']);
        Route::get('shifts/swap-requests', [ShiftController::class, 'swapRequests']);
        Route::post('shifts/swap-requests/{id}/approve', [ShiftController::class, 'approveSwap']);
        Route::post('shifts/swap-requests/{id}/reject', [ShiftController::class, 'rejectSwap']);

        // ─── Envanter ───────────────────────────────────────────────────
        Route::apiResource('assets', AssetController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::post('assets/{asset}/assign', [AssetController::class, 'assign']);
        Route::post('assets/{asset}/return', [AssetController::class, 'return']);
        Route::get('assets/{asset}/history', [AssetController::class, 'history']);
        Route::get('assets/{asset}/zimmet-pdf', [AssetController::class, 'zimmetPdf']);
        Route::get('asset-types', [AssetController::class, 'types']);
        Route::post('asset-types', [AssetController::class, 'storeType']);

        // ─── Ziyaretçi ──────────────────────────────────────────────────
        Route::apiResource('visitors', VisitorController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::post('visitors/{visitor}/checkin', [VisitorController::class, 'checkin']);
        Route::post('visitors/{visitor}/checkout', [VisitorController::class, 'checkout']);
        Route::get('visitors/{visitor}/badge', [VisitorController::class, 'badge']);

        // ─── Seyahat ────────────────────────────────────────────────────
        Route::apiResource('travel', TravelController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::post('travel/{travel}/approve', [TravelController::class, 'approve']);
        Route::post('travel/{travel}/reject', [TravelController::class, 'reject']);
        Route::post('travel/{travel}/cancel', [TravelController::class, 'cancel']);
        Route::post('travel/{travel}/complete', [TravelController::class, 'complete']);

        // ─── Araç ───────────────────────────────────────────────────────
        Route::apiResource('vehicles', VehicleController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::get('vehicles/fuel', [VehicleController::class, 'fuelIndex']);
        Route::post('vehicles/fuel', [VehicleController::class, 'fuelStore']);
        Route::put('vehicles/fuel/{fuel}', [VehicleController::class, 'fuelUpdate']);
        Route::delete('vehicles/fuel/{fuel}', [VehicleController::class, 'fuelDestroy']);
        Route::get('vehicles/usage', [VehicleController::class, 'usageIndex']);
        Route::post('vehicles/usage', [VehicleController::class, 'usageStore']);
        Route::put('vehicles/usage/{usage}', [VehicleController::class, 'usageUpdate']);
        Route::post('vehicles/usage/{usage}/complete', [VehicleController::class, 'usageComplete']);
        Route::delete('vehicles/usage/{usage}', [VehicleController::class, 'usageDestroy']);

        // ─── Finans (Avans & Masraf) ────────────────────────────────────
        Route::apiResource('advances', FinanceController::class)->only(['index', 'store', 'show', 'update', 'destroy'])
            ->names(['index' => 'advances.index', 'store' => 'advances.store', 'show' => 'advances.show', 'update' => 'advances.update', 'destroy' => 'advances.destroy']);
        Route::post('advances/{advance}/approve', [FinanceController::class, 'approveAdvance']);
        Route::post('advances/{advance}/reject', [FinanceController::class, 'rejectAdvance']);
        Route::post('advances/{advance}/cancel', [FinanceController::class, 'cancelAdvance']);
        Route::post('advances/{advance}/repaid', [FinanceController::class, 'markRepaid']);

        Route::apiResource('expenses', FinanceController::class)->only(['index', 'store', 'show', 'update', 'destroy'])
            ->names(['index' => 'expenses.index', 'store' => 'expenses.store', 'show' => 'expenses.show', 'update' => 'expenses.update', 'destroy' => 'expenses.destroy']);
        Route::post('expenses/{expense}/approve', [FinanceController::class, 'approveExpense']);
        Route::post('expenses/{expense}/reject', [FinanceController::class, 'rejectExpense']);
        Route::post('expenses/{expense}/paid', [FinanceController::class, 'markPaid']);
        Route::get('expense-categories', [FinanceController::class, 'categories']);
        Route::post('expense-categories', [FinanceController::class, 'storeCategory']);

        // ─── Bildirimler ────────────────────────────────────────────────
        Route::get('notifications/recent', [NotificationController::class, 'recent']);
        Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('notifications/mark-read', [NotificationController::class, 'markRead']);
        Route::delete('notifications/{id}', [NotificationController::class, 'destroy']);
    });
});

// ─── API Docs (OpenAPI/Swagger) ────────────────────────────────────────
Route::get('/docs', fn () => redirect('/docs/api-docs.html'))->name('api.docs');
Route::get('/openapi.yaml', function () {
    return response()->file(base_path('storage/api-docs/openapi.yaml'))
        ->header('Content-Type', 'application/x-yaml');
})->name('api.openapi');