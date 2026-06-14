<h3>📍 Lokasyon Yönetimi</h3>

<p>Lokasyon yönetimi modülü, şirketinize ait tüm fiziksel konumların harita üzerinde tanımlandığı ve yönetildiği modüldür. Her lokasyon için adres, koordinatlar, çalışma saatleri, personel atamaları ve QR kod ile yoklama gibi özellikler tanımlanabilir.</p>

<p>Sistem, <strong>Nominatim</strong> adres arama motoru ile adres sorgulama, <strong>Leaflet</strong> harita kütüphanesi ile interaktif harita gösterimi ve <strong>Street View</strong> ile lokasyonun sokak görünümünü sunar. Ayrıca her lokasyon için belirlenen yarıçap (radius) dahilinde QR kod ile yoklama yapılabilir.</p>

<div class="doc-flow">
    <div class="doc-flow-step">Adres Ara</div>
    <div class="doc-flow-arrow">→</div>
    <div class="doc-flow-step">Haritadan Seç</div>
    <div class="doc-flow-arrow">→</div>
    <div class="doc-flow-step">Kaydet</div>
    <div class="doc-flow-arrow">→</div>
    <div class="doc-flow-step">Personel Ata</div>
</div>

<h4>Personel Atama Tipleri</h4>

<table class="doc-table">
    <thead>
        <tr>
            <th>Tip</th>
            <th>Açıklama</th>
            <th>Kullanım</th>
        </tr>
    </thead>
    <tbody>
        <tr><td><code>in</code></td><td>Sadece Giriş</td><td>Personel sadece giriş yapabilir</td></tr>
        <tr><td><code>out</code></td><td>Sadece Çıkış</td><td>Personel sadece çıkış yapabilir</td></tr>
        <tr><td><code>inout</code></td><td>Giriş/Çıkış</td><td>Personel hem giriş hem çıkış yapabilir</td></tr>
        <tr><td><code>shift</code></td><td>Vardiya</td><td>Vardiyalı çalışma düzeni</td></tr>
        <tr><td><code>overtime</code></td><td>Fazla Mesai</td><td>Fazla mesai takibi</td></tr>
    </tbody>
</table>

<h4>Harita Özellikleri</h4>
<ul>
    <li><strong>Nominatim Arama:</strong> Adres yazarak konum bulma (ücretsiz, OpenStreetMap tabanlı)</li>
    <li><strong>Leaflet Harita:</strong> İnteraktif harita, tıklayarak veya sürükleyerek konum seçme</li>
    <li><strong>Street View:</strong> Google Maps Street View ile konumun sokak görünümü</li>
    <li><strong>Yarıçap:</strong> Lokasyon çevresinde QR yoklama için mesafe sınırı</li>
    <li><strong>Renk Kodu:</strong> Her lokasyon için özel renk tanımlama</li>
</ul>

<h4>Navigasyon</h4>
<p>Sol yan menü <strong>Lokasyon → Lokasyonlar</strong> bölümü altında yer alır.</p>

<h4>İlgili Yetkiler</h4>
<ul>
    <li><code>location.view</code> - Lokasyonları görüntüleme</li>
    <li><code>location.create</code> - Lokasyon oluşturma</li>
    <li><code>location.edit</code> - Lokasyon düzenleme</li>
    <li><code>location.delete</code> - Lokasyon silme</li>
    <li><code>location.assign</code> - Personel atama</li>
</ul>
