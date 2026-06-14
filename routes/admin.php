<?php
// MİYSOFT PTS — Admin rotaları: prefix admin, middleware auth + verified + can:access_admin
// (login/register bu dosyada yok; yalnızca routes/auth.php guest grubunda.)

use Illuminate\Support\Facades\Route;
use App\Modules\Personel\Controllers\PersonelController;
use App\Modules\Dashboard\Controllers\DashboardController;
use App\Modules\Izin\Controllers\LeaveTypeController;
use App\Modules\Izin\Controllers\LeaveRequestController;
use App\Modules\Puantaj\Controllers\TimeRecordController;
use App\Modules\Puantaj\Controllers\PuantajController;
use App\Modules\Vardiya\Controllers\ShiftController;
use App\Modules\Sirket\Controllers\CompanyController;
use App\Modules\Sirket\Controllers\DepartmentController;
use App\Modules\Sirket\Controllers\PositionController;
use App\Modules\Envanter\Controllers\AssetController;
use App\Modules\Ziyaretci\Controllers\VisitorController;
use App\Modules\Finans\Controllers\AdvanceController;
use App\Modules\Finans\Controllers\ExpenseController;
use App\Modules\Abonelik\Controllers\SubscriptionController;
use App\Modules\CMS\Controllers\CmsController;
use App\Modules\Etkilesim\Controllers\AnnouncementController;
use App\Modules\Surec\Controllers\ProcessController;
use App\Modules\OzelSaat\Controllers\SpecialAttendanceController;

// Abonelik süresi dolmuş kullanıcılar için (subscription middleware'i yok)
Route::get('admin/subscription-expired', function () {
    return view('admin.subscription.expired');
})->middleware(['auth', 'verified', 'can:access_admin'])->name('admin.subscription.expired');

Route::prefix('admin')
    ->middleware(['auth', 'verified', 'can:access_admin', 'subscription'])
    ->name('admin.')
    ->group(function () {

        // ─── Dashboard ────────────────────────────────────────────
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
        Route::get('/dashboard/widgets', [DashboardController::class, 'widgetData'])->name('dashboard.widgets');
        Route::get('/dashboard/chart', [DashboardController::class, 'chartData'])->name('dashboard.chart');
        Route::get('/dashboard/activity', [DashboardController::class, 'recentActivity'])->name('dashboard.activity');
        Route::post('/dashboard/refresh', [DashboardController::class, 'clearCache'])->name('dashboard.refresh');

        // ��� Personel Y�netimi ��������������������������������������������������������
        // Personel rota dizili�i: JSON API'lar �nce, HTML g�r�n�m� sonra
        Route::prefix('personel')->name('personel.')->group(function () {
            // Widget verileri
            Route::get('/widgets', [PersonelController::class, 'widgetData'])->name('widgets');

            // JSON API endpointleri (HTTP Accept: application/json)
            Route::get('/list', [PersonelController::class, 'index'])->name('list');
            Route::post('/', [PersonelController::class, 'store'])->name('store');
            Route::post('/{personel}', [PersonelController::class, 'update'])->name('update');
            Route::delete('/{personel}', [PersonelController::class, 'destroy'])->name('destroy');
            Route::get('/{personel}/edit', [PersonelController::class, 'edit'])->name('edit');
            Route::get('/{personel}/show', [PersonelController::class, 'show'])->name('show');
            Route::get('/create', [PersonelController::class, 'create'])->name('create');
            Route::get('/{personel}/card', [PersonelController::class, 'card'])->name('card');
            
            // Export endpointleri
            Route::get('/export/excel', [PersonelController::class, 'exportExcel'])->name('exportExcel');
            Route::post('/{personel}/export/pdf', [PersonelController::class, 'exportPdf'])->name('exportPdf');
            Route::patch('/{personel}/toggle-active', [PersonelController::class, 'toggleActive'])->name('toggleActive');
            
            // HTML g�r�n�m� (index)
            Route::get('/', [PersonelController::class, 'indexView'])->name('index');
        });

        // ─── Personel Belge Yönetimi ─────────────────────────────
        Route::prefix('personel/{personel}/documents')->name('personel.documents.')->group(function () {
            Route::get('/', [\App\Modules\Personel\Controllers\PersonelDocumentController::class, 'index'])->name('index');
            Route::post('/', [\App\Modules\Personel\Controllers\PersonelDocumentController::class, 'store'])->name('store');
        });
        Route::prefix('personel/documents')->name('personel.documents.')->group(function () {
            Route::get('/{id}/download', [\App\Modules\Personel\Controllers\PersonelDocumentController::class, 'download'])->name('download');
            Route::get('/{id}/view', [\App\Modules\Personel\Controllers\PersonelDocumentController::class, 'view'])->name('view');
            Route::delete('/{id}', [\App\Modules\Personel\Controllers\PersonelDocumentController::class, 'destroy'])->name('destroy');
        });

        // ─── Şirket Yapısı ────────────────────────────────────────
        // Statik route'lar resource'dan ÖNCE tanımlanmalı (/{company} yakalamasın diye)
        Route::get('companies/departments', [CompanyController::class, 'departments'])->name('companies.departments');
        Route::get('companies/positions', [CompanyController::class, 'positions'])->name('companies.positions');
        Route::get('companies/personels', [CompanyController::class, 'personels'])->name('companies.personels');
        Route::get('companies/org-tree', [CompanyController::class, 'myOrgTree'])->name('companies.org-tree');
        Route::get('companies/dashboard', [CompanyController::class, 'dashboard'])->name('companies.dashboard');
        Route::get('companies/personel-create', [CompanyController::class, 'createPersonel'])->name('companies.personel-create');
        Route::post('companies/personel-store', [CompanyController::class, 'storePersonel'])->name('companies.personel-store');
        Route::get('companies/assign-form', [CompanyController::class, 'assignPersonelForm'])->name('companies.assign-form');
        Route::post('companies/assign-action', [CompanyController::class, 'assignPersonelToDept'])->name('companies.assign-action');
        Route::post('companies/unassign-action', [CompanyController::class, 'unassignPersonel'])->name('companies.unassign-action');
        Route::get('companies/{company}/org-tree', [CompanyController::class, 'orgTree'])->name('companies.org-tree.detail');
        Route::resource('companies', CompanyController::class);

        Route::get('departments/tree', [DepartmentController::class, 'tree'])->name('departments.tree');
        Route::post('departments/{department}/assign-personel', [DepartmentController::class, 'assignPersonel'])->name('departments.assign-personel');
        Route::post('departments/{department}/remove-personel', [DepartmentController::class, 'removePersonel'])->name('departments.remove-personel');
        Route::get('departments/{department}/personels', [DepartmentController::class, 'personels'])->name('departments.personels');
        Route::resource('departments', DepartmentController::class)->except(['show']);

        Route::resource('positions', PositionController::class)->except(['show']);

        // ─── İzin Yönetimi ────────────────────────────────────────
        Route::prefix('leave')->name('leave.')->group(function () {
            Route::get('/', [LeaveRequestController::class, 'indexView'])->name('index');
            Route::get('/list', [LeaveRequestController::class, 'index'])->name('list');
            Route::resource('types', LeaveTypeController::class)->names([
                'index'   => 'types.index',
                'create'  => 'types.create',
                'store'   => 'types.store',
                'edit'    => 'types.edit',
                'update'  => 'types.update',
                'destroy' => 'types.destroy',
            ]);
            Route::resource('requests', LeaveRequestController::class)->names([
                'index'   => 'requests.index',
                'create'  => 'requests.create',
                'store'   => 'requests.store',
                'show'    => 'requests.show',
                'edit'    => 'requests.edit',
                'update'  => 'requests.update',
                'destroy' => 'requests.destroy',
            ]);
            Route::get('export/excel', [LeaveRequestController::class, 'exportExcel'])->name('export.excel');
            Route::get('export/pdf', [LeaveRequestController::class, 'exportPdf'])->name('export.pdf');
            Route::post('requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('requests.approve');
            Route::post('requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('requests.reject');
            Route::post('requests/{leaveRequest}/cancel', [LeaveRequestController::class, 'cancel'])->name('requests.cancel');
            Route::get('balances', [LeaveRequestController::class, 'balances'])->name('balances');
            Route::post('balances/recalculate', [LeaveRequestController::class, 'recalculateBalances'])->name('balances.recalculate');
            Route::get('calendar', [LeaveRequestController::class, 'calendar'])->name('calendar');
            Route::get('validate-dates', [LeaveRequestController::class, 'validateDates'])->name('validate-dates');
        });

        // ─── Puantaj / Mesai (Basic) ────────────────────────────────
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/', [TimeRecordController::class, 'indexView'])->name('index');
            Route::get('/list', [TimeRecordController::class, 'index'])->name('list');
            Route::get('/daily-summary', [TimeRecordController::class, 'dailySummary'])->name('daily-summary');
            Route::get('/monthly-summary', [TimeRecordController::class, 'monthlySummary'])->name('monthly-summary');
            Route::post('record', [TimeRecordController::class, 'storeRecord'])->name('record');
            Route::patch('{timeRecord}/correct', [TimeRecordController::class, 'correct'])->name('correct');
            Route::delete('{timeRecord}', [TimeRecordController::class, 'destroy'])->name('destroy');
            Route::get('export', [TimeRecordController::class, 'export'])->name('export');
        });

        // ─── Puantaj / Mesai (Professional Module) ─────────────────
        Route::prefix('puantaj')->name('puantaj.')->group(function () {
            Route::get('/', [PuantajController::class, 'indexView'])->name('index');
            Route::get('/live-status', [PuantajController::class, 'liveStatus'])->name('live-status');
            Route::get('/daily-overview', [PuantajController::class, 'dailyOverview'])->name('daily-overview');
            Route::get('/personel-detail', [PuantajController::class, 'personelDetail'])->name('personel-detail');
            Route::get('/monthly-overview', [PuantajController::class, 'monthlyOverview'])->name('monthly-overview');
            Route::get('/today-stats', [PuantajController::class, 'todayStats'])->name('today-stats');
            Route::get('/shifts-today', [PuantajController::class, 'shiftsToday'])->name('shifts-today');
            Route::post('record', [TimeRecordController::class, 'storeRecord'])->name('record');
            Route::get('export', [PuantajController::class, 'exportExcel'])->name('export');
            Route::get('export-pdf', [PuantajController::class, 'exportPdf'])->name('export-pdf');
            Route::get('personel/{personel}/export/pdf', [PuantajController::class, 'exportPersonelPdf'])->name('export-personel-pdf');
        });

        // ─── Vardiya Yönetimi ─────────────────────────────────────
        Route::prefix('shifts')->name('shifts.')->group(function () {
            Route::get('/', [ShiftController::class, 'indexView'])->name('index');
            Route::get('/list', [ShiftController::class, 'index'])->name('list');
            Route::get('/create', [ShiftController::class, 'create'])->name('create');
            Route::post('/', [ShiftController::class, 'store'])->name('store');
            Route::get('/{shift}/edit', [ShiftController::class, 'edit'])->name('edit');
            Route::put('/{shift}', [ShiftController::class, 'update'])->name('update');
            Route::delete('/{shift}', [ShiftController::class, 'destroy'])->name('destroy');
            Route::get('/roster', [ShiftController::class, 'roster'])->name('roster');
            Route::post('/assign', [ShiftController::class, 'assign'])->name('assign');
            Route::delete('/assignments/{shiftAssignment}', [ShiftController::class, 'destroyAssignment'])->name('assignment.destroy');
            Route::get('/swap-requests', [ShiftController::class, 'swapRequests'])->name('swap.index');
            Route::post('/swap-requests/{id}/approve', [ShiftController::class, 'approveSwap'])->name('swap.approve');
            Route::post('/swap-requests/{id}/reject', [ShiftController::class, 'rejectSwap'])->name('swap.reject');
            Route::get('/plans', [ShiftController::class, 'plans'])->name('plans.index');
            Route::post('/plans', [ShiftController::class, 'storePlan'])->name('plans.store');
            // Canlı yoklama
            Route::post('/clock-in', [ShiftController::class, 'clockIn'])->name('clock-in');
            Route::post('/clock-out', [ShiftController::class, 'clockOut'])->name('clock-out');
            Route::get('/live-status', [ShiftController::class, 'liveStatus'])->name('live-status');
            Route::get('/attendance-history', [ShiftController::class, 'attendanceHistory'])->name('attendance-history');
            // Widget
            Route::get('/widget-data', [ShiftController::class, 'widgetData'])->name('widget-data');
            // Export
            Route::get('/export/excel', [ShiftController::class, 'exportExcel'])->name('export.excel');
            Route::get('/export/pdf', [ShiftController::class, 'exportPdf'])->name('export.pdf');
        });

        // ─── Envanter Yönetimi ────────────────────────────────────
        Route::prefix('assets')->name('assets.')->group(function () {
            Route::get('/', [AssetController::class, 'indexView'])->name('index');
            Route::get('/list', [AssetController::class, 'index'])->name('list');
            Route::get('/create', [AssetController::class, 'create'])->name('create');
            Route::post('/', [AssetController::class, 'store'])->name('store');
            Route::get('/{asset}/edit', [AssetController::class, 'edit'])->name('edit');
            Route::put('/{asset}', [AssetController::class, 'update'])->name('update');
            Route::delete('/{asset}', [AssetController::class, 'destroy'])->name('destroy');
            Route::post('/{asset}/assign', [AssetController::class, 'assign'])->name('assign');
            Route::post('/{asset}/return', [AssetController::class, 'return'])->name('return');
            Route::get('/{asset}/history', [AssetController::class, 'history'])->name('history');
            Route::get('/assignments/{assetAssignment}/pdf', [AssetController::class, 'zimmetPdf'])->name('zimmet-pdf');
            Route::get('/generate-serial', [AssetController::class, 'generateSerial'])->name('generate-serial');
            Route::get('/generate-barcode', [AssetController::class, 'generateBarcode'])->name('generate-barcode');
            Route::get('/types', [AssetController::class, 'types'])->name('types.index');
            Route::post('/types', [AssetController::class, 'storeType'])->name('types.store');
        });

        // ─── Avans Yönetimi ───────────────────────────────────────
        Route::prefix('advances')->name('advance.')->group(function () {
            Route::get('/', [AdvanceController::class, 'indexView'])->name('index');
            Route::get('/list', [AdvanceController::class, 'index'])->name('list');
            Route::get('/create', [AdvanceController::class, 'create'])->name('create');
            Route::post('/', [AdvanceController::class, 'store'])->name('store');
            Route::delete('/requests/{advance}', [AdvanceController::class, 'destroy'])->name('destroy');
            Route::post('/requests/{advance}/approve', [AdvanceController::class, 'approve'])->name('requests.approve');
            Route::post('/requests/{advance}/reject', [AdvanceController::class, 'reject'])->name('requests.reject');
            Route::post('/requests/{advance}/cancel', [AdvanceController::class, 'cancel'])->name('requests.cancel');
            Route::post('/requests/{advance}/repaid', [AdvanceController::class, 'markRepaid'])->name('requests.repaid');
        });

        // ─── Masraf Yönetimi ──────────────────────────────────────
        Route::prefix('expenses')->name('expense.')->group(function () {
            Route::get('/', [ExpenseController::class, 'indexView'])->name('index');
            Route::get('/list', [ExpenseController::class, 'index'])->name('list');
            Route::get('/create', [ExpenseController::class, 'create'])->name('create');
            Route::post('/', [ExpenseController::class, 'store'])->name('store');
            Route::delete('/requests/{expense}', [ExpenseController::class, 'destroy'])->name('destroy');
            Route::post('/requests/{expense}/approve', [ExpenseController::class, 'approve'])->name('requests.approve');
            Route::post('/requests/{expense}/reject', [ExpenseController::class, 'reject'])->name('requests.reject');
            Route::post('/requests/{expense}/paid', [ExpenseController::class, 'markPaid'])->name('requests.paid');
            Route::get('/requests/{expense}/attachments/{index}', [ExpenseController::class, 'viewAttachment'])->name('requests.attachment');
            Route::get('/categories', [ExpenseController::class, 'categories'])->name('categories.index');
            Route::post('/categories', [ExpenseController::class, 'storeCategory'])->name('categories.store');
        });

        // ─── Seyahat & Araç ───────────────────────────────────────
        Route::prefix('travel')->name('travel.')->group(function () {
            Route::get('/', [\App\Modules\Seyahat\Controllers\TravelController::class, 'indexView'])->name('index');
            Route::get('/widgets', [\App\Modules\Seyahat\Controllers\TravelController::class, 'widgetData'])->name('widgets');
            Route::get('/list', [\App\Modules\Seyahat\Controllers\TravelController::class, 'index'])->name('list');
            Route::get('/create', [\App\Modules\Seyahat\Controllers\TravelController::class, 'create'])->name('create');
            Route::post('/', [\App\Modules\Seyahat\Controllers\TravelController::class, 'store'])->name('store');
            Route::get('/{travel}', [\App\Modules\Seyahat\Controllers\TravelController::class, 'show'])->name('show');
            Route::get('/{travel}/edit', [\App\Modules\Seyahat\Controllers\TravelController::class, 'edit'])->name('edit');
            Route::put('/{travel}', [\App\Modules\Seyahat\Controllers\TravelController::class, 'update'])->name('update');
            Route::delete('/{travel}', [\App\Modules\Seyahat\Controllers\TravelController::class, 'destroy'])->name('destroy');
            Route::post('/{travel}/approve', [\App\Modules\Seyahat\Controllers\TravelController::class, 'approve'])->name('approve');
            Route::post('/{travel}/reject', [\App\Modules\Seyahat\Controllers\TravelController::class, 'reject'])->name('reject');
            Route::post('/{travel}/cancel', [\App\Modules\Seyahat\Controllers\TravelController::class, 'cancel'])->name('cancel');
            Route::post('/{travel}/complete', [\App\Modules\Seyahat\Controllers\TravelController::class, 'complete'])->name('complete');
            Route::get('/export/excel', [\App\Modules\Seyahat\Controllers\TravelController::class, 'exportExcel'])->name('export.excel');
            Route::get('/export/pdf', [\App\Modules\Seyahat\Controllers\TravelController::class, 'exportPdf'])->name('export.pdf');
        });

        Route::prefix('vehicles')->name('vehicles.')->group(function () {
            Route::get('/', [\App\Modules\Arac\Controllers\VehicleController::class, 'indexView'])->name('index');
            Route::get('/widgets', [\App\Modules\Arac\Controllers\VehicleController::class, 'widgetData'])->name('widgets');
            Route::get('/list', [\App\Modules\Arac\Controllers\VehicleController::class, 'index'])->name('list');
            Route::get('/create', [\App\Modules\Arac\Controllers\VehicleController::class, 'create'])->name('create');
            Route::post('/', [\App\Modules\Arac\Controllers\VehicleController::class, 'store'])->name('store');
            Route::get('/{vehicle}', [\App\Modules\Arac\Controllers\VehicleController::class, 'show'])->name('show');
            Route::get('/{vehicle}/edit', [\App\Modules\Arac\Controllers\VehicleController::class, 'edit'])->name('edit');
            Route::put('/{vehicle}', [\App\Modules\Arac\Controllers\VehicleController::class, 'update'])->name('update');
            Route::delete('/{vehicle}', [\App\Modules\Arac\Controllers\VehicleController::class, 'destroy'])->name('destroy');
            Route::get('/export/excel', [\App\Modules\Arac\Controllers\VehicleController::class, 'exportExcel'])->name('export.excel');
            Route::get('/export/pdf', [\App\Modules\Arac\Controllers\VehicleController::class, 'exportPdf'])->name('export.pdf');

            // Yakıt Kayıtları
            Route::get('/fuel/list', [\App\Modules\Arac\Controllers\VehicleController::class, 'fuelIndex'])->name('fuel.list');
            Route::post('/fuel', [\App\Modules\Arac\Controllers\VehicleController::class, 'fuelStore'])->name('fuel.store');
            Route::put('/fuel/{fuelRecord}', [\App\Modules\Arac\Controllers\VehicleController::class, 'fuelUpdate'])->name('fuel.update');
            Route::delete('/fuel/{fuelRecord}', [\App\Modules\Arac\Controllers\VehicleController::class, 'fuelDestroy'])->name('fuel.destroy');
            Route::get('/fuel/widgets', [\App\Modules\Arac\Controllers\VehicleController::class, 'fuelWidgetData'])->name('fuel.widgets');

            // Kullanım Kayıtları
            Route::get('/usage/list', [\App\Modules\Arac\Controllers\VehicleController::class, 'usageIndex'])->name('usage.list');
            Route::post('/usage', [\App\Modules\Arac\Controllers\VehicleController::class, 'usageStore'])->name('usage.store');
            Route::put('/usage/{usageLog}', [\App\Modules\Arac\Controllers\VehicleController::class, 'usageUpdate'])->name('usage.update');
            Route::post('/usage/{usageLog}/complete', [\App\Modules\Arac\Controllers\VehicleController::class, 'usageComplete'])->name('usage.complete');
            Route::delete('/usage/{usageLog}', [\App\Modules\Arac\Controllers\VehicleController::class, 'usageDestroy'])->name('usage.destroy');
        });

        // ─── Hizmet & Ziyaretçi ───────────────────────────────────
        Route::prefix('services')->name('services.')->group(function () {
            Route::get('/', [\App\Modules\Hizmet\Controllers\ServiceController::class, 'indexView'])->name('index');
            Route::get('/list', [\App\Modules\Hizmet\Controllers\ServiceController::class, 'index'])->name('list');
            Route::get('/create', [\App\Modules\Hizmet\Controllers\ServiceController::class, 'create'])->name('create');
            Route::post('/', [\App\Modules\Hizmet\Controllers\ServiceController::class, 'store'])->name('store');
            Route::get('/{service}', [\App\Modules\Hizmet\Controllers\ServiceController::class, 'show'])->name('show');
            Route::get('/{service}/edit', [\App\Modules\Hizmet\Controllers\ServiceController::class, 'edit'])->name('edit');
            Route::put('/{service}', [\App\Modules\Hizmet\Controllers\ServiceController::class, 'update'])->name('update');
            Route::delete('/{service}', [\App\Modules\Hizmet\Controllers\ServiceController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('visitors')->name('visitors.')->group(function () {
            Route::get('/', [VisitorController::class, 'indexView'])->name('index');
            Route::get('/list', [VisitorController::class, 'index'])->name('list');
            Route::get('/create', [VisitorController::class, 'create'])->name('create');
            Route::post('/', [VisitorController::class, 'store'])->name('store');
            Route::get('/{visitor}', [VisitorController::class, 'show'])->name('show');
            Route::get('/{visitor}/edit', [VisitorController::class, 'edit'])->name('edit');
            Route::put('/{visitor}', [VisitorController::class, 'update'])->name('update');
            Route::delete('/{visitor}', [VisitorController::class, 'destroy'])->name('destroy');
            Route::post('/{visitor}/checkin', [VisitorController::class, 'checkin'])->name('checkin');
            Route::post('/{visitor}/checkout', [VisitorController::class, 'checkout'])->name('checkout');
            Route::get('/{visitor}/badge', [VisitorController::class, 'badge'])->name('badge');
        });

        // ─── Etkileşim (Duyurular, Anketler) ─────────────────────
        Route::prefix('interactions')->name('interactions.')->group(function () {
            Route::get('/', [AnnouncementController::class, 'indexView'])->name('index');
            Route::prefix('announcements')->name('announcements.')->group(function () {
                Route::get('/', [AnnouncementController::class, 'index'])->name('index');
                Route::post('/', [AnnouncementController::class, 'store'])->name('store');
                Route::put('/{announcement}', [AnnouncementController::class, 'update'])->name('update');
                Route::delete('/{announcement}', [AnnouncementController::class, 'destroy'])->name('destroy');
            });
            Route::prefix('polls')->name('polls.')->group(function () {
                Route::get('/', [AnnouncementController::class, 'polls'])->name('index');
                Route::post('/', [AnnouncementController::class, 'storePoll'])->name('store');
                Route::post('/{poll}/vote', [AnnouncementController::class, 'votePoll'])->name('vote');
            });
        });

        // ─── CMS (Site İçerik Yönetimi) ──────────────────────────
        Route::prefix('cms')->name('cms.')->group(function () {
            Route::get('/', [CmsController::class, 'dashboard'])->name('index');
            Route::get('/contents', [CmsController::class, 'getContents'])->name('contents');
            Route::post('/contents', [CmsController::class, 'upsertContent'])->name('upsert');
            Route::prefix('blog')->name('blog.')->group(function () {
                Route::get('/', [CmsController::class, 'blogIndex'])->name('index');
                Route::get('/create', [CmsController::class, 'blogCreate'])->name('create');
                Route::post('/', [CmsController::class, 'blogStore'])->name('store');
                Route::get('/{blog}/edit', [CmsController::class, 'blogEdit'])->name('edit');
                Route::put('/{blog}', [CmsController::class, 'blogUpdate'])->name('update');
                Route::delete('/{blog}', [CmsController::class, 'blogDestroy'])->name('destroy');
                Route::prefix('categories')->name('categories.')->group(function () {
                    Route::get('/', [CmsController::class, 'blogCategories'])->name('index');
                    Route::post('/', [CmsController::class, 'storeBlogCategory'])->name('store');
                    Route::put('/{blogCategory}', [CmsController::class, 'updateBlogCategory'])->name('update');
                    Route::delete('/{blogCategory}', [CmsController::class, 'destroyBlogCategory'])->name('destroy');
                });
            });
            Route::get('/partners', [CmsController::class, 'partners'])->name('partners');
            Route::post('/partners', [CmsController::class, 'storePartner'])->name('partners.store');
            Route::put('/partners/{partnerLogo}', [CmsController::class, 'updatePartner'])->name('partners.update');
            Route::delete('/partners/{partnerLogo}', [CmsController::class, 'destroyPartner'])->name('partners.destroy');
        });

        // ─── Süreç Yönetimi ───────────────────────────────────────
        Route::prefix('processes')->name('processes.')->group(function () {
            Route::get('/', [ProcessController::class, 'indexView'])->name('index');
            Route::get('/kpi', [ProcessController::class, 'kpi'])->name('kpi');
            Route::get('/templates', [ProcessController::class, 'templates'])->name('templates');
            Route::post('/templates', [ProcessController::class, 'storeTemplate'])->name('store');
            Route::get('/templates/{processTemplate}/edit', [ProcessController::class, 'editTemplate'])->name('templates.edit');
            Route::put('/templates/{processTemplate}', [ProcessController::class, 'updateTemplate'])->name('templates.update');
            Route::delete('/templates/{processTemplate}', [ProcessController::class, 'destroyTemplate'])->name('templates.destroy');
            Route::put('/templates/{processTemplate}/toggle', [ProcessController::class, 'toggleTemplate'])->name('templates.toggle');
            Route::get('/instances', [ProcessController::class, 'instances'])->name('instances');
            Route::post('/instantiate', [ProcessController::class, 'instantiate'])->name('instantiate');
            Route::post('/instances/{processInstance}/complete-step', [ProcessController::class, 'completeStep'])->name('complete-step');
            Route::get('/instances/{processInstance}', [ProcessController::class, 'showInstance'])->name('instances.show');
        });

        // ─── Abonelik ─────────────────────────────────────────────
        Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
            Route::get('/', [SubscriptionController::class, 'index'])->name('index');
            Route::get('/billing', [SubscriptionController::class, 'billing'])->name('billing');
            Route::get('/invoices', [SubscriptionController::class, 'invoices'])->name('invoices');
            Route::get('/current', [SubscriptionController::class, 'currentSubscription'])->name('current');
            Route::post('/upgrade', [SubscriptionController::class, 'upgrade'])->name('upgrade');
            Route::get('/plans', [SubscriptionController::class, 'plans'])->name('plans');
            Route::post('/plans', [SubscriptionController::class, 'storePlan'])->name('plans.store');
            Route::put('/plans/{plan}', [SubscriptionController::class, 'updatePlan'])->name('plans.update');
            Route::delete('/plans/{plan}', [SubscriptionController::class, 'destroyPlan'])->name('plans.destroy');
        });

        // ─── Tatil Yönetimi ───────────────────────────────────────
        Route::prefix('holidays')->name('holidays.')->group(function () {
            Route::get('/', [\App\Modules\Tatil\Controllers\HolidayController::class, 'indexView'])->name('index');
            Route::get('/list', [\App\Modules\Tatil\Controllers\HolidayController::class, 'index'])->name('list');
            Route::post('/', [\App\Modules\Tatil\Controllers\HolidayController::class, 'store'])->name('store');
            Route::get('/{holiday}/edit', [\App\Modules\Tatil\Controllers\HolidayController::class, 'edit'])->name('edit');
            Route::put('/{holiday}', [\App\Modules\Tatil\Controllers\HolidayController::class, 'update'])->name('update');
            Route::delete('/{holiday}', [\App\Modules\Tatil\Controllers\HolidayController::class, 'destroy'])->name('destroy');
            Route::post('/seed', [\App\Modules\Tatil\Controllers\HolidayController::class, 'seedYear'])->name('seed');
        });

        // ─── Raporlar ─────────────────────────────────────────────
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ReportsController::class, 'index'])->name('index');
            Route::post('/generate', [\App\Http\Controllers\Admin\ReportsController::class, 'generate'])->name('generate');
            Route::get('/download/{exportId}', [\App\Http\Controllers\Admin\ReportsController::class, 'download'])->name('download');
        });

        // ─── Ayarlar ──────────────────────────────────────────────
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('index');
            Route::get('/load', [\App\Http\Controllers\Admin\SettingsController::class, 'load'])->name('load');
            Route::post('/save', [\App\Http\Controllers\Admin\SettingsController::class, 'save'])->name('save');
        });

        // ─── Rol & Yetki (Production Ready) ──────────────────────
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\RoleController::class, 'indexView'])->name('index');
            Route::get('/list', [\App\Http\Controllers\Admin\RoleController::class, 'index'])->name('list');
            Route::post('/', [\App\Http\Controllers\Admin\RoleController::class, 'store'])->name('store');
            Route::delete('/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'destroy'])->name('destroy');
            Route::get('/{role}/permissions', [\App\Http\Controllers\Admin\RoleController::class, 'permissions'])->name('permissions');
            Route::post('/{role}/permissions', [\App\Http\Controllers\Admin\RoleController::class, 'syncPermissions'])->name('permissions.sync');
            Route::post('/assign-user', [\App\Http\Controllers\Admin\RoleController::class, 'assignToUser'])->name('assign-user');
            Route::post('/revoke-user', [\App\Http\Controllers\Admin\RoleController::class, 'revokeFromUser'])->name('revoke-user');
            Route::get('/users', [\App\Http\Controllers\Admin\RoleController::class, 'users'])->name('users');
            Route::get('/{role}/users', [\App\Http\Controllers\Admin\RoleController::class, 'usersByRole'])->name('users.by-role');
        });
        Route::prefix('permissions')->name('permissions.')->group(function () {
            Route::get('/list', [\App\Http\Controllers\Admin\PermissionController::class, 'index'])->name('list');
        });

        // ─── Bildirimler (Production Ready) ──────────────────────
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [\App\Http\Controllers\NotificationController::class, 'index'])->name('index');
            Route::get('/recent', [\App\Http\Controllers\NotificationController::class, 'recent'])->name('recent');
            Route::get('/unread-count', [\App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('unread');
            Route::post('/mark-read', [\App\Http\Controllers\NotificationController::class, 'markRead'])->name('markRead');
            Route::delete('/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('destroy');
        });

        // ─── Export Tetikleyiciler (Production Ready) ─────────────
        Route::prefix('exports')->name('exports.')->group(function () {
            Route::get('/personel/excel', [\App\Modules\Personel\Controllers\PersonelController::class, 'exportExcel'])->name('personel.excel');
            Route::get('/personel/{personel}/pdf', [\App\Modules\Personel\Controllers\PersonelController::class, 'exportPdf'])->name('personel.pdf');
            Route::get('/attendance/excel', [\App\Modules\Puantaj\Controllers\TimeRecordController::class, 'export'])->name('attendance.excel');
            Route::get('/status', function(\Illuminate\Http\Request $req) {
                $export = \Illuminate\Support\Facades\DB::table('exports')
                    ->where('company_id', auth()->user()->company_id)
                    ->when($req->filled('module'), fn ($q) => $q->where('module', $req->module))
                    ->when($req->filled('export_id'), fn ($q) => $q->where('id', $req->export_id))
                    ->orderByDesc('created_at')
                    ->first();
                return response()->json(['data' => $export]);
            })->name('status');
        });

        // ─── Özel Saat (Üst Düzey Personel Devam) ────────────────
        Route::prefix('ozel-saat')->name('ozel-saat.')->group(function () {
            Route::get('/', [SpecialAttendanceController::class, 'index'])->name('index');
            Route::get('/list', [SpecialAttendanceController::class, 'list'])->name('list');
            Route::post('/toggle', [SpecialAttendanceController::class, 'toggle'])->name('toggle');
            Route::post('/mark', [SpecialAttendanceController::class, 'markAttendance'])->name('mark');
            Route::post('/mark-all', [SpecialAttendanceController::class, 'markAllToday'])->name('mark-all');
            Route::get('/report', [SpecialAttendanceController::class, 'monthlyReport'])->name('report');
        });

        // ─── Medya Kütüphanesi ────────────────────────────────────
        Route::prefix('media')->name('media.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\MediaController::class, 'index'])->name('index');
            Route::get('/list', [\App\Http\Controllers\Admin\MediaController::class, 'list'])->name('list');
            Route::post('/upload', [\App\Http\Controllers\Admin\MediaController::class, 'upload'])->name('upload');
            Route::delete('/{id}', [\App\Http\Controllers\Admin\MediaController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-delete', [\App\Http\Controllers\Admin\MediaController::class, 'bulkDelete'])->name('bulk-delete');
        });

        // ─── Lokasyon Yönetimi ─────────────────────────────────────
        Route::prefix('lokasyon')->name('lokasyon.')->group(function () {
            Route::get('/', [\App\Modules\Lokasyon\Controllers\LocationController::class, 'indexView'])->name('index');
            Route::get('/list', [\App\Modules\Lokasyon\Controllers\LocationController::class, 'index'])->name('list');
            Route::get('/create', [\App\Modules\Lokasyon\Controllers\LocationController::class, 'create'])->name('create');
            Route::post('/', [\App\Modules\Lokasyon\Controllers\LocationController::class, 'store'])->name('store');
            Route::get('/{lokasyon}/edit', [\App\Modules\Lokasyon\Controllers\LocationController::class, 'edit'])->name('edit');
            Route::put('/{lokasyon}', [\App\Modules\Lokasyon\Controllers\LocationController::class, 'update'])->name('update');
            Route::delete('/{lokasyon}', [\App\Modules\Lokasyon\Controllers\LocationController::class, 'destroy'])->name('destroy');
            Route::get('/map-data', [\App\Modules\Lokasyon\Controllers\LocationController::class, 'mapData'])->name('map-data');
            Route::post('/{lokasyon}/assign-personels', [\App\Modules\Lokasyon\Controllers\LocationController::class, 'assignPersonels'])->name('assign-personels');
            Route::post('/{lokasyon}/assign-by-department', [\App\Modules\Lokasyon\Controllers\LocationController::class, 'assignByDepartment'])->name('assign-by-department');
            Route::match(['delete', 'post'], '/{lokasyon}/remove-personel/{personel}', [\App\Modules\Lokasyon\Controllers\LocationController::class, 'removePersonel'])->name('remove-personel');
            Route::get('/{lokasyon}/personels', [\App\Modules\Lokasyon\Controllers\LocationController::class, 'personels'])->name('personels');
            Route::get('/check-distance', [\App\Modules\Lokasyon\Controllers\LocationController::class, 'checkDistance'])->name('check-distance');
            Route::post('/types', [\App\Modules\Lokasyon\Controllers\LocationController::class, 'storeType'])->name('types.store');
        });

        // ─── Özel Saat ────────────────────────────────────────────
        Route::prefix('ozel-saat')->name('special-hour.')->group(function () {
            Route::get('/', [\App\Modules\SpecialHour\Controllers\SpecialHourController::class, 'index'])->name('index');
            Route::post('verify-password', [\App\Modules\SpecialHour\Controllers\SpecialHourController::class, 'verifyPassword'])->name('verify-password');
            Route::post('set-password', [\App\Modules\SpecialHour\Controllers\SpecialHourController::class, 'setPassword'])->name('set-password');
            Route::post('store', [\App\Modules\SpecialHour\Controllers\SpecialHourController::class, 'store'])->name('store');
            Route::post('bulk-store', [\App\Modules\SpecialHour\Controllers\SpecialHourController::class, 'bulkStore'])->name('bulk-store');
            Route::match(['put', 'patch'], '{specialHour}', [\App\Modules\SpecialHour\Controllers\SpecialHourController::class, 'update'])->name('update');
            Route::delete('{specialHour}', [\App\Modules\SpecialHour\Controllers\SpecialHourController::class, 'destroy'])->name('destroy');
        });

        // ─── Dökümantasyon ─────────────────────────────────────────
        Route::get('dokumantasyon/{category?}/{page?}', [\App\Modules\Dokumantasyon\Controllers\DokumantasyonController::class, 'index'])->name('dokumantasyon.page');

        // ─── Takvim ───────────────────────────────────────────────
        Route::get('calendar', fn() => view('admin.dashboard.index'))->name('calendar.index');

        // ─── Yardım Merkezi ───────────────────────────────────────
        Route::prefix('help')->name('help.')->group(function () {
            Route::get('/', fn() => view('admin.dashboard.index'))->name('index');
        });

        // ─── QR Giriş/Çıkış ───────────────────────────────────────
        Route::prefix('qr')->name('qr.')->group(function () {
            Route::get('kiosk', [\App\Http\Controllers\QrScanController::class, 'kiosk'])->name('kiosk');
            Route::get('personel/{personel}/qrcode', [\App\Http\Controllers\QrScanController::class, 'personelQrCode'])->name('personel.qrcode');
        });

    });






