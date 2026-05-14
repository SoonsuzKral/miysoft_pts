# MİYSOFT PTS — Executive Summary

**Proje:** MİYSOFT PTS (Personel Takip Sistemi)
**Tarih:** 2026-03-15
**Versiyon:** 1.0

## Özet

MİYSOFT PTS, tek bir Laravel monolitik kod tabanında iki ana bölümden oluşan bir SaaS uygulamasıdır: (1) Kurumsal müşterileri hedefleyen, dönüşüm odaklı bir kişisel/tanıtım web sitesi ve (2) KOBİ'ler ile orta ölçekli şirketlere yönelik kapsamlı bir Personel Takip Sistemi (PTS) yönetim paneli. Sistem; personel, izin, puantaj, vardiya, envanter, avans, seyahat, araç, ziyaretçi ve daha 20+ modülü kapsayan, rol tabanlı erişim kontrolü (RBAC) ve çok kiracılı (`company_id` row-scoping) mimarisiyle inşa edilmiştir. Tüm yönetim paneli içerikleri admin ekranından düzenlenebilir olup frontend Blade + Tailwind CSS ile Ajax tabanlı, server-side paginated ve mobil uyumlu tasarıma sahiptir. Proje, spatie/laravel-permission paketi ile güçlendirilmiş, Redis queue/cache destekli ve S3 uyumlu dosya yönetimi ile üretime hazır bir altyapı sunmaktadır.

## Kapsam ve Hedef

- **Hedef Kitle:** KOBİ ve orta ölçekli şirketler, İK ve İdari Birimler
- **Teknoloji:** Laravel 12, Blade, Tailwind CSS, MySQL, Redis, Vite
- **Mimari:** Modüler monolitik (`app/Modules/...`), çok kiracılı (company_id scoping)
- **Güvenlik:** Spatie RBAC, PII şifreleme, audit log, GDPR uyum

## Kritik Başarı Kriterleri

1. Tüm modüllerde CRUD işlemlerinin Ajax ile sayfa yenilenmeden yapılabilmesi
2. Rol ve yetki yönetiminin modül bazında granüler çalışması
3. Büyük veri setlerinde (10.000+ personel) performanslı çalışma (pagination, indexing, background jobs)
4. Admin panelinden web sitesi içeriğinin tam olarak yönetilebilmesi
5. Audit log ile tüm kritik aksiyonların izlenebilirliği
