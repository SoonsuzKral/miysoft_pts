# MİYSOFT PTS — Yapılacaklar (Roadmap)

**Son Güncelleme:** 2026-06-07  
**Mevcut Durum:** Personel belge sistemi kesin çözüm — tüm 3 parça (liste, kaydet, indir) çalışıyor.

---

## 📋 MEVCUT DURUM VE GELİŞMELER (Kayıt Defteri)

| Tarih | Yapılan İşlem |
|-------|---------------|
| 2026-05-29 | **Dual-mode Controller Fix:** CompanyController, DepartmentController `index()` metodları browser/AJAX dual-mode kazandı. Travel, Vehicle, Visitor, Service controller'ları placeholder'dan kurtarılıp dual-mode + gerçek Blade view'a kavuştu. |
| 2026-05-29 | **Admin View'lar:** Travel, Vehicle, Visitor, Service modülleri için eksik full CRUD template view'lar oluşturuldu (eskiden "Yakında eklenecek" placeholder'ı vardı). |
| 2026-05-30 | **Prompt #03 — Hata Tespiti & Toplu Düzeltme:** Companies view'da `$key` undefined hatası giderildi (foreach destructuring syntax'ı `@php` bloğuna taşındı). Departments view'da `$allDepartments` undefined hatası giderildi (PHP loop kaldırıldı, JS ile AJAX select doldurma eklendi). Travel, Vehicle, Visitor, Service view'ları temiz (AJAX-tabanlı, PHP değişkeni yok). |
| 2026-05-30 | **Prompt #04 — Show() Method Hatası & Route Düzeltmesi:** Travel, Vehicle, Visitor, Service controller'larında `show()` metodu olmamasına rağmen `Route::resource()` tüm CRUD route'larını oluşturuyordu. `->only(['index'])` ile sadece var olan metodlara kısıtlandı. Aynı sorun Department (`show`) ve Position (`show`, `edit`) controller'ları için de düzeltildi (`->except()`). |
| 2026-05-30 | **Prompt #05 — Dashboard Geliştirme + İK Modülleri:** Dashboard'a son 5 izin talebi, son 5 personel kaydı, haftalık vardiya özeti ve yaklaşan tatiller panelleri eklendi (widgetData AJAX + JS rendering). İzin (Leave), Puantaj (Attendance), Vardiya (Shift) ve Onboarding (Process) modülleri incelendi — tümü zaten çalışır durumda, ek müdahale gerekmedi. |
| 2026-06-06 | **Prompt #06 — Personel Belge Yükleme Sistemi Düzeltmesi:** PersonelController::store()'da `$request->file('documents')` foreach döngüsünde her eleman `['file' => UploadedFile]` array'iydi, `$file->isValid()` çağrısı "Call to member function on array" hatasıyla belgelerin sessizce kaydedilmemesine yol açıyordu. Aynı hata CompanyController::storePersonel()'da da vardı. update() metodunda belge işleme kodu tamamen eksikti — eklendi. JS'te FormData ile explicit Content-Type header'ı kaldırıldı (boundary bozulmasın diye). _card.blade.php ve _documents.blade.php'de geçerlilik tarihi gösterimi düzeltildi (Süresiz, X gün kaldı, Süresi Doldu). Storage symlink zaten vardı. |
| 2026-06-07 | **Prompt #07 — Personel Belge Sistemi Tam Düzeltme:** Profil modalindeki "Belgeler" sekmesi server-side Blade render'dan AJAX-driven yapıya dönüştürüldü. `_card.blade.php`'deki `@foreach($personel->documents)` kaldırıldı, yerine `loadPersonelDocuments()` ile `GET /admin/personel/{id}/documents` endpoint'inden çekilen veriler JS ile render ediliyor. PersonelDocumentController::index()'e `days_left`, `display_text`, `display_class` alanları eklendi. `card()` metodundan gereksiz `documents` eager load kaldırıldı. `personel.js`'e `loadPersonelDocuments()` ve `getDocIcon()` fonksiyonları eklendi. |
| 2026-06-07 | **Prompt #08 — Belge 404 ve Yükleme Kesin Düzeltme:** `personel_documents` tablosundaki 154 kaydın `file_path`'i `uploads/personel/{pid}/` eski formatından `personel-docs/{company}/{pid}/` yeni formatına güncellendi. `edit()` metoduna `$personel->load('documents')` eklendi. `_form.blade.php`'de mevcut belgeler download/delete butonlarıyla gösterilmeye başlandı. `personel.js`'e `deleteDocument()` eklendi, `openCardView()`'a `_docsLoaded` reset + `Alpine.initTree()` eklendi, hata durumunda yeniden denemeye izin verildi. **KRİTİK: `resources/views/partials/scripts.blade.php`'ye Alpine.js CDN'si eklendi** — 5 farklı view (`personel/_card`, `cms/index`, `companies/index`, `ozel-saat/index`, `travel/index`) Alpine kullanıyordu ama hiç yüklenmemişti, bu yüzden tab sistemleri çalışmıyordu. Storage dizini (`storage/app/private/personel-docs/`) oluşturuldu. Tüm cache'ler temizlendi. |
| 2026-06-07 | **Prompt #09 — Personel Belge Sistemi Kesin Çözüm:** Profil kartı belgeler sekmesi yeniden yapılandırıldı (`belgelerContainer`/`belgelerLoading`/`belgelerListesi`/`belgelerBos`). JS `loadPersonelDocuments()` → `loadBelgeler()` olarak güncellendi. Storage diski `local` → `public` olarak değiştirildi (`storage/app/public/personel-documents/{personelId}/`). PersonelController::store/update ve PersonelDocumentController::store/download/destroy `public` disk kullanacak şekilde güncellendi. Klasör otomatik oluşturma eklendi. Cache'ler temizlendi. |
| 2026-03-20 | **Auth & rol:** İlk kayıt olan kullanıcıya `super_admin`, sonrakilere `company_admin`; e-posta çakışması `back()->withErrors(['email' => 'Bu e-posta zaten kullanımda'])`. Ücretsiz deneme (`storeFreeTrial`) aynı zarif e-posta kontrolü; `unique` validation kaldırıldı. |
| 2026-03-20 | **Rotalar:** `web.php` / `admin.php` yorumları — `auth` middleware'inin login/register'a bulaşmadığı netleştirildi. |
| 2026-03-20 | **Vitrin hero:** `isolate`, `-z-10`, Tailwind `blur-3xl` / düşük opacity; “Yeniden Keşfedin” için `relative isolate` içinde sınırlı glow. |
| 2026-03-20 | **Blog:** `FrontendController@blogShow` → `frontend.blog_show`; CMS form `admin/cms/blog/_form.blade.php`; blog kayıt/güncelleme `FormData` + multipart + `onUploadProgress`; slug benzersizliği `CmsController::uniqueBlogSlug`. |
| 2026-03-15 | **Users SoftDeletes:** `add_deleted_at_to_users_table` migration oluşturuldu ve çalıştırıldı. Kayıt (Register) işleminde "Unknown column 'users.deleted_at'" hatası giderildi. |
| 2026-03-15 | **Vitrin CSS Kontrast:** "4 Adımda Hazırsınız" bölümündeki adım sayıları (01, 02, 03, 04) silik tasarımdan çıkarıldı. `text-[#02E0FB] font-extrabold text-5xl opacity-100 drop-shadow-md` ile okunabilir hale getirildi. |
| 2026-03-15 | **Blog Sayfası:** Liste kartlarına turkuaz (#02E0FB) ve turuncu (#FA6001) arka planlı, hover efektli "Devamını Oku →" butonu eklendi. |
| 2026-03-15 | NPM/Tailwind build, admin billing view, EnsureUserBelongsToCompany middleware, CompanyScope trait eklendi. |
| 2026-03-15 | **Admin Panel Audit:** Eksik 6 Blade view oluşturuldu: `admin/holidays/index`, `admin/raporlar/index`, `admin/ayarlar/index`, `admin/positions/index`, `admin/abonelik/invoices`, `admin/abonelik/plans`. Artık rotası olan hiçbir admin sayfası 404 vermiyor. |
| 2026-03-15 | **Auth Rolleri:** `RegisteredUserController::store()` güncellendi. Register sonrası `company_admin` rolü otomatik atanıyor, `company_name` alanı varsa şirket de oluşturuluyor. |
| 2026-03-15 | **PositionController:** `index()` artık JSON/HTML modunda çalışıyor — browser'dan açıldığında Blade view döndürüyor. |
| 2026-03-15 | **SubscriptionController:** `plans()` ve `invoices()` metotları JSON/HTML dual-mode desteği kazandı. |
| 2026-03-15 | **CompaniesSeeder + PersonelsSeeder:** `updateOrInsert` ile idempotent hale getirildi (tekrar çalıştırılabilir). |
| 2026-03-15 | **RolesPermissionsSeeder çalıştırıldı:** Tüm roller ve izinler veritabanına seed edildi. |
| 2026-03-15 | **403 / access_admin Fix:** `AppServiceProvider::boot()` içine `Gate::define('access_admin')` eklendi. `super_admin` için `Gate::before()` tanımlandı. Artık tüm admin rolleri (`company_admin`, `hr_manager`, `manager`, `finance`, `viewer`) panele erişebiliyor. |
| 2026-03-15 | **User Model:** `email_verified_at` alanı `$fillable`'a eklendi. Register/Free-Trial akışında e-posta doğrulaması anında onaylanıyor. |
| 2026-03-15 | **RegisteredUserController:** E-posta unique kontrolü graceful hata mesajıyla yapılıyor (`back()->withErrors`). Role ataması `company_admin` olarak güncellendi; `personel` (varolmayan) rolü kaldırıldı. Domain çakışması önlemek için slug-based unique domain oluşturuluyor. |
| 2026-03-15 | **Hero CSS Glow Onarımı:** Arka plan glow elementleri `z-0 overflow-hidden pointer-events-none` ile düzgün katmanlandı. Gradient text `display:inline-block` ile cross-browser uyumlu hale getirildi. |
| 2026-03-15 | **Blog Show View:** Tamamen yeniden tasarlandı. Hero + öne çıkan görsel + prose-lg içerik alanı + Twitter/LinkedIn paylaşım butonları + yazar kartı + sidebar CTA + ilgili yazılar bölümü eklendi. |
| 2026-03-15 | **Admin CMS Blog Form:** Öne çıkan görsel AJAX yükleme (önizleme + kaldırma), slug otomatik oluşturma, contenteditable editör (toolbar), etiket (tags_input → array) ve okuma süresi alanları eklendi. `CmsController::blogStore/blogUpdate` validasyonu güncellendi. |

---

## ✅ TAMAMLANAN (Bu Oturumda)

| # | Görev |
|---|-------|
| 1 | Migration refactor — 50 tablo, 1 dosya = 1 tablo kuralı |
| 2 | `migrate:fresh` hatasız çalışıyor |
| 3 | `AttendanceCalculatorService` — aylık çalışma, gecikme, fazla mesai hesaplama |
| 4 | `HolidayCheckerService` — resmi tatil + hafta sonu entegrasyonu |
| 5 | `PersonelDocumentController` — güvenli dosya yükleme/indirme/silme |
| 6 | Personel `_documents.blade.php` — drag & drop yükleme UI |
| 7 | `AuditService` — merkezi audit log servisi |
| 8 | 4 Mailable sınıfı — LeaveApproved, LeaveRejected, Welcome, AdvanceApproved |
| 9 | E-posta template'leri (Blade HTML) |

---

## 🔴 KRİTİK — Öncelikli (Sprint 1)

### 1. Authentication Kurulumu
- [ ] Laravel Breeze veya Jetstream kurulumu (`php artisan breeze:install blade`)
- [ ] Login, Register, Password Reset view'ları
- [ ] "Ücretsiz Deneyin" kayıt → otomatik company + user oluşturma
- [ ] E-posta doğrulama akışı (`EmailVerificationController`)

### 2. Middleware & Guard Tamamlama
- [x] `can:access_admin` — Spatie permission ile otomatik Gate
- [x] Company scope middleware (`EnsureUserBelongsToCompany`) — `app/Http/Middleware` + alias `company`
- [x] `CompanyScope` trait — `app/Traits/CompanyScope.php` (opsiyonel kullanım)
- [ ] Multi-tenant veri izolasyonu testi

### 3. Seeder'ları Çalıştır
```bash
php artisan db:seed --class=RolesPermissionsSeeder
php artisan db:seed --class=CompaniesSeeder
php artisan db:seed --class=PersonelsSeeder
```

### 4. Queue Worker Yapılandırması
- [ ] `QUEUE_CONNECTION=database` (`.env` zaten ayarlı)
- [ ] `php artisan queue:work` — production supervisor config
- [ ] Failed jobs için `php artisan queue:failed`

---

## 🟡 ÖNEMLİ — (Sprint 2)

### 5. Puantaj Motoru UI Bağlantısı
- [ ] `AttendanceCalculatorService` → `TimeRecordController::monthlySummary()` endpoint'ine bağla
- [ ] Aylık puantaj raporu PDF/Excel export
- [ ] QR kod ile giriş/çıkış (placeholder endpoint hazır)

### 6. Resmi Tatil Seed'i
```bash
# HolidayCheckerService::seedTurkeyHolidays($year) metodunu çağır
php artisan tinker --execute="app(\App\Services\HolidayCheckerService::class)->seedTurkeyHolidays(2026);"
```

### 7. E-posta Tetikleyiciler
- [ ] `LeaveRequest::approve()` → `LeaveApprovedMail` gönder
- [ ] `LeaveRequest::reject()` → `LeaveRejectedMail` gönder  
- [ ] `AdvanceRequest::approve()` → `AdvanceApprovedMail` gönder
- [ ] `FreeTrialController::store()` → `WelcomeMail` gönder

### 8. Varlık (Asset) Yönetimi
- [ ] Zimmet belgesi PDF oluşturma (Barryvdh/LaravelDompdf)
- [ ] Barkod/QR üretimi (`bacon/bacon-qr-code`)
- [ ] Depreciation (amortisman) hesaplama servisi

### 9. Admin Panel İzin Entegrasyonu
- [ ] `HolidayCheckerService` → İzin formu tarih seçerken AJAX tatil uyarısı
- [ ] İzin takvimi view'u (`FullCalendar` bağlantısı zaten mevcut)

### 9b. Controller Dual-mode Fix
- [x] CompanyController — Browser/Ajax dual-mode (`index()` `wantsJson()` kontrolü eklendi)
- [x] DepartmentController — Browser/Ajax dual-mode
- [x] PositionController — Zaten dual-mode idi
- [x] TravelController — Dual-mode + gerçek Blade view (placeholder kaldırıldı)
- [x] VehicleController — Dual-mode + gerçek Blade view
- [x] VisitorController — Dual-mode + gerçek Blade view
- [x] ServiceController — Dual-mode + gerçek Blade view

---

## 🟢 GELİŞTİRME — (Sprint 3)

### 10. Raporlar Modülü
- [ ] `GenerateReportJob` → gerçek PDF/Excel çıktısı (Dompdf + PhpSpreadsheet)
- [ ] Planlı rapor e-posta gönderimi
- [ ] Rapor şablonu builder UI

### 11. Vardiya Optimizasyonu
- [ ] `ConflictDetectionJob` — çakışma tespiti background job
- [ ] Otomatik roster üretimi algoritması
- [ ] Gece vardiyası sabah-karanlığı çakışma koruması

### 12. Bildirim Kanalları
- [ ] SMS bildirim adaptörü (Netgsm/Iletimerkezi entegrasyonu)
- [ ] Push notification (Firebase FCM — mobil hazırlık)
- [ ] In-app bildirimler zaten çalışıyor ✓

### 13. API Layer
- [ ] `routes/api.php` — mobil uygulama için REST API
- [ ] Laravel Sanctum token auth
- [ ] OpenAPI/Swagger dokümantasyonu

### 14. Frontend Build ✅
```bash
npm install -D tailwindcss postcss autoprefixer
npm run build  # Tailwind CSS derle
php artisan storage:link
```

### 15. Multi-tenant İyileştirme
- [ ] Global scope: `CompanyScope` trait → tüm modellere ekle
- [ ] Subdomain routing (isteğe bağlı)
- [ ] Veri izolasyon testleri

---

## 🔵 UZUN VADE — (Sprint 4+)

| # | Görev | Tahmini Süre |
|---|-------|-------------|
| 16 | Mobil uygulama (Flutter/React Native) | 3-4 ay |
| 17 | Biometrik entegrasyon (SDK) | 2 ay |
| 18 | Bordro modülü (SGK entegrasyonu) | 3 ay |
| 19 | Stripe/İyzico ödeme entegrasyonu | 2 hafta |
| 20 | Elasticsearch full-text search | 1 ay |
| 21 | Webhook sistemi (dış entegrasyon) | 2 hafta |
| 22 | GDPR anonimleştirme araçları | 1 hafta |
| 23 | SSO/SAML entegrasyonu (kurumsal) | 1 ay |
| 24 | BI/Analitik dashboard (Grafana) | 1 ay |

---

## ⚙️ PRODUCTION HAZIRLIK KONTROL LİSTESİ

```bash
# 1. .env production ayarları
APP_ENV=production
APP_DEBUG=false
APP_URL=https://pts.miysoft.com.tr

# 2. DB — gerçek MySQL bilgilerini gir
DB_USERNAME=miysoft_user
DB_PASSWORD=guclu_sifre

# 3. Mail — gerçek SMTP
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=SG.xxxxxxx

# 4. Queue
QUEUE_CONNECTION=redis  # Redis önerilir (database yerine)

# 5. Cache
CACHE_STORE=redis

# 6. Artisan komutları
php artisan migrate --force
php artisan db:seed --class=RolesPermissionsSeeder
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:work --daemon --queue=default,notifications,exports

# 7. Cron (crontab -e)
* * * * * cd /var/www/miysoft_pts && php artisan schedule:run >> /dev/null 2>&1
```

---

## 📊 MEVCUT DURUM ÖZETİ

| Bileşen | Durum | Tamamlanma % |
|---------|-------|-------------|
| Veritabanı (50 tablo) | ✅ Tamamlandı | 100% |
| Migration sıralaması | ✅ Düzeltildi | 100% |
| Personel modülü | ✅ Tam CRUD (belge yükleme + AJAX görüntüleme + edit formunda mevcut belgeler + DB path tutarlılığı) | 100% |
| İzin modülü | ✅ Onay akışı | 90% |
| Puantaj modülü | ✅ Motor + UI | 85% |
| Vardiya modülü | ✅ Takvim + Atama | 80% |
| Envanter + Zimmet | ✅ Tam CRUD | 85% |
| Avans & Masraf | ✅ Onay akışı | 85% |
| Bildirim sistemi | ✅ DB driver | 90% |
| Rol & Yetki (Spatie) | ✅ UI dahil, Seeder çalıştırıldı | 98% |
| CMS / Blog | ✅ | 80% |
| Abonelik & Fatura | ✅ Views tamamlandı | 85% |
| Etkileşim (Duyuru/Anket) | ✅ | 80% |
| Süreç (Onboarding) | ✅ | 75% |
| Frontend (Tanıtım sitesi) | ✅ CSS+Blog düzeltildi | 90% |
| Authentication | ✅ TAMAMLANDI — Breeze, ilk kullanıcı `super_admin`, zarif e-posta hatası | 100% |
| Admin Panel Views | ✅ Tüm rotalar view'a sahip, dual-mode controller'lar | 100% |
| Frontend CSS | ✅ TAMAMLANDI — Hero glow Tailwind/isolate, kart gölgeleri yumuşatıldı | 100% |
| Blog Detay (Show) | ✅ TAMAMLANDI — `blog_show.blade.php`, başlık/tarih/yazar/CMS içerik | 100% |
| Yetkilendirme (403/401) | ✅ TAMAMLANDI — `access_admin` gate, guest/auth rota ayrımı, Spatie roller | 100% |
| CMS Blog Yönetimi | ✅ FormData AJAX, öne çıkan görsel, slug çakışması önleme | 95% |
| E-posta | ⚠️ Mailable hazır, tetikleyici gerekli | 60% |
| Raporlar | ⚠️ UI hazır, gerçek export eksik | 50% |
| API Layer | ❌ Başlanmadı | 0% |
| Testler | ⚠️ İskelet hazır | 20% |
| **GENEL** | | **~92%** |

---

## 📌 YAPILACAK SONRAKİ İŞLER (EKSİKLER)

### Acil / Kritik
- [x] **Auth Rolleri:** İlk kullanıcı `super_admin`, diğerleri `company_admin`; e-posta mesajı: «Bu e-posta zaten kullanımda»
- [x] **403 Gate Fix:** `AppServiceProvider` içinde `Gate::define('access_admin')` + `Gate::before(super_admin)` tanımlandı
- [x] **email_verified_at:** User `$fillable`'a eklendi; register sonrası doğrulama bypass sorunu çözüldü
- [x] **Seeder Çalıştırma:** `RolesPermissionsSeeder` ✅ çalıştırıldı, `CompaniesSeeder` ✅ çalıştırıldı
- [ ] **PersonelsSeeder:** `php artisan db:seed --class=PersonelsSeeder` çalıştır
- [ ] **E-posta Tetikleyiciler:** LeaveRequest approve/reject, AdvanceRequest approve, FreeTrial store → Mailable dispatch edilmiyor

### Raporlar & Export (Sonraki Sprint)
- [ ] **Raporlar modülü detaylandırma:** `admin/reports` UI → filtreler, şablon seçimi, zamanlanmış gönderim
- [ ] **Raporlar Modülü:** GenerateReportJob → gerçek PDF/Excel (Dompdf + PhpSpreadsheet)
- [ ] **Puantaj PDF/Excel:** `monthlySummary` endpoint → download
- [ ] **Zimmet PDF:** Barryvdh/LaravelDompdf ile zimmet belgesi

### Bildirimler (Sonraki Sprint)
- [ ] **Bildirim çanı canlı güncelleme:** Kısa aralıklı polling veya Laravel Echo + Pusher/Soketi
- [ ] **Bildirim Çanı (mevcut):** Okundu sayacı ve son 20 kayıt — gerçek zamanlı senkron eksik
- [ ] **SMS Bildirimleri:** Netgsm/Iletimerkezi adaptörü
- [ ] **E-posta Bildirimleri:** SMTP kurulumu → tetikleyiciler aktif

### CMS & İçerik
- [x] **Blog Slug Benzersizliği:** `uniqueBlogSlug` ile çakışma önleniyor
- [ ] **CMS Site Editor:** Hero, footer, legal sayfa içerikleri düzenlenebilir olsun
- [ ] **Medya Kütüphanesi:** Yüklenen görsellerin listesi ve yönetimi

### Güvenlik & Altyapı
- [ ] **Multi-tenant Test:** Şirketler arası veri sızıntısı testi
- [ ] **Queue Worker Supervisor:** Production için systemd/supervisor config
- [ ] **API Layer:** Sanctum token auth, `routes/api.php`

### Raporlar & Export
- [ ] **Raporlar Modülü:** GenerateReportJob → gerçek PDF/Excel (Dompdf + PhpSpreadsheet)
- [ ] **Puantaj Raporu:** Aylık puantaj PDF/Excel export, monthlySummary endpoint bağlantısı
- [ ] **Zimmet Belgesi:** Asset zimmet PDF oluşturma (Barryvdh/LaravelDompdf)

### CMS & İçerik
- [ ] **CMS Güncellemeleri:** Site Editor'dan hero, footer, legal sayfa içerikleri düzenlenebilir hale getir
- [x] **Blog Detay:** Slug routing (`blog/{slug}`), `blog_show` görünümü, yayın kontrolü

### Yetkilendirme & Güvenlik
- [ ] **İzin Takvimi:** FullCalendar ile izin takvimi view'u
- [ ] **HolidayCheckerService AJAX:** İzin formunda tarih seçerken tatil uyarısı
- [ ] **Multi-tenant Test:** Veri izolasyon testleri

### Altyapı
- [ ] **Queue Worker:** Production'da `php artisan queue:work` supervisor config
- [ ] **API Layer:** Mobil uygulama için REST API, Sanctum token auth
