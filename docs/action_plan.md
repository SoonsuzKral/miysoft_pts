# MİYSOFT PTS — Action Plan

**Proje:** MİYSOFT PTS
**Tarih:** 2026-03-15

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
