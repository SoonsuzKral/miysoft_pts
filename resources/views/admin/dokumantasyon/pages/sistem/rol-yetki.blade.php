<h3>🔐 Rol & Yetki Yönetimi</h3>

<p>Rol ve yetki yönetimi modülü, sistemdeki kullanıcıların erişim ve işlem yetkilerinin tanımlandığı en kritik modüllerden biridir. Bu modül sayesinde roller oluşturulur, her role özel yetkiler atanır ve kullanıcılar bu rollere atanarak yetkilendirilir.</p>

<p>Sistem <strong>Rol Tabanlı Yetkilendirme (RBAC)</strong> modelini kullanır. Her kullanıcı bir veya daha fazla role sahip olabilir. Yetkiler modül bazında görüntüleme, oluşturma, düzenleme ve silme şeklinde detaylı olarak tanımlanabilir.</p>

<div class="doc-flow">
    <div class="doc-flow-step">Rol Oluştur</div>
    <div class="doc-flow-arrow">→</div>
    <div class="doc-flow-step">Yetki Ata</div>
    <div class="doc-flow-arrow">→</div>
    <div class="doc-flow-step">Kullanıcı Ata</div>
    <div class="doc-flow-arrow">→</div>
    <div class="doc-flow-step">Uygula</div>
</div>

<h4>Yetki Hiyerarşisi</h4>

<div class="doc-diagram">
    <div class="doc-diagram-row">
        <div class="doc-diagram-box primary">Süper Admin<br>Tüm Yetkiler</div>
    </div>
    <div class="doc-diagram-arrow" style="transform:rotate(90deg)">↓</div>
    <div class="doc-diagram-row">
        <div class="doc-diagram-box warning">Yönetici<br>Yönetim Yetkileri</div>
        <div class="doc-diagram-box warning">İK Yöneticisi<br>Personel + İzin</div>
    </div>
    <div class="doc-diagram-arrow" style="transform:rotate(90deg)">↓</div>
    <div class="doc-diagram-row">
        <div class="doc-diagram-box">Departman Müdürü<br>Departman Yetkileri</div>
    </div>
    <div class="doc-diagram-arrow" style="transform:rotate(90deg)">↓</div>
    <div class="doc-diagram-row">
        <div class="doc-diagram-box success">Personel<br>Temel Yetkiler</div>
    </div>
</div>

<h4>Varsayılan Roller</h4>

<table class="doc-table">
    <thead>
        <tr>
            <th>Rol</th>
            <th>Açıklama</th>
            <th>Kapsam</th>
        </tr>
    </thead>
    <tbody>
        <tr><td><code>admin</code></td><td>Süper Admin</td><td>Tüm sistem tam yetki</td></tr>
        <tr><td><code>companyAdmin</code></td><td>Şirket Admini</td><td>Kendi şirketi için tam yetki</td></tr>
        <tr><td><code>hr_manager</code></td><td>İK Yöneticisi</td><td>Personel, izin, puantaj yönetimi</td></tr>
        <tr><td><code>manager</code></td><td>Departman Müdürü</td><td>Kendi departmanı için sınırlı yetki</td></tr>
        <tr><td><code>employee</code></td><td>Personel</td><td>Sadece kendi bilgilerini görme</td></tr>
    </tbody>
</table>

<h4>Navigasyon</h4>
<p>Sol yan menü <strong>Sistem → Rol & Yetki</strong> bölümü altında yer alır.</p>

<h4>İlgili Yetkiler</h4>
<ul>
    <li><code>roles.view</code> - Rolleri görüntüleme</li>
    <li><code>roles.create</code> - Rol oluşturma</li>
    <li><code>roles.edit</code> - Rol düzenleme</li>
    <li><code>roles.delete</code> - Rol silme</li>
    <li><code>users.manage</code> - Kullanıcı yönetimi</li>
</ul>
