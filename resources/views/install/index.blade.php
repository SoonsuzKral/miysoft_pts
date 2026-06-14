<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kurulum — MİYSOFT PTS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f4f8; color: #1e293b; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem 1rem; }
        .container { width: 100%; max-width: 680px; }
        .card { background: #fff; border-radius: 1.5rem; box-shadow: 0 4px 24px rgba(0,0,0,.06); padding: 2rem 2.5rem; }
        .logo { text-align: center; margin-bottom: 1.5rem; }
        .logo h1 { font-size: 1.5rem; font-weight: 700; background: linear-gradient(135deg, #02E0FB, #0284c7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .logo p { font-size: .875rem; color: #64748b; margin-top: .25rem; }
        h2 { font-size: 1.125rem; font-weight: 600; margin-bottom: .5rem; }
        p.desc { font-size: .875rem; color: #64748b; margin-bottom: 1.5rem; }
        .req-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; margin-bottom: 1.5rem; }
        .req-item { display: flex; align-items: center; gap: .5rem; font-size: .8125rem; padding: .5rem .75rem; border-radius: .75rem; background: #f8fafc; }
        .req-item .check { width: 1.25rem; height: 1.25rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .75rem; flex-shrink: 0; }
        .req-item .check.ok { background: #dcfce7; color: #16a34a; }
        .req-item .check.fail { background: #fef2f2; color: #dc2626; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; font-size: .8125rem; font-weight: 500; color: #374151; margin-bottom: .375rem; }
        input, select { width: 100%; padding: .625rem .875rem; font-size: .875rem; border: 1px solid #d1d5db; border-radius: .75rem; outline: none; transition: border .15s, box-shadow .15s; font-family: inherit; }
        input:focus, select:focus { border-color: #02E0FB; box-shadow: 0 0 0 3px rgba(2,224,251,.15); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; width: 100%; padding: .75rem 1.5rem; font-size: .9375rem; font-weight: 600; color: #fff; background: linear-gradient(135deg, #02E0FB, #0284c7); border: none; border-radius: .75rem; cursor: pointer; transition: opacity .15s; }
        .btn:hover { opacity: .9; }
        .btn:disabled { opacity: .5; cursor: not-allowed; }
        .btn-loading { position: relative; }
        .spinner { width: 1.25rem; height: 1.25rem; border: 2px solid rgba(255,255,255,.3); border-top-color: #fff; border-radius: 50%; animation: spin .6s linear infinite; display: none; }
        .btn-loading .spinner { display: inline-block; }
        .btn-loading .btn-text { opacity: .7; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .error-msg { background: #fef2f2; color: #dc2626; font-size: .8125rem; padding: .75rem 1rem; border-radius: .75rem; margin-bottom: 1rem; display: none; }
        .success-msg { background: #f0fdf4; color: #16a34a; font-size: .8125rem; padding: .75rem 1rem; border-radius: .75rem; margin-bottom: 1rem; display: none; }
        .section-title { font-size: .8125rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: #94a3b8; margin: 1.25rem 0 .75rem; }
        hr { border: none; border-top: 1px solid #f1f5f9; margin: 1.25rem 0; }
        .step-indicator { display: flex; justify-content: center; gap: .5rem; margin-bottom: 1.5rem; }
        .step { width: 2rem; height: .25rem; border-radius: 999px; background: #e2e8f0; }
        .step.active { background: #02E0FB; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo">
                <h1>MİYSOFT PTS</h1>
                <p>Personel Takip Sistemi — Kurulum</p>
            </div>

            <div class="step-indicator">
                <div class="step active"></div>
                <div class="step"></div>
                <div class="step"></div>
            </div>

            <div id="requirements">
                <h2>Sistem Gereksinimleri</h2>
                <p class="desc">Aşağıdaki gereksinimlerin sağlandığından emin olun.</p>
                <div class="req-grid">
                    @foreach($requirements as $key => $ok)
                        @php $optional = str_contains($key, '(opsiyonel)'); @endphp
                        <div class="req-item">
                            <div class="check {{ $ok ? 'ok' : 'fail' }}">{{ $ok ? '✓' : '✗' }}</div>
                            <span>{{ ucfirst(str_replace(' (opsiyonel)', '', $key)) }}
                                @if(!$ok && $optional)
                                    <span style="color:#a16207;font-size:.6875rem;margin-left:.25rem">(opsiyonel)</span>
                                @elseif(!$ok)
                                    <span style="color:#dc2626;font-size:.6875rem;margin-left:.25rem">(gerekli)</span>
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>
                @php
                    $missingRequired = collect($requirements)->filter(fn($ok, $key) => !$ok && !str_contains($key, '(opsiyonel)'))->isNotEmpty();
                @endphp
                @if($missingRequired)
                    <p style="color:#dc2626;font-size:.8125rem;margin-bottom:1rem">Lütfen eksik gereksinimleri yükleyin ve sayfayı yenileyin.</p>
                @endif
            </div>

            <form id="installForm" onsubmit="return install(this)">
                @php
                    $canInstall = collect($requirements)->filter(fn($ok, $key) => !$ok && !str_contains($key, '(opsiyonel)'))->isEmpty();
                @endphp
                <div id="formSection" style="{{ $canInstall ? '' : 'display:none' }}">
                    <div class="section-title">Uygulama Ayarları</div>
                    <div class="form-group">
                        <label for="app_name">Uygulama Adı</label>
                        <input type="text" id="app_name" name="app_name" value="MİYSOFT PTS" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="app_url">Site Adresi (URL)</label>
                            <input type="url" id="app_url" name="app_url" value="http://localhost" required>
                        </div>
                        <div class="form-group">
                            <label for="admin_email">Admin E-posta</label>
                            <input type="email" id="admin_email" name="admin_email" placeholder="admin@miysoft.com" required>
                        </div>
                    </div>

                    <hr>

                    <div class="section-title">Veritabanı Ayarları (MySQL)</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="db_host">Sunucu</label>
                            <input type="text" id="db_host" name="db_host" value="127.0.0.1" required>
                        </div>
                        <div class="form-group">
                            <label for="db_port">Port</label>
                            <input type="number" id="db_port" name="db_port" value="3306" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="db_database">Veritabanı Adı</label>
                        <input type="text" id="db_database" name="db_database" value="miysoft_pts" placeholder="miysoft_pts" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="db_username">Kullanıcı Adı</label>
                            <input type="text" id="db_username" name="db_username" value="root" required>
                        </div>
                        <div class="form-group">
                            <label for="db_password">Şifre</label>
                            <input type="text" id="db_password" name="db_password" placeholder="boş bırakılabilir">
                        </div>
                    </div>

                    <hr>

                    <div class="section-title">Yönetici Hesabı</div>
                    <div class="form-group">
                        <label for="admin_password">Yönetici Şifresi</label>
                        <input type="text" id="admin_password" name="admin_password" placeholder="en az 6 karakter" required>
                    </div>

                    <div id="errorMsg" class="error-msg"></div>
                    <div id="successMsg" class="success-msg"></div>

                    <button type="submit" class="btn" id="installBtn">
                        <span class="spinner"></span>
                        <span class="btn-text">Kurulumu Başlat</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function install(form) {
        const btn = document.getElementById('installBtn');
        const err = document.getElementById('errorMsg');
        const suc = document.getElementById('successMsg');

        err.style.display = 'none';
        suc.style.display = 'none';
        btn.classList.add('btn-loading');
        btn.disabled = true;

        const data = new FormData(form);
        fetch('{{ route("install.store") }}', {
            method: 'POST',
            body: data,
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                suc.textContent = res.message;
                suc.style.display = 'block';
                btn.classList.remove('btn-loading');
                btn.disabled = false;
                setTimeout(function() {
                    window.location.href = res.redirect || '/login';
                }, 2000);
            } else {
                err.textContent = res.message;
                err.style.display = 'block';
                btn.classList.remove('btn-loading');
                btn.disabled = false;
            }
        })
        .catch(function() {
            err.textContent = 'Kurulum sırasında bir hata oluştu.';
            err.style.display = 'block';
            btn.classList.remove('btn-loading');
            btn.disabled = false;
        });

        return false;
    }
    </script>
</body>
</html>
