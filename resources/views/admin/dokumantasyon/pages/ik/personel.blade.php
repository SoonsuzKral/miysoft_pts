<h3>👤 Personel Yönetimi</h3>

<p>Personel yönetimi modülü, şirketinizdeki tüm personel kayıtlarının yönetildiği ana modüldür. Personel kartı oluşturma, düzenleme, silme, departman atama, kimlik bilgileri, iletişim bilgileri, adres, acil durum kişisi, banka hesabı, eğitim bilgileri ve özlük dosyası gibi tüm işlemler bu modül üzerinden yapılır.</p>

<p>Personel listesi, isim, departman, sicil numarası gibi kriterlere göre filtrelenebilir ve aranabilir. Ayrıca Excel formatında toplu içe/dışa aktarma imkanı sunar. Her personelin izin geçmişi, puantaj kayıtları ve zimmet bilgileri personel kartı üzerinden görüntülenebilir.</p>

<div class="doc-flow">
    <div class="doc-flow-step">Personel Ekle</div>
    <div class="doc-flow-arrow">→</div>
    <div class="doc-flow-step">Kart Oluştur</div>
    <div class="doc-flow-arrow">→</div>
    <div class="doc-flow-step">Departman Ata</div>
    <div class="doc-flow-arrow">→</div>
    <div class="doc-flow-step">İzin/Puantaj</div>
</div>

<h4>Personel Kartı Bölümleri</h4>

<table class="doc-table">
    <thead>
        <tr>
            <th>Bölüm</th>
            <th>İçerik</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>Kimlik Bilgileri</td><td>Ad, soyad, TC kimlik, doğum tarihi, cinsiyet</td></tr>
        <tr><td>İletişim</td><td>E-posta, telefon, acil durum kişisi</td></tr>
        <tr><td>Adres</td><td>İkametgah adresi, şehir, ilçe</td></tr>
        <tr><td>Departman</td><td>Departman, pozisyon, yönetici</td></tr>
        <tr><td>Banka</td><td>Banka adı, IBAN, hesap no</td></tr>
        <tr><td>Eğitim</td><td>Okul, bölüm, mezuniyet yılı</td></tr>
        <tr><td>Özlük</td><td>İşe giriş tarihi, sözleşme türü, SGK bilgileri</td></tr>
    </tbody>
</table>

<h4>Özellikler</h4>
<ul>
    <li>Personel ekleme, düzenleme, silme (CRUD)</li>
    <li>Excel ile toplu personel içe/dışa aktarma</li>
    <li>Departman bazlı filtreleme ve arama</li>
    <li>Personel kartında izin ve puantaj geçmişi görüntüleme</li>
    <li>Toplu işlemler (departman değiştirme, aktif/pasif yapma)</li>
</ul>

<h4>Navigasyon</h4>
<p>Sol yan menü <strong>İnsan Kaynakları → Personel</strong> bölümü altında yer alır. <code>admin.personel.index</code> rotası ile erişilir.</p>

<h4>İlgili Yetkiler</h4>
<ul>
    <li><code>personel.view</code> - Personelleri görüntüleme</li>
    <li><code>personel.create</code> - Personel oluşturma</li>
    <li><code>personel.edit</code> - Personel düzenleme</li>
    <li><code>personel.delete</code> - Personel silme</li>
</ul>
