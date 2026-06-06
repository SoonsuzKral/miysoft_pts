<?php
// MİYSOFT PTS — Admin rotaları: prefix admin, middleware auth + verified + can:access_admin
// (login/register bu dosyada yok; yalnızca routes/auth.php guest grubunda.)

use Illuminate\Support\Facades\Route;
use App\Modules\Personel\Controllers\PersonelController;
use App\Modules\Dashboard\Controllers\DashboardController;
use App\Modules\Izin\Controllers\LeaveTypeController;
use App\Modules\Izin\Controllers\LeaveRequestController;
use App\Modules\Puantaj\Controllers\TimeRecordController;
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

Route::prefix('admin')
    ->middleware(['auth', 'verified', 'can:access_admin'])
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
            Route::delete('/{id}', [\App\Modules\Personel\Controllers\PersonelDocumentController::class, 'destroy'])->name('destroy');
        });

        // ─── Şirket Yapısı ────────────────────────────────────────
        Route::resource('companies', CompanyController::class);
        Route::resource('departments', DepartmentController::class)->except(['show']);
        Route::get('departments/tree', [DepartmentController::class, 'tree'])->name('departments.tree');
        Route::resource('positions', PositionController::class)->except(['show', 'edit']);

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
            Route::post('requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('requests.approve');
            Route::post('requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('requests.reject');
            Route::post('requests/{leaveRequest}/cancel', [LeaveRequestController::class, 'cancel'])->name('requests.cancel');
            Route::get('balances', [LeaveRequestController::class, 'balances'])->name('balances');
            Route::get('calendar', [LeaveRequestController::class, 'calendar'])->name('calendar');
        });

        // ─── Puantaj / Mesai ──────────────────────────────────────
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/', [TimeRecordController::class, 'indexView'])->name('index');
            Route::get('/list', [TimeRecordController::class, 'index'])->name('list');
            Route::get('/daily-summary', [TimeRecordController::class, 'dailySummary'])->name('daily-summary');
            Route::post('record', [TimeRecordController::class, 'storeRecord'])->name('record');
            Route::patch('{timeRecord}/correct', [TimeRecordController::class, 'correct'])->name('correct');
            Route::delete('{timeRecord}', [TimeRecordController::class, 'destroy'])->name('destroy');
            Route::get('export', [TimeRecordController::class, 'export'])->name('export');
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
            Route::get('/categories', [ExpenseController::class, 'categories'])->name('categories.index');
            Route::post('/categories', [ExpenseController::class, 'storeCategory'])->name('categories.store');
        });

        // ─── Seyahat & Araç ───────────────────────────────────────
        Route::resource('travel', \App\Modules\Seyahat\Controllers\TravelController::class)->only(['index']);
        Route::resource('vehicles', \App\Modules\Arac\Controllers\VehicleController::class)->only(['index']);
        Route::get('vehicles/{vehicle}/logs', [\App\Modules\Arac\Controllers\VehicleController::class, 'logs'])->name('vehicles.logs');

        // ─── Hizmet & Ziyaretçi ───────────────────────────────────
        Route::resource('services', \App\Modules\Hizmet\Controllers\ServiceController::class)->only(['index']);
        Route::resource('visitors', VisitorController::class)->only(['index']);
        Route::get('visitors/{visitor}/badge', [VisitorController::class, 'badge'])->name('visitors.badge');

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
            Route::get('/', fn() => view('admin.holidays.index'))->name('index');
        });

        // ─── Raporlar ─────────────────────────────────────────────
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', fn() => view('admin.raporlar.index'))->name('index');
        });

        // ─── Ayarlar ──────────────────────────────────────────────
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', fn() => view('admin.ayarlar.index'))->name('index');
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
                    ->orderByDesc('created_at')
                    ->first();
                return response()->json(['data' => $export]);
            })->name('status');
        });

        // ─── Takvim ───────────────────────────────────────────────
        Route::get('calendar', fn() => view('admin.dashboard.index'))->name('calendar.index');

        // ─── Yardım Merkezi ───────────────────────────────────────
        Route::prefix('help')->name('help.')->group(function () {
            Route::get('/', fn() => view('admin.dashboard.index'))->name('index');
        });
    });

