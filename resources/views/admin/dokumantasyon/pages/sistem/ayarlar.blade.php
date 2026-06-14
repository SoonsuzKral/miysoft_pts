<h3>⚙️ Sistem Ayarları</h3>

<p>Sistem Ayarları modülü, tüm sistemin yapılandırma ayarlarının yapıldığı merkezi bölümdür. Genel ayarlar (şirket adı, logo, saat dilimi, dil, para birimi), e-posta ayarları (SMTP yapılandırması, bildirim şablonları), güvenlik ayarları (şifre politikası, 2FA, oturum süresi) ve sistem tercihleri bu modülden yönetilir.</p>

<p>E-posta ayarları kısmında SMTP sunucu bilgileri, TLS/SSL seçenekleri, gönderici adresi ve test e-postası gönderme özelliği bulunur. Güvenlik ayarlarında minimum şifre uzunluğu, büyük/küçük harf ve özel karakter zorunluluğu, başarısız giriş denemesi sınırı gibi politikalar belirlenebilir.</p>

<div class="doc-flow">
  <div class="flow-item">⚙️ Ayar Seç</div>
  <div class="flow-arrow">→</div>
  <div class="flow-item">✏️ Değer Gir</div>
  <div class="flow-arrow">→</div>
  <div class="flow-item">💾 Kaydet</div>
  <div class="flow-arrow">→</div>
  <div class="flow-item">🔄 Uygula</div>
</div>

<h4>Ayar Kategorileri</h4>

<table class="doc-table">
  <thead>
    <tr>
      <th>Kategori</th>
      <th>Açıklama</th>
    </tr>
  </thead>
  <tbody>
    <tr><td>Genel Ayarlar</td><td>Şirket adı, logo, saat dilimi, dil, para birimi</td></tr>
    <tr><td>E-posta Ayarları</td><td>SMTP yapılandırması, TLS/SSL, gönderici adresi</td></tr>
    <tr><td>Güvenlik Ayarları</td><td>Şifre politikası, 2FA, oturum süresi, giriş denemesi</td></tr>
    <tr><td>Bildirim Ayarları</td><td>E-posta bildirimleri, sistem bildirimleri, şablonlar</td></tr>
    <tr><td>Entegrasyon Ayarları</td><td>API anahtarları, webhook URL'leri, üçüncü taraf entegrasyonlar</td></tr>
  </tbody>
</table>

<h4>Özellikler</h4>
<ul>
  <li>Merkezi sistem yapılandırması</li>
  <li>SMTP test e-postası gönderme</li>
  <li>Şifre politikası ve güvenlik kuralları</li>
  <li>Bildirim şablonu düzenleme</li>
  <li>Çoklu dil ve saat dilimi desteği</li>
</ul>

<h4>İzinler</h4>
<p><code>ayarlar.view</code>, <code>ayarlar.edit</code>, <code>ayarlar.email</code> (e-posta ayarları), <code>ayarlar.security</code> (güvenlik ayarları)</p>
