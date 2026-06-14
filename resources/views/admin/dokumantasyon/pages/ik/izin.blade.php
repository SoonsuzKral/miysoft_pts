<h3>🏖️ İzin Yönetimi</h3>

<p>İzin Yönetimi modülü, personellerin izin taleplerini oluşturduğu, yöneticiler ve İK departmanı tarafından onaylandığı çok aşamalı bir onay sistemidir. Personel kendi izin bakiyesini görüntüleyebilir, yıllık izin, hastalık izni, mazeret izni, ücretsiz izin gibi farklı izin türlerinde talep oluşturabilir. Talep oluşturulduktan sonra sırasıyla yönetici ve İK onayından geçer.</p>

<p>Modül ayrıca takvim görünümü sunar. Takvim üzerinden hangi personelin hangi tarihte izinli olduğunu toplu olarak görebilir, izin çakışmalarını önleyebilirsiniz. Her personelin kalan izin gün sayısı otomatik hesaplanır ve izin türüne göre ayrı ayrı takip edilir.</p>

<div class="doc-flow">
  <div class="flow-item">📝 Talep Oluştur</div>
  <div class="flow-arrow">→</div>
  <div class="flow-item">👔 Yönetici Onayı</div>
  <div class="flow-arrow">→</div>
  <div class="flow-item">🏢 İK Onayı</div>
  <div class="flow-arrow">→</div>
  <div class="flow-item">💾 Kayıt</div>
</div>

<h4>İzin Türleri</h4>

<table class="doc-table">
  <thead>
    <tr>
      <th>İzin Türü</th>
      <th>Varsayılan Gün</th>
      <th>Açıklama</th>
    </tr>
  </thead>
  <tbody>
    <tr><td>Yıllık İzin</td><td>14 gün</td><td>Kanuni yıllık ücretli izin</td></tr>
    <tr><td>Hastalık İzni</td><td>7 gün</td><td>Sağlık raporu ile kullanılır</td></tr>
    <tr><td>Mazeret İzni</td><td>3 gün</td><td>Evlilik, doğum, vefat gibi durumlar</td></tr>
    <tr><td>Ücretsiz İzin</td><td>Tanımlanmamış</td><td>Ücretsiz izin talebi</td></tr>
    <tr><td>Babaya İzin</td><td>5 gün</td><td>Doğum sonrası baba izni</td></tr>
    <tr><td>Doğum İzni</td><td>16 hafta</td><td>Analık doğum izni</td></tr>
  </tbody>
</table>

<h4>Özellikler</h4>
<ul>
  <li>Çok aşamalı onay süreci (Yönetici → İK)</li>
  <li>Anlık bakiye hesaplama</li>
  <li>Takvim görünümü</li>
  <li>İzin raporu ve istatistikler</li>
  <li>Reddetme durumunda bildirim ve açıklama</li>
</ul>

<h4>İzinler</h4>
<p><code>izin.list</code>, <code>izin.create</code>, <code>izin.approve</code> (onaylama), <code>izin.reject</code> (reddetme), <code>izin.view-balance</code> (bakiye görüntüleme)</p>
