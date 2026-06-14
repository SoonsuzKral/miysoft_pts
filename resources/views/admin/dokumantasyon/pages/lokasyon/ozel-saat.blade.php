<h3>⏰ Özel Saat Modülü</h3>

<p>Özel saat modülü, standart çalışma saatleri dışında kalan veya düzensiz zaman dilimlerinde çalışan personellerin puantaj kayıtlarının otomatik olarak oluşturulmasını sağlar. Belirli bir tarih aralığında, belirli personeller için özel çalışma saatleri tanımlanabilir ve bu saatler puantaj cetveline otomatik işlenir.</p>

<p>Modül, şifre koruması ile güvenlik altına alınmıştır. Yalnızca yetkili kullanıcılar özel saat tanımlaması yapabilir. Personel arama, "Tamamı" seçeneği ile toplu giriş+çıkış kaydı, süresiz veya belirli tarih aralığı tanımlama gibi özellikler sunar. Departmana toplu ekleme ile tek seferde tüm departman personeline kayıt oluşturulabilir.</p>

<div class="doc-flow">
    <div class="doc-flow-step">Şifre Doğrulama</div>
    <div class="doc-flow-arrow">→</div>
    <div class="doc-flow-step">Kayıt Ekle</div>
    <div class="doc-flow-arrow">→</div>
    <div class="doc-flow-step">Personel Seçimi</div>
    <div class="doc-flow-arrow">→</div>
    <div class="doc-flow-step">Otomatik Puantaj</div>
</div>

<h4>Otomatik Puantaj Akışı</h4>

<div class="doc-diagram">
    <div class="doc-diagram-row">
        <div class="doc-diagram-box primary">attendance:auto<br>Komutu</div>
        <div class="doc-diagram-arrow">→</div>
        <div class="doc-diagram-box warning">Özel Saat<br>Kayıtlarını Oku</div>
        <div class="doc-diagram-arrow">→</div>
        <div class="doc-diagram-box success">Puantaj<br>Cetveline İşle</div>
    </div>
</div>

<h4>Özellikler</h4>
<ul>
    <li><strong>Şifre Koruması:</strong> Modüle erişim için şifre doğrulaması</li>
    <li><strong>Personel Arama:</strong> İsim veya departman ile personel arama</li>
    <li><strong>Tamamı Seçeneği:</strong> Tek seferde giriş + çıkış kaydı oluşturma</li>
    <li><strong>Tarih Aralığı:</strong> Başlangıç ve bitiş tarihi tanımlama</li>
    <li><strong>Süresiz:</strong> Bitiş tarihi olmadan sınırsız süreli kayıt</li>
    <li><strong>Toplu İşlem:</strong> Departmana toplu kayıt ekleme</li>
    <li><strong>Otomatik Puantaj:</strong> Her dakika çalışan komut ile puantaja otomatik işleme</li>
</ul>

<h4>Navigasyon</h4>
<p>Sol yan menü <strong>Lokasyon → Özel Saat</strong> bölümü altında yer alır.</p>

<h4>İlgili Yetkiler</h4>
<ul>
    <li><code>special-hour.view</code> - Özel saat sayfasını görüntüleme</li>
    <li><code>special-hour.manage</code> - Özel saat kaydı oluşturma/düzenleme/silme</li>
</ul>
