# MİYSOFT PTS — MOBİL UYGULAMA MİMARİ DOKÜMANI

> **Proje:** MİYSOFT PTS Mobil Uygulama  
> **Platform:** Android + iOS (Flutter)  
> **Mimari:** BLoC Pattern + Repository Pattern  
> **API:** Laravel REST API (Sanctum Auth)  
> **Bildirim:** Firebase Cloud Messaging (FCM)  
> **Tema:** Backend admin panel ile aynı tasarım dili  
> **Geliştirme Ortamı:** macOS (Xcode + Android Studio)  

---

## 1. MİMARİ YAPI

```
miysoft_pts_mobile/
├── android/                          # Native Android (Kotlin)
│   ├── app/src/main/AndroidManifest.xml
│   └── app/src/main/kotlin/...
├── ios/                              # Native iOS (Swift)
│   ├── Runner/Info.plist
│   └── Runner/AppDelegate.swift
├── lib/
│   ├── main.dart                     # Uygulama giriş noktası
│   ├── app.dart                      # MaterialApp, tema, routing
│   ├── bootstrap.dart                # Başlangıç yapılandırması
│   │
│   ├── config/
│   │   ├── app_config.dart           # API URL, timeout, vs.
│   │   ├── theme/
│   │   │   ├── app_theme.dart        # Tema tanımları (backend ile aynı)
│   │   │   ├── app_colors.dart       # Renk paleti
│   │   │   └── app_text_styles.dart  # Yazı stilleri
│   │   └── routes/
│   │       ├── app_router.dart       # GoRouter yapılandırması
│   │       └── route_names.dart      # Route sabitleri
│   │
│   ├── core/
│   │   ├── constants/
│   │   │   ├── api_endpoints.dart    # Tüm API endpoint sabitleri
│   │   │   ├── app_constants.dart    # Uygulama sabitleri
│   │   │   └── enums.dart            # Genel enumlar
│   │   ├── errors/
│   │   │   ├── exceptions.dart       # Özel exception sınıfları
│   │   │   └── failures.dart         # Failure modelleri
│   │   ├── network/
│   │   │   ├── api_client.dart       # Dio HTTP client yapılandırması
│   │   │   ├── api_interceptors.dart # Token, hata, log interceptorları
│   │   │   └── network_info.dart     # İnternet bağlantı kontrolü
│   │   ├── security/
│   │   │   ├── fake_gps_detector.dart    # Sahte konum algılama
│   │   │   ├── root_detector.dart        # Jailbreak/Root tespiti
│   │   │   └── secure_storage.dart       # Token güvenli saklama
│   │   └── utils/
│   │       ├── date_formatter.dart
│   │       ├── validators.dart
│   │       └── permission_handler.dart
│   │
│   ├── data/
│   │   ├── datasources/
│   │   │   ├── remote/
│   │   │   │   ├── auth_remote_datasource.dart
│   │   │   │   ├── attendance_remote_datasource.dart
│   │   │   │   ├── leave_remote_datasource.dart
│   │   │   │   ├── advance_remote_datasource.dart
│   │   │   │   ├── expense_remote_datasource.dart
│   │   │   │   ├── shift_remote_datasource.dart
│   │   │   │   ├── asset_remote_datasource.dart
│   │   │   │   ├── travel_remote_datasource.dart
│   │   │   │   ├── vehicle_remote_datasource.dart
│   │   │   │   ├── visitor_remote_datasource.dart
│   │   │   │   ├── notification_remote_datasource.dart
│   │   │   │   ├── announcement_remote_datasource.dart
│   │   │   │   ├── personel_remote_datasource.dart
│   │   │   │   ├── holiday_remote_datasource.dart
│   │   │   │   └── dashboard_remote_datasource.dart
│   │   │   └── local/
│   │   │       └── auth_local_datasource.dart
│   │   ├── models/
│   │   │   ├── user_model.dart
│   │   │   ├── login_response_model.dart
│   │   │   ├── time_record_model.dart
│   │   │   ├── leave_request_model.dart
│   │   │   ├── leave_type_model.dart
│   │   │   ├── leave_balance_model.dart
│   │   │   ├── advance_request_model.dart
│   │   │   ├── expense_request_model.dart
│   │   │   ├── expense_category_model.dart
│   │   │   ├── shift_model.dart
│   │   │   ├── asset_model.dart
│   │   │   ├── asset_type_model.dart
│   │   │   ├── travel_request_model.dart
│   │   │   ├── vehicle_model.dart
│   │   │   ├── visitor_model.dart
│   │   │   ├── notification_model.dart
│   │   │   ├── announcement_model.dart
│   │   │   ├── personel_model.dart
│   │   │   ├── holiday_model.dart
│   │   │   ├── dashboard_kpi_model.dart
│   │   │   ├── company_model.dart
│   │   │   ├── department_model.dart
│   │   │   ├── position_model.dart
│   │   │   └── location_model.dart
│   │   └── repositories/
│   │       ├── auth_repository_impl.dart
│   │       ├── attendance_repository_impl.dart
│   │       ├── leave_repository_impl.dart
│   │       ├── advance_repository_impl.dart
│   │       ├── expense_repository_impl.dart
│   │       ├── shift_repository_impl.dart
│   │       ├── asset_repository_impl.dart
│   │       ├── travel_repository_impl.dart
│   │       ├── vehicle_repository_impl.dart
│   │       ├── visitor_repository_impl.dart
│   │       ├── notification_repository_impl.dart
│   │       ├── announcement_repository_impl.dart
│   │       ├── personel_repository_impl.dart
│   │       ├── holiday_repository_impl.dart
│   │       └── dashboard_repository_impl.dart
│   │
│   ├── domain/
│   │   ├── entities/
│   │   │   ├── user.dart
│   │   │   ├── time_record.dart
│   │   │   ├── leave_request.dart
│   │   │   ├── leave_type.dart
│   │   │   ├── leave_balance.dart
│   │   │   ├── advance_request.dart
│   │   │   ├── expense_request.dart
│   │   │   ├── expense_category.dart
│   │   │   ├── shift.dart
│   │   │   ├── asset.dart
│   │   │   ├── travel_request.dart
│   │   │   ├── vehicle.dart
│   │   │   ├── visitor.dart
│   │   │   ├── notification_item.dart
│   │   │   ├── announcement.dart
│   │   │   ├── personel.dart
│   │   │   ├── holiday.dart
│   │   │   ├── dashboard_kpi.dart
│   │   │   ├── company.dart
│   │   │   ├── department.dart
│   │   │   ├── position.dart
│   │   │   └── location.dart
│   │   ├── repositories/
│   │   │   ├── auth_repository.dart
│   │   │   ├── attendance_repository.dart
│   │   │   ├── leave_repository.dart
│   │   │   ├── advance_repository.dart
│   │   │   ├── expense_repository.dart
│   │   │   ├── shift_repository.dart
│   │   │   ├── asset_repository.dart
│   │   │   ├── travel_repository.dart
│   │   │   ├── vehicle_repository.dart
│   │   │   ├── visitor_repository.dart
│   │   │   ├── notification_repository.dart
│   │   │   ├── announcement_repository.dart
│   │   │   ├── personel_repository.dart
│   │   │   ├── holiday_repository.dart
│   │   │   └── dashboard_repository.dart
│   │   └── usecases/                  # (Opsiyonel — basit CRUD için direkt repository)
│   │       ├── login_usecase.dart
│   │       ├── get_dashboard_kpi_usecase.dart
│   │       └── ...
│   │
│   ├── presentation/
│   │   ├── bloc/
│   │   │   ├── auth/
│   │   │   │   ├── auth_bloc.dart
│   │   │   │   ├── auth_event.dart
│   │   │   │   └── auth_state.dart
│   │   │   ├── attendance/
│   │   │   │   ├── attendance_bloc.dart
│   │   │   │   ├── attendance_event.dart
│   │   │   │   └── attendance_state.dart
│   │   │   ├── leave/
│   │   │   │   ├── leave_bloc.dart
│   │   │   │   ├── leave_event.dart
│   │   │   │   └── leave_state.dart
│   │   │   ├── advance/
│   │   │   │   ├── advance_bloc.dart
│   │   │   │   ├── advance_event.dart
│   │   │   │   └── advance_state.dart
│   │   │   ├── expense/
│   │   │   │   ├── expense_bloc.dart
│   │   │   │   ├── expense_event.dart
│   │   │   │   └── expense_state.dart
│   │   │   ├── shift/
│   │   │   │   ├── shift_bloc.dart
│   │   │   │   ├── shift_event.dart
│   │   │   │   └── shift_state.dart
│   │   │   ├── asset/
│   │   │   │   ├── asset_bloc.dart
│   │   │   │   ├── asset_event.dart
│   │   │   │   └── asset_state.dart
│   │   │   ├── travel/
│   │   │   │   ├── travel_bloc.dart
│   │   │   │   ├── travel_event.dart
│   │   │   │   └── travel_state.dart
│   │   │   ├── vehicle/
│   │   │   │   ├── vehicle_bloc.dart
│   │   │   │   ├── vehicle_event.dart
│   │   │   │   └── vehicle_state.dart
│   │   │   ├── visitor/
│   │   │   │   ├── visitor_bloc.dart
│   │   │   │   ├── visitor_event.dart
│   │   │   │   └── visitor_state.dart
│   │   │   ├── notification/
│   │   │   │   ├── notification_bloc.dart
│   │   │   │   ├── notification_event.dart
│   │   │   │   └── notification_state.dart
│   │   │   ├── announcement/
│   │   │   │   ├── announcement_bloc.dart
│   │   │   │   ├── announcement_event.dart
│   │   │   │   └── announcement_state.dart
│   │   │   ├── personel/
│   │   │   │   ├── personel_bloc.dart
│   │   │   │   ├── personel_event.dart
│   │   │   │   └── personel_state.dart
│   │   │   ├── dashboard/
│   │   │   │   ├── dashboard_bloc.dart
│   │   │   │   ├── dashboard_event.dart
│   │   │   │   └── dashboard_state.dart
│   │   │   ├── holiday/
│   │   │   │   ├── holiday_bloc.dart
│   │   │   │   ├── holiday_event.dart
│   │   │   │   └── holiday_state.dart
│   │   │   └── location/
│   │   │       ├── location_bloc.dart
│   │   │       ├── location_event.dart
│   │   │       └── location_state.dart
│   │   ├── screens/
│   │   │   ├── splash/
│   │   │   │   └── splash_screen.dart
│   │   │   ├── auth/
│   │   │   │   ├── login_screen.dart
│   │   │   │   └── forgot_password_screen.dart
│   │   │   ├── main/
│   │   │   │   └── main_shell.dart        # Scaffold + Bottom Nav + Drawer
│   │   │   ├── dashboard/
│   │   │   │   └── dashboard_screen.dart
│   │   │   ├── attendance/
│   │   │   │   ├── attendance_screen.dart        # Giriş/Çıkış ana ekranı
│   │   │   │   └── attendance_history_screen.dart
│   │   │   ├── puantaj/
│   │   │   │   ├── puantaj_screen.dart           # Aylık puantaj görünümü
│   │   │   │   └── puantaj_detail_screen.dart    # Günlük detay
│   │   │   ├── locations/
│   │   │   │   └── my_locations_screen.dart      # Konumlarım (giriş/çıkış konumları)
│   │   │   ├── leave/
│   │   │   │   ├── leave_requests_screen.dart
│   │   │   │   ├── leave_create_screen.dart
│   │   │   │   └── leave_detail_screen.dart
│   │   │   ├── advance/
│   │   │   │   ├── advance_requests_screen.dart
│   │   │   │   ├── advance_create_screen.dart
│   │   │   │   └── advance_detail_screen.dart
│   │   │   ├── expense/
│   │   │   │   ├── expense_requests_screen.dart
│   │   │   │   ├── expense_create_screen.dart
│   │   │   │   └── expense_detail_screen.dart
│   │   │   ├── shift/
│   │   │   │   ├── shift_screen.dart
│   │   │   │   └── shift_swap_screen.dart
│   │   │   ├── asset/
│   │   │   │   ├── assets_screen.dart
│   │   │   │   └── asset_detail_screen.dart
│   │   │   ├── travel/
│   │   │   │   ├── travel_requests_screen.dart
│   │   │   │   ├── travel_create_screen.dart
│   │   │   │   └── travel_detail_screen.dart
│   │   │   ├── vehicle/
│   │   │   │   ├── vehicles_screen.dart
│   │   │   │   └── vehicle_detail_screen.dart
│   │   │   ├── visitor/
│   │   │   │   ├── visitors_screen.dart
│   │   │   │   └── visitor_create_screen.dart
│   │   │   ├── announcement/
│   │   │   │   ├── announcements_screen.dart
│   │   │   │   └── announcement_detail_screen.dart
│   │   │   ├── notification/
│   │   │   │   └── notifications_screen.dart
│   │   │   ├── holiday/
│   │   │   │   └── holidays_screen.dart
│   │   │   ├── profile/
│   │   │   │   ├── profile_screen.dart
│   │   │   │   └── profile_edit_screen.dart
│   │   │   └── company/
│   │   │       ├── company_info_screen.dart
│   │   │       └── departments_screen.dart
│   │   ├── widgets/
│   │   │   ├── common/
│   │   │   │   ├── app_button.dart
│   │   │   │   ├── app_text_field.dart
│   │   │   │   ├── app_card.dart
│   │   │   │   ├── loading_indicator.dart
│   │   │   │   ├── error_widget.dart
│   │   │   │   ├── empty_state_widget.dart
│   │   │   │   ├── status_badge.dart
│   │   │   │   ├── confirm_dialog.dart
│   │   │   │   └── connectivity_banner.dart
│   │   │   ├── drawer/
│   │   │   │   ├── app_drawer.dart
│   │   │   │   └── drawer_menu_item.dart
│   │   │   ├── dashboard/
│   │   │   │   ├── kpi_card.dart
│   │   │   │   ├── quick_action_grid.dart
│   │   │   │   └── today_status_card.dart
│   │   │   ├── attendance/
│   │   │   │   ├── clock_button.dart
│   │   │   │   ├── location_status.dart
│   │   │   │   └── shift_info_card.dart
│   │   │   ├── puantaj/
│   │   │   │   ├── monthly_calendar.dart
│   │   │   │   └── daily_row.dart
│   │   │   └── notification/
│   │   │       └── notification_tile.dart
│   │   └── providers/
│   │       └── theme_provider.dart
│   │
│   └── services/
│       ├── firebase_messaging_service.dart   # FCM push notification
│       ├── location_service.dart             # Gerçek konum alma
│       └── local_notification_service.dart   # Yerel bildirimler
│
├── assets/
│   ├── images/
│   │   ├── logo.png
│   │   ├── logo_white.png
│   │   └── splash_logo.png
│   ├── fonts/
│   │   └── ...
│   ├── lottie/
│   │   └── splash_animation.json
│   └── i18n/                              # (ilerde çoklu dil)
│       └── tr.json
│
├── test/
│   ├── unit/
│   │   ├── bloc/
│   │   └── repositories/
│   ├── widget/
│   └── integration/
│
├── pubspec.yaml
├── analysis_options.yaml
└── README.md
```

---

## 2. EKRANLAR & MÖDÜLLER

### 2.1 Splash Screen
- Uygulama açılışında gösterilir
- Token kontrolü yapar (geçerli token varsa ana sayfaya, yoksa login'e yönlendir)
- İnternet bağlantısı kontrolü
- Firebase cihaz token kaydı

### 2.2 Login Screen
- E-posta + şifre girişi
- "Beni Hatırla" seçeneği
- Şifremi Unuttum bağlantısı
- Biyometrik giriş (parmak izi/yüz tanıma) desteği (opsiyonel)
- **Güvenlik:** Sahte konum algılama aktif, root/jailbreak tespiti

### 2.3 Forgot Password Screen
- E-posta ile şifre sıfırlama bağlantısı gönderme
- Backend `/api/v1/password/forgot` endpoint'ini kullanır

### 2.4 Ana Sayfa (Dashboard)
- **En Üst:** Özel saat tanımlanmışsa gösterimi (SpecialHour)
- **KPI Kartları:** Toplam personel, bekleyen izinler, bugünkü devamsızlık
- **Hızlı İşlemler:** İzin talep, avans talep, masraf talep, seyahat talep, giriş/çıkış yap
- **Gün Durumu:**
  - Bugün giriş yaptıysa yeşil badge "Giriş Yapıldı"
  - Çıkış yaptıysa "Mesai Tamamlandı"
  - Henüz giriş yapılmadıysa "Giriş Yapılmadı" + buton
- **Son Bildirimler:** Son 5 bildirim listesi
- **Vardiya Bilgisi:** Varsa bugünkü vardiya adı, başlangıç-bitiş saati

### 2.5 Giriş/Çıkış (Attendance) Ekranı
- **Giriş Yap Butonu:** Kullanıcı konumunu alır, tanımlı lokasyon içinde mi kontrol eder
- **Çıkış Yap Butonu:** Giriş yapıldıysa aktif olur
- **Konum Kontrolü:** Kullanıcının GPS konumu, şirketin tanımladığı lokasyonların (Location model) radius'u içinde mi kontrol edilir
- **Sahte Konum Algılama:**
  - Android: `LocationManager.isFromMockProvider()` kontrolü
  - iOS: `CLLocationManager` sourceType kontrolü
  - Flutter: `trusted_location` / `fake_location_detector` paketi
  - API tarafında da doğrulama (backend lokasyon kontrolü)
- **İnternet Kontrolü:** Giriş/çıkış öncesi internet bağlantısı kontrolü, yoksa uyarı göster
- **Kamera İsteği:** Opsiyonel selfie ile giriş doğrulama (ileri versiyon)

### 2.6 Konumlarım (My Locations) Ekranı
- Kullanıcının atandığı lokasyonların harita görünümü
- Her lokasyon için:
  - İsim, adres
  - Koordinatlar (enlem/boylam)
  - Radius bilgisi
  - Şu an bu lokasyonda mı? (canlı kontrol)
- Kullanıcının son giriş/çıkış konumlarının harita üzerinde gösterimi
- Kullanıcı için tanımlı saat (SpecialHour) varsa gösterimi

### 2.7 Puantaj Ekranı
- **Aylık Takvim Görünümü:**
  - Her gün için: giriş/çıkış saati, net çalışma süresi
  - Renk kodları: yeşil (tam gün), sarı (eksik), kırmızı (gelmedi), mavi (izinli)
  - İzin günleri belirgin işaretli
  - Resmi tatiller gösterilir
- **Aylık Özet:**
  - Toplam çalışma günü / saat
  - Toplam izin günü
  - Toplam geç kalma (dk)
  - Toplam fazla mesai (saat)
  - Verimlilik yüzdesi
  - Eksik günler
- **Günlük Detay:** Bir güne tıklayınca o günün tüm time_record'ları listelenir

### 2.8 İzin Yönetimi
- **İzin Taleplerim:** Tüm izin taleplerinin listesi
  - Filtre: durum, tarih aralığı
  - Her talep için: izin türü, tarih, gün sayısı, durum badge'i
- **İzin Talep Oluştur:**
  - İzin türü seç (LeaveType)
  - Başlangıç/bitiş tarihi
  - Açıklama
  - Kalan izin günü gösterimi
- **İzin Detay:** Onay akışı, onaylayan bilgisi
- **İzin Geçmişi Takvimi:** Onaylı izinlerin takvim görünümü

### 2.9 Avans Yönetimi
- **Avans Taleplerim:** Liste + durum badge'leri
- **Avans Talep Oluştur:** Miktar, para birimi, açıklama
- **Avans Detay:** Onay/red bilgisi, ödeme planı

### 2.10 Masraf Yönetimi
- **Masraf Taleplerim:** Liste + durum badge'leri
- **Masraf Talep Oluştur:**
  - Kategori seç (ExpenseCategory)
  - Miktar, para birimi
  - Tarih, açıklama
  - Fiş/fotoğraf ekle (attachments)
- **Masraf Detay:** Onay/red/ödeme durumu

### 2.11 Vardiya Ekranı
- **Bugünkü Vardiyam:** Vardiya adı, saat aralığı
- **Vardiya Takvimi:** Aylık vardiya planı görünümü (ShiftPlan)
- **Vardiya Değişim İstekleri:** Swap talep listesi + oluşturma

### 2.12 Envanter & Zimmet
- **Zimmetimdekiler:** Kullanıcıya atanmış asset'lerin listesi
  - Asset adı, türü, seri no, durum badge'i
  - Garanti durumu
- **Tüm Envanter:** Filtreleme + arama
- **Asset Detay:** Zimmet geçmişi

### 2.13 Seyahat Yönetimi
- **Seyahat Taleplerim:** Liste + durum badge'leri
- **Seyahat Talep Oluştur:**
  - Gidiş/dönüş tarihi
  - Gidilecek yer
  - Amaç / açıklama
  - Tahmini bütçe
- **Seyahat Detay:** Onay akışı

### 2.14 Araç Yönetimi
- **Araç Rezervasyonlarım:** Kullanıcının araç kullanım kayıtları
- **Araç Talebi:** Araç seç, tarih aralığı, amaç
- **Araç Listesi:** Şirket araçları (sadece görüntüleme)
- **Yakıt Kayıtları:** (sadece görüntüleme)

### 2.15 Ziyaretçi Yönetimi
- **Ziyaretçi Kayıtları:** Liste
- **Ziyaretçi Kaydı Oluştur:**
  - Ad soyad, şirket, belge no
  - Giriş/çıkış saati
  - Görüşülecek kişi
- **Ziyaretçi Giriş/Çıkış:** QR okutma ile hızlı giriş (ileri versiyon)

### 2.16 Duyuru & Anketler
- **Duyurular:** Şirket duyuruları listesi
  - Önemli duyurular pinned (sabitleme)
  - Tarih, başlık, içerik
- **Duyuru Detay:** Tam içerik görüntüleme

### 2.17 Bildirimler
- **Bildirim Listesi:** Tüm bildirimler
  - Okundu/okunmadı durumu
  - Bildirim türüne göre ikon
  - Tarih
- **Bildirim Detay:** İlgili sayfaya yönlendirme

### 2.18 Tatil Takvimi
- Yıllık tatil listesi
- Bugünden itibaren yaklaşan tatiller
- Resmi tatiller + şirket tatilleri

### 2.19 Profil Ekranı
- **Kullanıcı Bilgileri:** Ad, soyad, e-posta, telefon, rol, departman, pozisyon
- **Personel Bilgileri:** İşe giriş tarihi, personel no
- **Profil Düzenle:**
  - Ad soyad (kullanıcı adı)
  - E-posta
  - Telefon
  - Şifre değiştirme
  - Profil fotoğrafı

### 2.20 Şirket Bilgileri
- Şirket adı, adres, iletişim
- Departman listesi
- Pozisyon listesi

---

## 3. API ENDPOINTLERİ

### 3.1 Mevcut Backend API'leri (Kullanılacak)

| # | Metot | Endpoint | Açıklama |
|---|-------|----------|----------|
| 1 | POST | `/api/v1/login` | Giriş |
| 2 | POST | `/api/v1/logout` | Çıkış |
| 3 | POST | `/api/v1/logout-all` | Tüm cihazlardan çıkış |
| 4 | GET | `/api/v1/user` | Kullanıcı bilgileri |
| 5 | POST | `/api/v1/password/forgot` | Şifre sıfırlama maili |
| 6 | POST | `/api/v1/password/reset` | Şifre sıfırlama |
| 7 | GET | `/api/v1/dashboard/kpi` | Dashboard KPI |
| 8 | GET | `/api/v1/personels` | Personel listesi |
| 9 | GET | `/api/v1/personels/{id}` | Personel detay |
| 10 | GET | `/api/v1/departments` | Departman listesi |
| 11 | GET | `/api/v1/positions` | Pozisyon listesi |
| 12 | GET | `/api/v1/leave-types` | İzin türleri |
| 13 | GET | `/api/v1/leave-requests` | İzin talepleri |
| 14 | POST | `/api/v1/leave-requests` | İzin talep oluştur |
| 15 | POST | `/api/v1/leave-requests/{id}/approve` | İzin onayla |
| 16 | POST | `/api/v1/leave-requests/{id}/reject` | İzin reddet |
| 17 | POST | `/api/v1/leave-requests/{id}/cancel` | İzin iptal |
| 18 | GET | `/api/v1/leave-balances` | İzin bakiyeleri |
| 19 | GET | `/api/v1/leave/calendar` | İzin takvimi |
| 20 | GET | `/api/v1/attendance/today` | Bugünkü puantaj |
| 21 | GET | `/api/v1/attendance/daily-summary` | Günlük özet |
| 22 | GET | `/api/v1/attendance/monthly-summary` | Aylık özet |
| 23 | POST | `/api/v1/attendance/record` | Kayıt oluştur |
| 24 | GET | `/api/v1/shifts` | Vardiya listesi |
| 25 | GET | `/api/v1/shifts/roster` | Vardiya planı |
| 26 | POST | `/api/v1/shifts/clock-in` | Giriş yap |
| 27 | POST | `/api/v1/shifts/clock-out` | Çıkış yap |
| 28 | GET | `/api/v1/assets` | Envanter listesi |
| 29 | GET | `/api/v1/assets/{id}` | Envanter detay |
| 30 | GET | `/api/v1/asset-types` | Varlık türleri |
| 31 | GET | `/api/v1/advances` | Avans talepleri |
| 32 | POST | `/api/v1/advances` | Avans talep oluştur |
| 33 | GET | `/api/v1/expenses` | Masraf talepleri |
| 34 | POST | `/api/v1/expenses` | Masraf talep oluştur |
| 35 | GET | `/api/v1/expense-categories` | Masraf kategorileri |
| 36 | GET | `/api/v1/travel` | Seyahat talepleri |
| 37 | POST | `/api/v1/travel` | Seyahat talep oluştur |
| 38 | GET | `/api/v1/vehicles` | Araç listesi |
| 39 | GET | `/api/v1/visitors` | Ziyaretçi listesi |
| 40 | POST | `/api/v1/visitors` | Ziyaretçi kaydı |
| 41 | GET | `/api/v1/notifications/recent` | Son bildirimler |
| 42 | GET | `/api/v1/notifications/unread-count` | Okunmamış sayısı |
| 43 | POST | `/api/v1/notifications/mark-read` | Okundu işaretle |

### 3.2 Mobil İçin Eklenecek Yeni API'ler

| # | Metot | Endpoint | Açıklama |
|---|-------|----------|----------|
| 1 | PUT | `/api/v1/profile` | Profil güncelle (ad, e-posta, telefon) |
| 2 | PUT | `/api/v1/profile/password` | Şifre değiştir |
| 3 | POST | `/api/v1/profile/photo` | Profil fotoğrafı yükle |
| 4 | GET | `/api/v1/my-locations` | Kullanıcının atandığı lokasyonlar |
| 5 | POST | `/api/v1/attendance/clock-in` | Konum doğrulamalı giriş |
| 6 | POST | `/api/v1/attendance/clock-out` | Konum doğrulamalı çıkış |
| 7 | GET | `/api/v1/my-locations/check` | Kullanıcının mevcut konumu tanımlı lokasyonlarda mı? |
| 8 | POST | `/api/v1/device/register` | Firebase cihaz token kaydı |
| 9 | POST | `/api/v1/device/unregister` | Firebase cihaz token sil |
| 10 | GET | `/api/v1/announcements` | Duyuru listesi |
| 11 | GET | `/api/v1/announcements/{id}` | Duyuru detay |
| 12 | GET | `/api/v1/my-shifts` | Kullanıcının vardiyaları |
| 13 | GET | `/api/v1/my-assets` | Kullanıcıya zimmetli varlıklar |
| 14 | GET | `/api/v1/holidays` | Tatil listesi |
| 15 | GET | `/api/v1/company/info` | Şirket bilgileri |
| 16 | GET | `/api/v1/special-hours` | Özel saat bilgisi |

### 3.3 API İstek/Yanıt Formatı

**Login Request:**
```json
{
  "email": "user@company.com",
  "password": "password123",
  "device_name": "iPhone 15 Pro"
}
```

**Login Response:**
```json
{
  "token": "1|abc123...",
  "token_type": "Bearer",
  "expires_at": "2026-07-15T00:00:00.000000Z",
  "user": {
    "id": 1,
    "name": "Ahmet Yılmaz",
    "email": "ahmet@company.com",
    "company_id": 1,
    "roles": ["employee"],
    "permissions": ["leave.view", "leave.request", ...],
    "personel_id": 5,
    "phone": "5551234567"
  }
}
```

**Error Response:**
```json
{
  "message": "Geçersiz kimlik bilgileri.",
  "errors": {
    "email": ["Geçersiz kimlik bilgileri."]
  }
}
```

---

## 4. YETKİLENDİRME & VERİ Erişimi

### Mobil Kullanıcı Rolleri ve Erişimleri:

| Rol | Erişebileceği Ekranlar | Veri Değiştirme |
|-----|----------------------|-----------------|
| **employee** | Dashboard, Giriş/Çıkış, Puantaj (kendi), İzin (kendi), Avans (kendi), Masraf (kendi), Seyahat (kendi), Envanter (zimmetli), Araç, Ziyaretçi, Duyuru, Bildirim, Tatil, Profil, Şirket Bilgileri, Konumlarım | Sadece kendi talep oluşturma + profil düzenleme |
| **manager** | employee + departmanındaki personellerin verilerini görme + onay/ret işlemleri | Talep onaylama/ret + profil düzenleme |
| **hr_manager** | Tüm personel verileri + tüm talepleri görme + onay/ret | Talep onaylama/ret + profil düzenleme |
| **company_admin** | Şirketteki tüm veriler | Talep onaylama/ret + profil düzenleme |
| **super_admin** | Tüm veriler | Talep onaylama/ret + profil düzenleme |

### KISITLAMALAR:
- **Kullanıcı SADECE kendi profilini düzenleyebilir** (ad, e-posta, telefon, şifre)
- **Hiçbir kullanıcı başka bir kullanıcının verisini güncelleyemez**
- **Hiçbir Kullanıcı Başja bir Kullanıcın Hesabını Ve Kullanıcıyı Göremez**
- **Kullanıcı Hnagi Depatrmanda Oldugu Anasayfa Dashboard Yazıcak**
- **Hiçbir kullanıcı silme işlemi yapamaz**
- **Talepler sadece oluşturulabilir, onay/ret işlemleri sadece yetkili roller yapar**
- **Tüm CRUD işlemleri backend tarafında authorize() kontrolünden geçer**

---

## 5. GÜVENLİK

### 5.1 Sahte Konum Algılama (Fake GPS Detection)

**Android:**
```kotlin
// AndroidManifest.xml'de gerekli
// <uses-permission android:name="android.permission.ACCESS_FINE_LOCATION"/>

fun isMockLocation(): Boolean {
    if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
        return locationManager.isLocationEnabled && 
               locationManager.getProvider(LocationManager.GPS_PROVIDER)?.let {
                   it.requiresNetwork() || it.requiresSatellite()
               } == false
    } else {
        @Suppress("DEPRECATION")
        return locationManager.isProviderEnabled(LocationManager.GPS_PROVIDER) &&
               locationManager.getProviders(true).any { provider ->
                   locationManager.isProviderEnabled(provider) &&
                   !provider.equals(LocationManager.GPS_PROVIDER, ignoreCase = true)
               }
    }
}
```

**iOS (Swift):**
```swift
// Info.plist'te gerekli
// <key>NSLocationWhenInUseUsageDescription</key>
// <string>Konum bilgisi giriş/çıkış işlemleri için gereklidir</string>

func isLocationSimulated() -> Bool {
    if #available(iOS 15.0, *) {
        return CLLocationManager().sourceInformation?.isSimulatedBySoftware ?? false
    } else {
        return false // iOS 15 öncesi native kontrol sınırlı
    }
}
```

**Flutter Katmanı:**
- `fake_gps_detector.dart` servisi ile her giriş/çıkış öncesi kontrol
- Sahte konum tespit edilirse: kullanıcı uyarılır, işlem engellenir
- Loglama: backend'e `is_mock_location: true/false` bilgisi gönderilir
- API validation: backend da konum doğrulaması yapar

### 5.2 İnternet Kontrolü
- `connectivity_plus` paketi ile sürekli internet takibi
- İnternet kesildiğinde `ConnectivityBanner` widget'ı gösterilir
- Kritik işlemler (giriş/çıkış) öncesi internet kontrolü
- İnternet yoksa kullanıcı uyarılır ve işlem engellenir

### 5.3 Root/Jailbreak Tespiti
- `root_detector.dart` ile cihaz root/jailbreak kontrolü
- Root'lu cihazlarda uygulama çalışmaz veya uyarı gösterir
- Android: `SafetyNet` / `Play Integrity` API entegrasyonu (ileri versiyon)

### 5.4 Token Yönetimi
- Token `flutter_secure_storage` ile güvenli saklama
- Token süresi dolduğunda otomatik logout
- 401 hatalarında token silinir, login ekranına yönlendirilir
- Interceptor ile her isteğe `Authorization: Bearer <token>` header'ı eklenir

---

## 6. BİLDİRİM SİSTEMİ (Firebase Cloud Messaging)

### 6.1 Push Notification Akışı
```
Backend (Laravel) 
  → Firebase Cloud Messaging (FCM)
    → Mobil Uygulama (Flutter)
      → Bildirim gösterimi
      → Bildirime tıklayınca ilgili sayfaya yönlendirme
```

### 6.2 Bildirim Türleri
| Olay | Bildirim Başlığı | Yönlendirme |
|------|-----------------|-------------|
| İzin onaylandı | "İzin Talebin Onaylandı" | İzin detay |
| İzin reddedildi | "İzin Talebin Reddedildi" | İzin detay |
| Avans onaylandı | "Avans Talebin Onaylandı" | Avans detay |
| Avans reddedildi | "Avans Talebin Reddedildi" | Avans detay |
| Masraf onaylandı | "Masraf Talebin Onaylandı" | Masraf detay |
| Masraf reddedildi | "Masraf Talebin Reddedildi" | Masraf detay |
| Masraf ödendi | "Masraf Ödemen Yapıldı" | Masraf detay |
| Seyahat onaylandı | "Seyahat Talebin Onaylandı" | Seyahat detay |
| Seyahat reddedildi | "Seyahat Talebin Reddedildi" | Seyahat detay |
| Yeni duyuru | "Yeni Duyuru: {başlık}" | Duyuru detay |
| Vardiya değişti | "Vardiyan Güncellendi" | Vardiya ekranı |
| Vardiya swap talebi | "Vardiya Değişim Talebi" | Vardiya swap |
| Personel onboarding | "Hoş Geldin {isim}!" | Dashboard |

### 6.3 Firebase Yapılandırması
- **Android:** `google-services.json` (Firebase Console'dan indir)
- **iOS:** `GoogleService-Info.plist` (Firebase Console'dan indir)
- **Flutter:** `firebase_messaging` paketi ile entegrasyon
- **Arka Plan Bildirimleri:** iOS'da `content-available: 1` ile silent push
- **Bildirim Kanalları:** Android'de bildirim kanalları oluşturma

### 6.4 Backend FCM Gönderimi (Laravel)
```php
// App\Services\FCMService.php
public function sendPushNotification($deviceTokens, $title, $body, $data = [])
{
    $message = [
        'tokens' => is_array($deviceTokens) ? $deviceTokens : [$deviceTokens],
        'notification' => [
            'title' => $title,
            'body'  => $body,
        ],
        'data' => $data, // screen, id gibi yönlendirme bilgileri
    ];
    
    // Firebase Admin SDK veya HTTPS ile gönderim
}
```

### 6.5 Kapalı Uygulama Bildirimleri
- **Android:** FCM bildirimleri uygulama kapalıyken system tray'de gösterilir
- **iOS:** APNs üzerinden FCM bildirimleri kapalıyken gelir
- Backend'de `firebase_tokens` tablosu: user_id, token, device_type, created_at

---

## 7. İZİNLER (Permissions)

### 7.1 Android (AndroidManifest.xml)
```xml
<!-- Temel -->
<uses-permission android:name="android.permission.INTERNET"/>
<uses-permission android:name="android.permission.ACCESS_NETWORK_STATE"/>

<!-- Konum -->
<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION"/>
<uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION"/>
<uses-permission android:name="android.permission.ACCESS_BACKGROUND_LOCATION"/>

<!-- Bildirim -->
<uses-permission android:name="android.permission.POST_NOTIFICATIONS"/>

<!-- Kamera (ilerde selfie doğrulama için) -->
<uses-permission android:name="android.permission.CAMERA"/>

<!-- Depolama (dosya yükleme için) -->
<uses-permission android:name="android.permission.READ_EXTERNAL_STORAGE"/>
<uses-permission android:name="android.permission.WRITE_EXTERNAL_STORAGE"/>

<!-- Biyometrik (parmak izi) -->
<uses-permission android:name="android.permission.USE_BIOMETRIC"/>
```

### 7.2 iOS (Info.plist)
```xml
<key>NSLocationWhenInUseUsageDescription</key>
<string>Uygulama, giriş/çıkış işlemlerinde konumunuzu kullanır.</string>

<key>NSLocationAlwaysAndWhenInUseUsageDescription</key>
<string>Uygulama, giriş/çıkış işlemlerinde konumunuzu kullanır.</string>

<key>NSCameraUsageDescription</key>
<string>Kamera, profil fotoğrafı yükleme için kullanılır.</string>

<key>NSPhotoLibraryUsageDescription</key>
<string>Fotoğraf kütüphanesi, profil fotoğrafı yükleme için kullanılır.</string>

<key>UIBackgroundModes</key>
<array>
    <string>fetch</string>
    <string>remote-notification</string>
</array>

<key>FirebaseMessagingAutoInitEnabled</key>
<false/>
```

### 7.3 Runtime İzin İsteme Sırası
1. **Uygulama ilk açılış:** Bildirim izni (iOS)
2. **Login sonrası:** Konum izni
3. **İlk giriş/çıkış:** Konum izni (tekrar kontrol)
4. **Profil fotoğrafı:** Kamera/Galeri izni
5. **Masraf fişi:** Kamera/Galeri izni
6. **Arka plan konumu:** Sadece gerekirse (iOS)

---

## 8. PAKETLER (pubspec.yaml)

```yaml
name: miysoft_pts_mobile
description: MİYSOFT PTS Mobil Uygulama
version: 1.0.0+1

environment:
  sdk: '>=3.0.0 <4.0.0'

dependencies:
  flutter:
    sdk: flutter

  # State Management
  flutter_bloc: ^8.1.3
  bloc: ^8.1.2
  equatable: ^2.0.5

  # Routing
  go_router: ^13.0.0

  # Network
  dio: ^5.4.0
  retrofit: ^4.1.0
  retrofit_generator: ^8.1.0  # (build_runner ile)
  json_annotation: ^4.8.1

  # Local Storage
  flutter_secure_storage: ^9.0.0
  shared_preferences: ^2.2.2

  # Location & Maps
  geolocator: ^10.1.0
  geocoding: ^2.1.1
  google_maps_flutter: ^2.5.3
  flutter_polyline_points: ^2.0.0

  # Fake GPS Detection
  fake_location_detector: ^1.0.0  # veya custom implementation
  trusted_location: ^1.0.0

  # Connectivity
  connectivity_plus: ^5.0.2

  # Firebase
  firebase_core: ^2.25.4
  firebase_messaging: ^14.7.15
  firebase_analytics: ^10.8.6

  # Local Notifications (Flutter Local + FCM combined)
  flutter_local_notifications: ^17.0.0

  # Biometrics
  local_auth: ^2.1.8

  # UI Components
  flutter_svg: ^2.0.9
  cached_network_image: ^3.3.1
  shimmer: ^3.0.0
  lottie: ^3.0.0
  fl_chart: ^0.66.0
  table_calendar: ^3.0.9
  image_picker: ^1.0.7

  # Permissions
  permission_handler: ^11.2.0

  # Utils
  intl: ^0.19.0
  url_launcher: ^6.2.2
  path_provider: ^2.1.2
  package_info_plus: ^7.0.0
  device_info_plus: ^9.1.1

  # Mason (template generation — development only)
  mason: ^0.1.0-dev.53  # CLI aracı, kod üretimi için

dev_dependencies:
  flutter_test:
    sdk: flutter
  flutter_lints: ^3.0.1
  mockito: ^5.4.4
  bloc_test: ^9.1.5

  # Code Generation
  build_runner: ^2.4.8
  json_serializable: ^6.7.1
  retrofit_generator: ^8.1.0
  freezed: ^2.4.7          # (opsiyonel — immutable modeller için)
  freezed_annotation: ^2.4.1

flutter:
  uses-material-design: true
  assets:
    - assets/images/
    - assets/fonts/
    - assets/lottie/
```

---

## 9. TEMA (Backend ile Aynı Renk Paleti)

```dart
// app_colors.dart
class AppColors {
  // Brand Colors (Backend ile aynı)
  static const Color brand = Color(0xFF02E0FB);     // Cyan - Ana marka rengi
  static const Color brandDark = Color(0xFF01B3CA);
  static const Color brandLight = Color(0xFF66F0FF);
  static const Color accent = Color(0xFFFA6001);     // Turuncu - Vurgu rengi
  
  // Background
  static const Color background = Color(0xFFF8FAFC);  // Slate 50
  static const Color surface = Color(0xFFFFFFFF);
  static const Color sidebarBg = Color(0xFF0F172A);    // Slate 900
  
  // Text
  static const Color textPrimary = Color(0xFF1E293B);  // Slate 800
  static const Color textSecondary = Color(0xFF64748B); // Slate 500
  static const Color textMuted = Color(0xFF94A3B8);    // Slate 400
  
  // Status Colors
  static const Color success = Color(0xFF22C55E);      // Green 500
  static const Color warning = Color(0xFFEAB308);      // Yellow 500
  static const Color error = Color(0xFFEF4444);        // Red 500
  static const Color info = Color(0xFF3B82F6);         // Blue 500
  
  // Status Badge Backgrounds
  static const Color pendingBg = Color(0xFFFEF9C3);    // Yellow 100
  static const Color approvedBg = Color(0xFFDCFCE7);   // Green 100
  static const Color rejectedBg = Color(0xFFFEE2E2);   // Red 100
  static const Color cancelledBg = Color(0xFFF1F5F9);  // Slate 100
}
```

### Tema Bileşenleri:
- **Drawer:** Koyu tema (Slate 900 arka plan, beyaz metin) - backend sidebar'ı ile aynı
- **Ana İçerik:** Açık tema (Slate 50 arka plan)
- **Kartlar:** Beyaz arka plan, gölgeli, yuvarlak köşeler
- **Butonlar:** Brand rengi (Cyan) primary, Accent (Turuncu) secondary
- **Badge'ler:** Backend'deki gibi renk kodları
- **İkonlar:** Outline style (çizgi ikonlar)
- **Yazı Tipleri:** System default (Inter benzeri)

---

## 10. NAVİGASYON YAPISI

### Bottom Navigation Bar (Ana Alt Menü)
1. **Ana Sayfa** (Dashboard) — Ev ikonu
2. **Giriş/Çıkış** — El kronometresi ikonu
3. **Puantaj** — Takvim ikonu
4. **Bildirimler** — Zil ikonu (badge ile okunmamış sayısı)
5. **Profil** — Kullanıcı ikonu

### Hamburger Drawer Menü (Sol Tarafta)
```
MİYSOFT PTS
─────────────
Kullanıcı Adı
Rolü
─────────────
📊  Genel Bakış
──── İNSAN KAYNAKLARI ────
👥  Personel (kendi bilgisi)
📅  İzin Yönetimi
⏰  Puantaj
🔄  Vardiya
──── KONUMLAR ────
📍  Konumlarım
⏱  Özel Saat
──── VARLIKLAR & FİNANS ────
📦  Envanter & Zimmet
💰  Avans
🧾  Masraf
✈️  Seyahat
🚗  Araç Yönetimi
──── ETKİLEŞİM ────
📢  Duyurular
👤  Ziyaretçi
──── TAKVİM ────
🎉  Tatil Takvimi
──── ŞİRKET ────
🏢  Şirket Bilgileri
──── SİSTEM ────
❓  Yardım & Destek
📄  Dökümantasyon
─────────────
⚙️  Ayarlar
🚪  Çıkış Yap
```

---

## 11. VERİ AKIŞI (BLoC Pattern)

```
UI (Screen)
    ↓
BLoC (Business Logic Component)
    ↓
Repository (Abstract Interface)
    ↓
Data Source (Remote/Local)
    ↓
API Client (Dio) / Local Storage
```

### Örnek Akış: Giriş/Çıkış (Clock In)
```
1. Kullanıcı "Giriş Yap" butonuna basar
2. AttendanceScreen → AttendanceBloc.add(ClockInEvent())
3. AttendanceBloc:
   a. LocationService.getCurrentLocation() → GPS konumu al
   b. FakeGpsDetector.isMockLocation() → sahte konum kontrolü
   c. NetworkInfo.isConnected() → internet kontrolü
   d. AttendanceRepository.clockIn(lat, lng, is_mock)
   e. API: POST /api/v1/attendance/clock-in
   f. Backend: konum doğrulama, TimeRecord oluşturma
   g. Bloc → ClockInSuccess state
4. UI: Başarılı mesajı göster, clock button state güncelle
```

### Örnek Akış: İzin Talep Oluşturma
```
1. Kullanıcı izin türü, tarih, açıklama girer
2. LeaveCreateScreen → LeaveBloc.add(CreateLeaveRequest(data))
3. LeaveBloc:
   a. LeaveRepository.createLeaveRequest(data)
   b. API: POST /api/v1/leave-requests
   c. Backend: validation, LeaveRequest oluşturma
   d. Bloc → CreateLeaveRequestSuccess state
4. UI: Başarılı mesajı, listeye dön
```

---

## 12. GERÇEK ZAMANLI GÜNCELLEME (BLoC ile)

- Her modül kendi BLoC'una sahiptir
- Sayfa açıldığında BLoC tetiklenir, veri yüklenir
- Pull-to-refresh ile manuel yenileme
- Bazı modüllerde otomatik polling (ör: bildirimler 30sn, giriş/çıkış durumu 15sn)
- Firebase push notification ile anlık bildirim
- Backend'de değişiklik olduğunda push notification tetiklenir
- Kullanıcı bildirime tıklayınca ilgili sayfaya yönlendirilir

---

## 13. HATA YÖNETİMİ

| HTTP Kodu | Durum | Kullanıcıya Gösterilecek Mesaj |
|-----------|-------|-------------------------------|
| 200 | Başarılı | İşleminiz başarıyla tamamlandı |
| 400 | Hatalı istek | Lütfen bilgileri kontrol edin |
| 401 | Yetkisiz | Oturum süreniz doldu, lütfen tekrar giriş yapın |
| 403 | Yasak | Bu işlem için yetkiniz bulunmuyor |
| 404 | Bulunamadı | Kayıt bulunamadı |
| 422 | Doğrulama hatası | {validasyon mesajı} |
| 429 | Çok fazla istek | Çok fazla istek gönderdiniz, lütfen bekleyin |
| 500 | Sunucu hatası | Beklenmeyen bir hata oluştu, lütfen tekrar deneyin |
| -1 | İnternet yok | İnternet bağlantınızı kontrol edin |
| -2 | Sahte konum | Lütfen sahte konum uygulamasını kapatın |
| -3 | Konum dışı | Giriş/çıkış için tanımlı lokasyonda olmalısınız |

---

## 14. ADIM ADIM KURULUM & GELİŞTİRME

### Aşama 1: Flutter Proje Oluşturma
```bash
flutter create --org com.miysoft --project-name miysoft_pts_mobile miysoft_pts_mobile
cd miysoft_pts_mobile
```

### Aşama 2: Bağımlılıkları Kurma
```bash
flutter pub add flutter_bloc bloc equatable
flutter pub add go_router
flutter pub add dio retrofit json_annotation
flutter pub add flutter_secure_storage shared_preferences
flutter pub add geolocator geocoding google_maps_flutter
flutter pub add connectivity_plus
flutter pub add firebase_core firebase_messaging firebase_analytics
flutter pub add flutter_local_notifications
flutter pub add permission_handler
flutter pub add intl cached_network_image shimmer lottie fl_chart
flutter pub add image_picker
flutter pub add dev:build_runner dev:json_serializable dev:retrofit_generator
```

### Aşama 3: Firebase Yapılandırması
1. Firebase Console'da proje oluştur
2. Android uygulaması ekle (`com.miysoft.miysoft_pts_mobile`)
3. `google-services.json` indir → `android/app/` klasörüne koy
4. iOS uygulaması ekle (`com.miysoft.miysoft_pts_mobile`)
5. `GoogleService-Info.plist` indir → Xcode'a ekle
6. Firebase Cloud Messaging API'yı aktifleştir

### Aşama 4: API Controller'ları Geliştirme (Backend)
Mobil API'ler için mevcut `app/Http/Controllers/Api/` dizinindeki controller'ları güncelle:
- FinanceController → tam implementasyon (avans + masraf)
- ShiftController → clock-in/clock-out implementasyonu
- Yeni controller'lar: ProfileController, AnnouncementController, MyLocationsController

### Aşama 5: BLoC + Data Layer Kod Üretimi (Mason ile)
```bash
# Mason şablonları oluştur
mason make bloc --name auth
mason make bloc --name attendance
mason make bloc --name leave
# ... tüm modüller için
```

### Aşama 6: Ekran Geliştirme Sırası
1. Splash Screen
2. Login Screen
3. Main Shell (Bottom Nav + Drawer)
4. Dashboard Screen
5. Giriş/Çıkış Screen (GPS + Fake GPS detection)
6. Puantaj Screen (Takvim)
7. Konumlarım Screen (Harita)
8. İzin Yönetimi (Liste + Oluştur + Detay)
9. Avans Yönetimi
10. Masraf Yönetimi
11. Vardiya Ekranı
12. Envanter & Zimmet
13. Seyahat Yönetimi
14. Araç Yönetimi
15. Ziyaretçi Yönetimi
16. Duyuru & Anketler
17. Bildirimler
18. Tatil Takvimi
19. Profil & Düzenleme
20. Şirket Bilgileri

### Aşama 7: Native Kod (Android - Kotlin)
```kotlin
// android/app/src/main/kotlin/.../MainActivity.kt
// Fake GPS detection plugin registration
// Biometric authentication support
// FCM background handler
```

### Aşama 8: Native Kod (iOS - Swift)
```swift
// ios/Runner/AppDelegate.swift
// Push notification delegation
// Location manager configuration
// Fake GPS detection
```

---

## 15. ÖNEMLİ NOTLAR

1. **Veri Güncelleme:** Kullanıcı sadece kendi profilini güncelleyebilir (ad, e-posta, telefon, şifre). Diğer tüm veriler salt okunur veya sadece talep oluşturma amaçlıdır.

2. **Talep Oluşturma:** Kullanıcılar izin, avans, masraf, seyahat vb. talepleri oluşturabilir. Onay/red işlemleri backend tarafında ilgili yetkili roller tarafından yapılır.

3. **Anlık Güncelleme:** Backend'de bir talep onaylandığında/reddedildiğinde Firebase Cloud Messaging üzerinden push notification gönderilir. Kullanıcı uygulamayı açmadan bildirim alır.

4. **Sahte Konum:** Her giriş/çıkış işleminde sahte konum kontrolü yapılır. Tespit edilirse işlem engellenir ve backend'e log gönderilir.

5. **İnternet Kontrolü:** Kritik işlemler öncesi internet bağlantısı kontrol edilir. Bağlantı yoksa kullanıcı uyarılır.

6. **Kapalı Uygulama Bildirimleri:** FCM sayesinde uygulama kapalıyken dahi push notification alınır. Bildirime tıklayınca uygulama açılır ve ilgili sayfaya yönlendirilir.

7. **Tema:** Backend admin paneli ile aynı tasarım dili kullanılır (Cyan brand, Slate arka plan, yuvarlak kartlar, gölgeler).

8. **Onboarding Yok:** Mobil uygulamada onboarding ekranı bulunmaz. Kullanıcı direkt login ekranı ile karşılaşır.

9. **Backend Özel Ekranlar:** İçerik Yönetimi (CMS), Lokasyon Yönetimi gibi backend'e özel sayfalar mobilde yer almaz.

10. **Dosya Yükleme:** Masraf fişleri, profil fotoğrafı gibi dosyalar `image_picker` ile seçilir, multipart/form-data ile API'ye gönderilir.

---

## 16. BACKEND'E EKLENECEK YENİ API CONTROLLER'LARI

### 16.1 ProfileController
```php
// app/Http/Controllers/Api/ProfileController.php
// PUT /api/v1/profile - Ad, e-posta, telefon güncelleme
// PUT /api/v1/profile/password - Şifre değiştirme
// POST /api/v1/profile/photo - Profil fotoğrafı yükleme
```

### 16.2 MyLocationController
```php
// app/Http/Controllers/Api/MyLocationController.php
// GET /api/v1/my-locations - Kullanıcıya atanmış lokasyonlar
// POST /api/v1/my-locations/check - Konum doğrulama
```

### 16.3 AnnouncementController
```php
// app/Http/Controllers/Api/AnnouncementController.php
// GET /api/v1/announcements - Duyuru listesi
// GET /api/v1/announcements/{id} - Duyuru detay
```

### 16.4 HolidayController (API)
```php
// app/Http/Controllers/Api/HolidayController.php
// GET /api/v1/holidays - Tatil listesi
```

### 16.5 CompanyInfoController (API)
```php
// app/Http/Controllers/Api/CompanyInfoController.php
// GET /api/v1/company/info - Şirket bilgileri
```

### 16.6 DeviceController
```php
// app/Http/Controllers/Api/DeviceController.php
// POST /api/v1/device/register - Firebase token kaydı
// POST /api/v1/device/unregister - Firebase token silme
// Tablo: firebase_tokens (id, user_id, token, device_type, created_at, updated_at)
```

### 16.7 SpecialHourController (API)
```php
// app/Http/Controllers/Api/SpecialHourController.php
// GET /api/v1/special-hours - Kullanıcının özel saat bilgisi
```

---

## 17. VERİTABANI (Yeni Tablolar)

### firebase_tokens
```sql
CREATE TABLE firebase_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token TEXT NOT NULL,
    device_type VARCHAR(20) NOT NULL, -- 'android' veya 'ios'
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## 18. XCODE / iOS YAPILANDIRMASI

### Info.plist Eklemeleri
- `FirebaseMessagingAutoInitEnabled` = NO (manuel init)
- `UIBackgroundModes` = fetch, remote-notification
- Push Notification Capability açık (Xcode → Signing & Capabilities)
- Background Modes → Remote notifications açık

### Podfile
```ruby
platform :ios, '13.0'

target 'Runner' do
  use_frameworks!
  use_modular_headers!
  
  flutter_install_all_ios_pods File.dirname(File.realpath(__FILE__))
  
  # Firebase
  pod 'Firebase', :modular_headers => true
  pod 'FirebaseMessaging', :modular_headers => true
end
```

---

## 19. ANDROID YAPILANDIRMASI

### build.gradle (app level)
```gradle
android {
    compileSdk 34
    
    defaultConfig {
        minSdk 24
        targetSdk 34
        multiDexEnabled true
    }
}

dependencies {
    implementation platform('com.google.firebase:firebase-bom:32.7.0')
    implementation 'com.google.firebase:firebase-messaging'
    implementation 'com.google.firebase:firebase-analytics'
    implementation 'androidx.biometric:biometric:1.2.0-alpha05'
}
```

### AndroidManifest.xml
```xml
<application>
    <!-- Firebase Cloud Messaging -->
    <service
        android:name="com.google.firebase.messaging.FirebaseMessagingService"
        android:exported="false">
        <intent-filter>
            <action android:name="com.google.firebase.MESSAGING_EVENT"/>
        </intent-filter>
    </service>
    
    <meta-data
        android:name="com.google.firebase.messaging.default_notification_channel_id"
        android:value="miysoft_notifications"/>
</application>
```

---

## 20. PROJE TESLİM & ÇALIŞTIRMA ADIMLARI

```bash
# 1. Projeyi oluştur
flutter create --org com.miysoft --project-name miysoft_pts_mobile .
cd miysoft_pts_mobile

# 2. Bağımlılıkları yükle
flutter pub get

# 3. Firebase yapılandırma dosyalarını ekle
# Android: google-services.json → android/app/
# iOS: GoogleService-Info.plist → ios/Runner/ (Xcode'a ekle)

# 4. Code generation (json_serializable, retrofit)
dart run build_runner build --delete-conflicting-outputs

# 5. Android için çalıştır
flutter run

# 6. iOS için çalıştır (Xcode gerekli)
flutter run -d ios

# 7. iOS Pod kurulumu (gerekirse)
cd ios && pod install && cd ..

# 8. Build alma
flutter build apk --release        # Android
flutter build ios --release         # iOS (Xcode üzerinden Archive)
```

---

> **Not:** Bu doküman, MİYSOFT PTS mobil uygulamasının tam mimarisini, ekranlarını, API yapısını ve geliştirme adımlarını içerir. OpenCode MacBook üzerinde bu dokümanı okuyarak sıfırdan Flutter projesini oluşturacak, gerekli tüm paketleri kuracak, servisleri, API'leri, BLoC yapılarını, ekranları ve native kodları (Kotlin/Swift) yazacak, hata durumlarında (Pod hataları, Xcode hataları) düzeltmeleri yapacaktır.
