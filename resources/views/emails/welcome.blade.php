<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<style>
body{font-family:Arial,sans-serif;background:#f5f5f5;margin:0;padding:20px}
.c{max-width:600px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08)}
.h{background:linear-gradient(135deg,#0a0a1a,#0d1b2a);padding:40px;text-align:center}
.logo{width:48px;height:48px;background:linear-gradient(135deg,#02E0FB,#00b8d9);border-radius:14px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px}
.h h1{color:#fff;margin:0;font-size:28px;font-weight:900}
.h h1 span{color:#02E0FB}
.b{padding:40px}
.features{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:24px 0}
.feat{background:#f9fafb;border-radius:12px;padding:16px;border:1px solid #e5e7eb}
.feat-icon{font-size:24px;margin-bottom:8px}
.feat-title{font-size:13px;font-weight:700;color:#111}
.feat-desc{font-size:12px;color:#6b7280;margin-top:4px}
.btn{display:block;text-align:center;background:linear-gradient(135deg,#02E0FB,#00b8d9);color:#0a0a0a;text-decoration:none;padding:16px 32px;border-radius:14px;font-weight:900;font-size:16px;margin:28px 0}
.trial{background:linear-gradient(135deg,#FA6001,#e05500);color:#fff;border-radius:12px;padding:16px 20px;text-align:center;margin:24px 0}
.foot{background:#f9fafb;padding:24px 40px;text-align:center;color:#9ca3af;font-size:12px;border-top:1px solid #f3f4f6}
</style>
</head>
<body>
<div class="c">
    <div class="h">
        <div class="logo" style="display:inline-flex;"><span style="color:#0a0a1a;font-weight:900;font-size:20px">M</span></div>
        <h1>MİYSOFT <span>PTS</span></h1>
        <p style="color:rgba(255,255,255,.7);margin:8px 0 0;font-size:15px">🎉 Sisteme hoş geldiniz!</p>
    </div>
    <div class="b">
        <p style="font-size:16px;font-weight:700;color:#111">Merhaba {{ $name }},</p>
        <p style="color:#6b7280;font-size:14px;line-height:1.7"><strong>{{ $companyName }}</strong> adına MİYSOFT PTS'ye kaydınız tamamlandı. 14 günlük ücretsiz deneme süreniz başlamıştır!</p>

        <div class="trial">
            <p style="margin:0;font-size:15px;font-weight:800">⏰ Deneme Süreniz: 14 Gün</p>
            <p style="margin:6px 0 0;font-size:13px;opacity:.9">Bitiş tarihi: {{ $trialEndsAt }}</p>
        </div>

        <p style="font-size:14px;font-weight:700;color:#111;margin-bottom:16px">Neler yapabilirsiniz?</p>
        <div class="features">
            @foreach([['👤','Personel Yönetimi','Departman, pozisyon, evrak'],['📅','İzin Yönetimi','Onay akışı, bakiye takibi'],['⏰','Puantaj','Giriş/çıkış, fazla mesai'],['📦','Envanter','Zimmet, garanti takibi'],['💰','Finans','Avans ve masraf yönetimi'],['📊','Raporlar','Excel ve PDF export']] as [$icon,$title,$desc])
            <div class="feat">
                <div class="feat-icon">{{ $icon }}</div>
                <div class="feat-title">{{ $title }}</div>
                <div class="feat-desc">{{ $desc }}</div>
            </div>
            @endforeach
        </div>

        <a href="{{ $loginUrl }}" class="btn">🚀 Panele Gir ve Başla</a>

        <p style="font-size:12px;color:#9ca3af;text-align:center">Sorun yaşarsanız destek@miysoft.com.tr adresinden bize ulaşın.</p>
    </div>
    <div class="foot">
        &copy; {{ now()->year }} MİYSOFT Teknoloji A.Ş. · <a href="{{ config('app.url') }}" style="color:#02E0FB">miysoft.com.tr</a>
    </div>
</div>
</body>
</html>
