# MİYSOFT PTS — Action Plan

**Proje:** MİYSOFT PTS
**Tarih:** 2026-03-15
**Son Güncelleme:** 2026-05-14

---

## Adım 11: Kritik Altyapı Onarımı (2026-05-14)
**Amaç:** `authorize()` 500 hatası, sidebar layout bozukluğu ve çalışmayan modülleri düzeltmek.
**Yapılanlar:**

### Bug Fix 1 — Controller `authorize()` Hatası (KRİTİK)
- **Sorun:** Laravel 12'de `AuthorizesRequests` trait base `Controller`'dan çıkarıldı. Tüm modül controller'larında `$this->authorize()` çağrısı `Call to undefined method` 500 hatasına yol açıyordu.
- **Çözüm:** `app/Http/Controllers/Controller.php` içine `use Illuminate\Foundation\Auth\Access\AuthorizesRequests;` trait eklendi.

### Bug Fix 2 — Admin Panel Layout & Sidebar Yeniden Tasarımı
- **Sorun:** Sidebar `fixed top-16 lg:relative` kombinasyonu desktop'ta 64px dikey kayma yaratıyordu. Duplicate `id` attribute (`id="sidebar"` ve `id="sidebarEl"` aynı element). Mobile sidebar toggle JS hiç yoktu. `isActive()` fonksiyonu `@php` bloğunda her render'da redeclare hatası riski.
- **Çözüm:**
  - `layouts/app.blade.php` → `h-screen overflow-hidden flex` yapısına geçildi; footer geri eklendi.
  - `partials/sidebar.blade.php` → Tam yeniden tasarım: `fixed inset-y-0 lg:static` pattern, duplicate id kaldırıldı, `isActive` closure'a çevrildi, professional dark theme (`#0F172A`), aktif item `bg-[#02E0FB]/15` tonu, bottom user bar.
  - `partials/_sidebar_item.blade.php` → `$active` parametresi (dışarıdan önceden hesaplanmış) ile güncellendi.
  - `partials/header.blade.php` → Alpine.js bağımlılığı kaldırıldı, saf JS ile dropdown; hamburger butonu düzeltildi.
  - `partials/scripts.blade.php` → `toggleSidebar()`, `closeSidebar()`, `toggleUserMenu()` fonksiyonları eklendi; mobile overlay backdrop desteği.

### Bug Fix 3 — `admin.leave.index` Rota Düzeltmesi
- **Sorun:** `GET /admin/leave` → `LeaveRequestController@index` (JSON) bağlıydı; tarayıcıdan açıldığında ham JSON dönüyordu.
- **Çözüm:** `routes/admin.php` → `indexView` metoduna yönlendirildi; eski `index` (JSON) `/list` path'ine taşındı.

**Dosyalar Değiştirilen:**
- `app/Http/Controllers/Controller.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/partials/sidebar.blade.php`
- `resources/views/partials/_sidebar_item.blade.php`
- `resources/views/partials/header.blade.php`
- `resources/views/partials/scripts.blade.php`
- `routes/admin.php`

---

---

## Adım 1: Proje Kurulumu ve Temel Yapı
**Amaç:** Laravel uygulamasını kurmak, gerekli paketleri yüklemek, veritabanı bağlantısını yapılandırmak.
**Girdi:** Boş klasör, PHP 8.2+, Composer
**Çıktı:** Çalışan Laravel uygulaması, .env yapılandırılmış, spatie/laravel-permission kurulu
**Dosyalar:**
- `composer.json`, `.env`, `config/permission.php`
- `database/migrations/...create_permission_tables.php`

---

## Adım 2: Veritabanı Mimarisi ve Migrations
**Amaç:** Tüm modüller için veritabanı tablolarını tasarlamak ve migration dosyalarını oluşturmak.
**Girdi:** database_schema.json (modül → tablo → sütun)
**Çıktı:** 25+ migration dosyası, her biri Schema::create ile
**Dosyalar:**
- `database/migrations/2026_03_15_*_create_companies_table.php`
- `database/migrations/2026_03_15_*_create_personels_table.php`
- `database/migrations/2026_03_15_*_create_departments_table.php`
- `database/migrations/2026_03_15_*_create_leave_requests_table.php`
- `database/migrations/2026_03_15_*_create_time_records_table.php`
- `database/migrations/2026_03_15_*_create_shifts_table.php`
- `database/migrations/2026_03_15_*_create_assets_table.php`
- `database/migrations/2026_03_15_*_create_audit_logs_table.php`
- (ve daha 15+ migration)

---

## Adım 3: Modüler Yapı — app/Modules
**Amaç:** Her modül için Controller, Model, Request, Policy ve Service sınıflarını oluşturmak.
**Girdi:** Module spec (her modülün alan listesi, ilişkiler)
**Çıktı:** Her modül klasöründe eksiksiz PHP sınıfları
**Dosyalar:**
- `app/Modules/Personel/Controllers/PersonelController.php`
- `app/Modules/Personel/Models/Personel.php`
- `app/Modules/Personel/Requests/StorePersonelRequest.php`
- `app/Modules/Personel/Requests/UpdatePersonelRequest.php`
- `app/Modules/Personel/Policies/PersonelPolicy.php`
- (benzer yapı tüm modüller için)

---

## Adım 4: RBAC Yapılandırması
**Amaç:** Rol ve izin sistemini tanımlamak, seeder ile doldurmak.
**Girdi:** rbac_mapping.csv
**Çıktı:** Roller ve izinler veritabanında, her kullanıcıya atanabilir
**Dosyalar:**
- `database/seeders/RolesPermissionsSeeder.php`
- `docs/rbac_mapping.csv`

---

## Adım 5: Route Yapılandırması
**Amaç:** Admin ve public rotaları tanımlamak.
**Girdi:** Modül listesi, route prefix kuralları
**Çıktı:** Tüm admin rotaları `/admin` prefix ile, middleware ile korumalı
**Dosyalar:**
- `routes/web.php` (public rotalar)
- `routes/admin.php` (admin panel rotaları)

---

## Adım 6: Blade Layout ve View Dosyaları
**Amaç:** Ana layout, partial ve modül view'larını oluşturmak.
**Girdi:** Layout spec (head, header, sidebar, footer, messages, scripts)
**Çıktı:** Tüm admin ekranları için blade dosyaları
**Dosyalar:**
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/guest.blade.php`
- `resources/views/partials/head.blade.php`
- `resources/views/partials/header.blade.php`
- `resources/views/partials/sidebar.blade.php`
- `resources/views/partials/footer.blade.php`
- `resources/views/partials/messages.blade.php`
- `resources/views/partials/scripts.blade.php`
- `resources/views/admin/personel/index.blade.php`
- `resources/views/admin/personel/_form.blade.php`
- `resources/views/admin/personel/_card.blade.php`

---

## Adım 7: Seeder Dosyaları
**Amaç:** Geliştirme ve test için örnek veri oluşturmak.
**Girdi:** Her modül için sayılar (5 şirket, 10 departman, 50 personel...)
**Çıktı:** Çalışan seeder dosyaları
**Dosyalar:**
- `database/seeders/CompaniesSeeder.php`
- `database/seeders/RolesPermissionsSeeder.php`
- `database/seeders/PersonelsSeeder.php`

---

## Adım 8: Background Jobs
**Amaç:** Export, rapor ve thumbnail işlemleri için job sınıfları oluşturmak.
**Girdi:** Queue gereksinim listesi
**Çıktı:** Queue-ready Job sınıfları
**Dosyalar:**
- `app/Jobs/ExportPersonelExcelJob.php`
- `app/Jobs/GenerateReportJob.php`
- `app/Jobs/ProcessThumbnailJob.php`
- `app/Jobs/CalculateDailyMetricsJob.php`
- `app/Jobs/RecalculateLeaveBalancesJob.php`

---

## Adım 9: Feature Testleri
**Amaç:** Kritik iş akışları için PHPUnit testleri oluşturmak.
**Girdi:** Test case listesi (modül bazında)
**Çıktı:** Çalışan feature test dosyaları
**Dosyalar:**
- `tests/Feature/PersonelCrudTest.php`
- `tests/Feature/LeaveApprovalFlowTest.php`
- `tests/Feature/TimeRecordPairingTest.php`
- `tests/Feature/RolePermissionMatrixTest.php`

---

## Adım 10: Dokümantasyon ve Follow-up Promptlar
**Amaç:** Projenin tüm detaylarını belgelemek ve sonraki adımlar için prompt şablonları oluşturmak.
**Girdi:** Tüm geliştirme çıktıları
**Çıktı:** Eksiksiz docs/ klasörü
**Dosyalar:**
- `docs/executive_summary.md`
- `docs/action_plan.md`
- `docs/database_schema.json`
- `docs/erd.mermaid`
- `docs/rbac_mapping.csv`
- `docs/follow_up_prompts.md`

---

## Tahmini Sıralama ve Bağımlılıklar

```
Adım 1 (Kurulum)
  └─> Adım 2 (Migrations)
        └─> Adım 3 (Models/Controllers)
              ├─> Adım 4 (RBAC)
              ├─> Adım 5 (Routes)
              ├─> Adım 6 (Views)
              ├─> Adım 7 (Seeders)
              ├─> Adım 8 (Jobs)
              └─> Adım 9 (Tests)
                    └─> Adım 10 (Docs)
```

## Adım 12: Kritik Hata Onarımı — Layout v2 + Null Safety (2026-05-14)

### Backend Fix — scopeForCompany Null TypeError (TÜM MODÜLLER)
- **Sorun:** company_id null olabilen (super_admin) kullanıcılar için 20 modelde int type hint TypeError veriyordu.
- **Cozum:** ?int companyId yapildi; null geldiginde super_admin icin kisitlama yapilmaz.
- **Etkilenen:** 20 model (LeaveType, LeaveRequest, Personel, TimeRecord, AdvanceRequest, ExpenseRequest, ExpenseCategory, Asset, AssetType, Announcement, Poll, Department, Position, Team, Shift, ShiftAssignment, ShiftPlan, ProcessTemplate, ProcessInstance, CompanySubscription, Invoice)

### Frontend Fix — Admin Layout v2 (CSS-first)
- **Sorun:** Tailwind arbitrary class'lar Windows build ortaminda CSS'e girmiyor, layout bozuluyordu.
- **Cozum:** resources/css/app.css'e 300 satirlik admin layout CSS yazildi. CSS custom properties: --pts-brand, --pts-sidebar-bg vb.
- **Yapi:** #admin-wrapper (flex) => #admin-sidebar + #admin-main => #admin-header + #admin-content
- **CSS build:** app-TovAPEZK.css 87.49 kB

