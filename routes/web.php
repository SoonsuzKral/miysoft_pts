<?php
// MİYSOFT PTS — Public + Auth + Admin
// Giriş/kayıt yalnızca routes/auth.php içinde middleware('guest') grubundadır; burada auth kullanılmaz.

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\FrontendController;
use App\Http\Controllers\ProfileController;

// ─── Public Site Rotaları ─────────────────────────────────────────────────────
Route::get('/', [FrontendController::class, 'index'])->name('home');
Route::get('/hakkimizda', [FrontendController::class, 'about'])->name('about');
Route::get('/urun', [FrontendController::class, 'pricing'])->name('product');
Route::get('/fiyatlandirma', [FrontendController::class, 'pricing'])->name('pricing');
Route::get('/iletisim', [FrontendController::class, 'contact'])->name('contact');
Route::post('/iletisim', [FrontendController::class, 'storeContact'])->name('contact.store');
Route::get('/ucretsiz-deneyin', [FrontendController::class, 'freeTrial'])->name('free-trial');
Route::post('/ucretsiz-deneyin', [FrontendController::class, 'storeFreeTrial'])->name('free-trial.store');

// Blog
Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', [FrontendController::class, 'blog'])->name('index');
    Route::get('/{slug}', [FrontendController::class, 'blogShow'])->name('show');
});

// Legal Pages (CMS yönetilebilir)
Route::get('/kvkk', [FrontendController::class, 'legalPage'])->defaults('page', 'kvkk')->name('kvkk');
Route::get('/gizlilik-politikasi', [FrontendController::class, 'legalPage'])->defaults('page', 'privacy')->name('privacy');
Route::get('/kullanim-sartlari', [FrontendController::class, 'legalPage'])->defaults('page', 'terms')->name('terms');

// ─── Auth (Laravel Breeze) ────────────────────────────────────────────────────
require __DIR__ . '/auth.php';

// ─── Authenticated: Dashboard redirect + Profile ─────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn() => redirect()->route('admin.dashboard'))->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ─── QR Giriş/Çıkış (Public — auth gerekmez) ─────────────────────────────────
Route::prefix('qr')->name('qr.')->group(function () {
    Route::get('/scan/{token}', [\App\Http\Controllers\QrScanController::class, 'scanView'])->name('scan.view');
    Route::post('/scan/{token}/submit', [\App\Http\Controllers\QrScanController::class, 'submit'])->name('scan.submit');
});

// ─── Admin Panel Rotaları ─────────────────────────────────────────────────────
require __DIR__ . '/admin.php';
