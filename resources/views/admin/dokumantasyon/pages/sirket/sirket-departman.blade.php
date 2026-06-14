<h3>🏢 Şirket & Departman Yönetimi</h3>

<p>Şirket & Departman Yönetimi modülü, şirket profili bilgilerinin düzenlendiği ve departman hiyerarşisinin oluşturulduğu merkezi yönetim ekranıdır. Şirket bilgileri (vergi numarası, MERSİS, ticaret sicil, adres, logo) bu modülde tanımlanır. Departmanlar hiyerarşik olarak oluşturulabilir, her departmana bir yönetici atanabilir.</p>

<p>Alt departman desteği sayesinde şirket organizasyon şeması birebir yansıtılabilir. Departman bazında yetkilendirme sayesinde kullanıcılar yalnızca kendi departmanlarındaki personeller üzerinde işlem yapabilir. Departman silindiğinde alt departmanlar ve bağlı personeller korunur.</p>

<div class="doc-flow">
  <div class="flow-item">🏛️ Şirket Bilgileri</div>
  <div class="flow-arrow">→</div>
  <div class="flow-item">➕ Departman Ekle</div>
  <div class="flow-arrow">→</div>
  <div class="flow-item">🔽 Alt Departman</div>
  <div class="flow-arrow">→</div>
  <div class="flow-item">👔 Yönetici Ata</div>
</div>

<h4>Hiyerarşi Yapısı</h4>

<div class="doc-diagram">
  <div class="diagram-box">
    <div class="diagram-title">Departman Hiyerarşisi</div>
    <div class="diagram-row">
      <div class="diagram-item level-1">🏛️ Genel Müdürlük</div>
    </div>
    <div class="diagram-row">
      <div class="diagram-item level-2">📊 Finans</div>
      <div class="diagram-item level-2">👥 İnsan Kaynakları</div>
      <div class="diagram-item level-2">💻 Teknoloji</div>
    </div>
    <div class="diagram-row">
      <div class="diagram-item level-3">  ├ Muhasebe</div>
      <div class="diagram-item level-3">  ├ Bütçe</div>
      <div class="diagram-item level-3">  ├ İşe Alım</div>
      <div class="diagram-item level-3">  ├ Eğitim</div>
      <div class="diagram-item level-3">  ├ Yazılım</div>
      <div class="diagram-item level-3">  └ Altyapı</div>
    </div>
  </div>
</div>

<h4>Özellikler</h4>
<ul>
  <li>Şirket profili düzenleme (logo, iletişim, vergi bilgileri)</li>
  <li>Hiyerarşik departman yapısı (sınırsız alt departman)</li>
  <li>Departman yöneticisi atama</li>
  <li>Departman bazında yetkilendirme</li>
  <li>Organizasyon şeması görünümü</li>
</ul>

<h4>İzinler</h4>
<p><code>sirket.edit</code> (şirket düzenleme), <code>departman.list</code>, <code>departman.create</code>, <code>departman.edit</code>, <code>departman.delete</code></p>
