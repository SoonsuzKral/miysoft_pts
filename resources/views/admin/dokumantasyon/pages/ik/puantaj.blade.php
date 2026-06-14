<h3>⏱️ Puantaj (Devam Takibi)</h3>

<p>Puantaj modülü, personellerin günlük giriş ve çıkış saatlerinin kayıt altına alındığı devam takip sistemidir. Personeller sisteme giriş yaparken QR kod okutarak, manuel saat girerek veya otomatik devam takibi (geolokasyon tabanlı) ile puantaj kaydı oluşturabilir. Her kayıt anlık olarak sisteme işlenir ve personelin aylık puantaj cetvelinde görüntülenir.</p>

<p>Modül ayrıca fazla mesai takibi, geç kalma ve erken çıkış raporlaması sunar. Aylık puantaj cetveli PDF/Excel olarak dışa aktarılabilir. QR kod girişi, her personel için benzersiz QR kod üretir ve terminal cihazlarla entegre çalışabilir.</p>

<div class="doc-flow">
  <div class="flow-item">📸 QR Okut / Giriş</div>
  <div class="flow-arrow">→</div>
  <div class="flow-item">💾 Kayıt Oluşur</div>
  <div class="flow-arrow">→</div>
  <div class="flow-item">📋 Puantaj Cetveli</div>
  <div class="flow-arrow">→</div>
  <div class="flow-item">📊 Rapor</div>
</div>

<h4>QR Kod Giriş Akışı</h4>

<div class="doc-diagram">
  <div class="diagram-box">
    <div class="diagram-title">QR Kod ile Puantaj Girişi</div>
    <div class="diagram-row">
      <div class="diagram-item">📱 Mobil/Terminal <br><small>QR kod okutulur</small></div>
      <div class="diagram-arrow">→</div>
      <div class="diagram-item">✅ Doğrulama <br><small>Personel bulunur</small></div>
      <div class="diagram-arrow">→</div>
      <div class="diagram-item">📍 Konum Kontrolü <br><small>Lokasyon doğrulanır</small></div>
      <div class="diagram-arrow">→</div>
      <div class="diagram-item">💾 Kayıt <br><small>Giriş/Çıkış kaydedilir</small></div>
    </div>
  </div>
</div>

<h4>Özellikler</h4>
<ul>
  <li>QR kod ile hızlı giriş/çıkış</li>
  <li>Manuel puantaj girişi</li>
  <li>Otomatik devam takibi (konum bazlı)</li>
  <li>Fazla mesai hesaplama</li>
  <li>Aylık puantaj cetveli (PDF/Excel)</li>
  <li>Geç kalma ve erken çıkış raporları</li>
</ul>

<h4>İzinler</h4>
<p><code>puantaj.list</code>, <code>puantaj.create</code>, <code>puantaj.edit</code>, <code>puantaj.export</code>, <code>puantaj.qr</code> (QR okutma)</p>
