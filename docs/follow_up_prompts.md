# MİYSOFT PTS — Follow-up Prompts

Bu dosya, projenin sonraki geliştirme aşamaları için hazır prompt şablonları içerir.
Her promptu doğrudan Cursor IDE veya LLM'e kopyalayıp yapıştırabilirsiniz.

---

## 1. Migration & Tablo Detaylandırma

```
ROLE: Senior Laravel migration uzmanısın.
Görev: MİYSOFT PTS projesindeki tüm migration dosyalarını incele (database/migrations/), 
eksik FK constraint, index ve pivot tablolarını ekle. 
Ayrıca şu tabloları oluştur (henüz migration dosyası yok):
- advance_requests, expense_categories, expense_requests
- travel_requests, itineraries
- vehicles, vehicle_requests, vehicle_logs
- services, service_requests
- polls, poll_options, poll_responses
- announcements
- gifts, gift_assignments
- subscription_plans, company_subscriptions, invoices, payments
- notification_templates, user_notifications
- settings, feature_flags
- support_tickets
- releases

Her migration için: company_id FK, softDeletes, indexler, enum tanımları.
Tüm migration'ları çalıştır: php artisan migrate --path=...
```

---

## 2. Model & Eloquent İlişkileri

```
ROLE: Laravel Eloquent uzmanısın.
Görev: MİYSOFT PTS projesindeki tüm Modelleri oluştur veya güncelle.
app/Modules/*/Models/ ve app/Models/ altında:

Her Model için:
- $fillable listesi (tüm sütunlar)
- $casts (dates, boolean, decimal, array/json)
- $hidden (PII: national_id_enc, document_no_enc)
- Relations: belongsTo, hasMany, belongsToMany (pivot tablolar dahil)
- Scopes: scopeForCompany, scopeActive, scopeByStatus
- Mutators: PII alanları için set*/get* (Crypt::encrypt/decrypt)
- accessors: full_name, masked_national_id gibi

Minimum model listesi: Company, Department, Position, Team, Personel, PersonelDocument, 
Certification, LeaveType, LeaveBalance, LeaveRequest, TimeRecord, OvertimeRequest, 
Shift, ShiftPlan, ShiftAssignment, Holiday, AssetType, Asset, AssetAssignment, 
Visitor, AuditLog, Media, Export.
```

---

## 3. Blade View Scaffold (Tüm Modüller)

```
ROLE: Senior Blade + Tailwind CSS uzmanısın.
Görev: MİYSOFT PTS admin paneli için tüm modüllerin Blade view dosyalarını oluştur.

Renk paleti: primary #02E0FB, secondary #FA6001, bg #FEFEFE
Layout: resources/views/layouts/app.blade.php (zaten mevcut)

Her modül için oluşturulacak dosyalar:
- resources/views/admin/{modul}/index.blade.php (DataTable + filtreler + butonlar)
- resources/views/admin/{modul}/_form.blade.php (Create/Edit modal formu)
- resources/views/admin/{modul}/_card.blade.php (Detay kart görünümü)

Modüller: dashboard, izin (leave), puantaj (attendance), vardiya (shift), 
sirket (company/department/position), envanter (asset), avans, masraf, 
seyahat, arac, hizmet, ziyaretci, raporlar, ayarlar, roller.

Her view için: Ajax DataTable entegrasyonu, modal CRUD, Export butonları, 
server-side pagination, responsive ve mobile-first.
```

---

## 4. Controller & Service Katmanı

```
ROLE: Laravel Controller ve Service Layer uzmanısın.
Görev: MİYSOFT PTS için tüm modüllerin Controller sınıflarını oluştur.

Her Controller için:
- Constructor: authorize/middleware
- index(): Ajax JSON için paginated sonuç (DataTable uyumlu)
- create(): Modal HTML döner
- store(): FormRequest validation + kaydet + JSON yanıt
- edit($id): Modal HTML döner (mevcut veri dolu)
- update($id): FormRequest validation + güncelle + JSON yanıt
- destroy($id): Soft delete + JSON yanıt
- Modüle özel metodlar (approve, reject, card, export vs.)
- Policy authorization her methodda

Controller listesi (app/Modules/*/Controllers/):
DashboardController, LeaveTypeController, LeaveRequestController,
TimeController, ShiftController, CompanyController, DepartmentController,
PositionController, AssetController, AssetTypeController, VisitorController,
AdvanceController, ExpenseController, TravelController, VehicleController,
ReportController, SettingsController, RoleController, PermissionController.
```

---

## 5. Seeder & Fake Data

```
ROLE: Laravel Database Seeder uzmanısın.
Görev: MİYSOFT PTS için tüm modüllere ait kapsamlı seeder dosyaları oluştur.

Mevcut seeders:
- CompaniesSeeder (5 şirket) ✓
- RolesPermissionsSeeder ✓  
- PersonelsSeeder (50 personel) ✓

Oluşturulacak yeni seeders:
- LeaveTypesSeeder (10 izin türü: yıllık, mazeret, ücretsiz vs.)
- LeaveRequestsSeeder (200 izin talebi, çeşitli statüler)
- TimeRecordsSeeder (son 30 gün için giriş/çıkış kayıtları)
- ShiftsSeeder (sabah, öğleden sonra, gece vardiyaları)
- ShiftAssignmentsSeeder (aylık roster)
- HolidaysSeeder (2025-2026 Türkiye resmi tatilleri)
- AssetTypesSeeder + AssetsSeeder (100 varlık, zimmet örnekleri)
- VisitorsSeeder (50 ziyaretçi kaydı)
- NotificationTemplatesSeeder (tüm modüller için şablonlar)
- SettingsSeeder (varsayılan sistem ayarları)

Her seeder: chunked insert, referential integrity, Faker kullanımı.
```

---

## 6. Background Jobs & Queue Yapılandırması

```
ROLE: Laravel Queue ve Job uzmanısın.
Görev: MİYSOFT PTS için tüm background job'ları tamamla ve queue yapılandırmasını ayarla.

Mevcut jobs:
- ExportPersonelExcelJob ✓
- ExportPersonelPdfJob
- GenerateReportJob
- ProcessThumbnailJob
- CalculateDailyMetricsJob
- RecalculateLeaveBalancesJob ✓

Her Job için:
- tries, timeout, backoff politikaları
- handle() metodunda chunked işlemler
- Hata durumunda failed() handler
- Export için Storage::put() ve signed URL

Yapılandırma:
- config/queue.php: Redis bağlantısı
- Ayrı queue kanalları: exports, reports, notifications, thumbnails
- Horizon kurulumu (supervisor config)
- php artisan queue:work --queue=exports,reports,notifications,thumbnails

Cron jobs (app/Console/Kernel.php):
- RecalculateLeaveBalancesJob: monthly
- CalculateDailyMetricsJob: daily
- GenerateReportJob (scheduled reports): as configured
```

---

## 7. API & Ajax Endpoint Dokümantasyonu

```
ROLE: Laravel API dokümantasyon uzmanısın.
Görev: MİYSOFT PTS admin panel Ajax endpoint'lerini belgele.

Çıktı formatı: OpenAPI 3.0 YAML veya Postman Collection JSON

Her endpoint için:
- Method (GET/POST/PUT/PATCH/DELETE)
- URL pattern (/admin/{resource}/{id?}/{action?})
- Request payload (örnek JSON)
- Response format (success/error örnekleri)
- Auth: Bearer token veya session
- Permission gereksinimleri

Modüller: personel, leave, attendance, shift, asset, visitor, report
```

---

## 8. Public Site (Marketing Site) Blade Views

```
ROLE: Laravel Blade + Tailwind CSS frontend uzmanısın.
Görev: MİYSOFT PTS kişisel tanıtım sitesinin tüm public sayfalarını oluştur.

Renk paleti: primary #02E0FB, secondary #FA6001, bg #FEFEFE
Font: Inter

Sayfalar:
- resources/views/public/home.blade.php (Hero, Features, Pricing, Testimonials)
- resources/views/public/about.blade.php (Hakkımızda, Ekip, Misyon)
- resources/views/public/product.blade.php (Modül detayları, Screenshot'lar)
- resources/views/public/pricing.blade.php (Paketler, Karşılaştırma tablosu)
- resources/views/public/contact.blade.php (İletişim formu)
- resources/views/public/free-trial.blade.php (Kayıt formu)
- resources/views/public/blog/index.blade.php, show.blade.php
- resources/views/layouts/public.blade.php (Header, Footer, Nav)

Her sayfa: Responsive, SEO optimized, structured data (JSON-LD), 
Lazy loading görseller, CTA butonları.
```

---

## 9. GDPR & Güvenlik Uygulama

```
ROLE: Laravel güvenlik uzmanısın.
Görev: MİYSOFT PTS için GDPR uyumu ve güvenlik önlemlerini uygula.

1. PII Şifreleme:
   - Personel: national_id_enc → Crypt::encryptString()
   - Ziyaretçi: document_no_enc → Crypt::encryptString()  
   - Tüm PII mutator/accessor'larını tamamla

2. Audit Logging Middleware:
   - Her kritik aksiyon (CRUD, approve, login) için audit_logs'a kayıt
   - AuditableInterface ve LogsActivity trait oluştur
   - IP, user_agent, model_diff kaydet

3. Rate Limiting:
   - Login: 5 istek/dakika
   - API: 60 istek/dakika per user

4. GDPR Tools:
   - Kullanıcı verisi dışa aktarma (GDPR export)
   - Kullanıcı hesabı silme (anonymize) komutu
   - Consent logging (pazarlama onayları)

5. Content Security Policy header middleware
```

---

## 10. Test Suite Genişletme

```
ROLE: Laravel PHPUnit test uzmanısın.
Görev: MİYSOFT PTS için kapsamlı test suite oluştur.

Mevcut testler: PersonelCrudTest.php ✓

Oluşturulacak testler:
- LeaveApprovalFlowTest: izin talebi → onay akışı testi
- TimeRecordPairingTest: giriş/çıkış eşleştirme testi
- RolePermissionMatrixTest: RBAC matrix testi
- AssetAssignmentLifecycleTest: zimmet yaşam döngüsü
- ShiftConflictDetectionTest: vardiya çakışma testi
- LeaveBalanceCalculationTest: bakiye hesaplama testi
- AuditLogImmutabilityTest: audit log değiştirilmezlik testi
- ExportJobTest: export job tamamlanma testi

Test stratejisi:
- RefreshDatabase trait
- Factory ile test verisi
- actingAs(user) ile yetki testleri
- assertDatabaseHas/assertSoftDeleted
- Mock: Queue::fake(), Storage::fake()
```

---

## 11. Dashboard & KPI Widgets

```
ROLE: Laravel + Chart.js uzmanısın.
Görev: MİYSOFT PTS dashboard'unun KPI widget'larını oluştur.

Widgets:
1. Toplam aktif personel sayısı
2. Bugün işe gelen/gelmeyen personel
3. Bekleyen izin talepleri sayısı
4. Bekleyen avans/masraf talepleri
5. Bu ay izin kullanan personel (pasta grafik)
6. Son 7 günün puantaj trendi (çizgi grafik)
7. Departman bazlı personel dağılımı (bar grafik)
8. Abonelik durumu ve kalan gün

Teknik detaylar:
- DashboardController@widgetData (Ajax endpoint)
- Redis cache (TTL: 5 dakika)
- Chart.js entegrasyonu
- CalculateDailyMetricsJob ile günlük önhesaplama
- Rol bazlı widget görünürlüğü (super_admin tüm widgetları görür)
```

---

## 12. Site Editor (CMS) Modülü

```
ROLE: Laravel CMS uzmanısın.
Görev: MİYSOFT PTS admin panelindeki Site Editor modülünü tam olarak uygula.

Site Editor admin panelinden şunları yönetebilmeli:
- Hero section: başlık, alt başlık, CTA butonları, görsel
- Navigasyon menüsü: hiyerarşik (max 2 seviye), drag & drop sıralama
- Özellikler grid: 6'ya kadar özellik kartı
- Partners slider: logo, link, alt text
- Testimonials: ad, rol, alıntı, avatar
- Blog ayarları: category list, featured posts
- Footer: 4 sütun (links/text/form tipi)
- Contact info, social media links

Veritabanı: settings tablosu (key-value + JSON)
Blade: resources/views/admin/site-editor/index.blade.php
WYSIWYG: TinyMCE veya Quill editor entegrasyonu
Media Library: görseller için media tablosu kullan
```
