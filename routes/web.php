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

// Legal Pages
Route::get('/gizlilik-politikasi', fn() => view('frontend.legal', ['title' => 'Gizlilik Politikası']))->name('privacy');
Route::get('/kullanim-sartlari', fn() => view('frontend.legal', ['title' => 'Kullanım Şartları']))->name('terms');
Route::get('/kvkk', fn() => view('frontend.legal', ['title' => 'KVKK Aydınlatma Metni']))->name('kvkk');

// ─── Auth (Laravel Breeze) ────────────────────────────────────────────────────
require __DIR__ . '/auth.php';

// ─── Authenticated: Dashboard redirect + Profile ─────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn() => redirect()->route('admin.dashboard'))->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ─── Admin Panel Rotaları ─────────────────────────────────────────────────────
require __DIR__ . '/admin.php';
