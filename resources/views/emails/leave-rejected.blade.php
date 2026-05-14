<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<style>
body{font-family:Arial,sans-serif;background:#f5f5f5;margin:0;padding:20px}
.c{max-width:600px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08)}
.h{background:linear-gradient(135deg,#ef4444,#dc2626);padding:32px 40px;text-align:center}
.h h1{color:#fff;margin:0;font-size:24px;font-weight:800}
.b{padding:32px 40px}
.badge{display:inline-block;background:#fee2e2;color:#dc2626;padding:6px 16px;border-radius:99px;font-size:13px;font-weight:700;margin-bottom:20px}
.box{background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:20px;margin:20px 0}
.btn{display:block;text-align:center;background:#02E0FB;color:#0a0a0a;text-decoration:none;padding:14px 32px;border-radius:12px;font-weight:700;font-size:15px;margin:24px 0}
.foot{background:#f9fafb;padding:20px 40px;text-align:center;color:#9ca3af;font-size:12px;border-top:1px solid #f3f4f6}
</style>
</head>
<body>
<div class="c">
    <div class="h"><h1>❌ İzin Talebiniz Reddedildi</h1><p style="color:rgba(255,255,255,.8);margin:8px 0 0;font-size:14px">MİYSOFT PTS</p></div>
    <div class="b">
        <div class="badge">✕ REDDEDİLDİ</div>
        <p>Sayın <strong>{{ $personelName }}</strong>,</p>
        <p style="color:#6b7280;font-size:14px">{{ $leaveTypeName }} izin talebiniz reddedilmiştir.</p>
        <div class="box">
            <p style="margin:0;font-size:13px;color:#6b7280">Tarih Aralığı: <strong style="color:#111">{{ $startDate }} — {{ $endDate }}</strong></p>
            <p style="margin:12px 0 0;font-size:13px;color:#6b7280">Reddeden: <strong style="color:#111">{{ $rejectorName }}</strong></p>
            @if($reason)
            <p style="margin:12px 0 0;font-size:13px;color:#6b7280">Gerekçe: <strong style="color:#dc2626">{{ $reason }}</strong></p>
            @endif
        </div>
        <p style="font-size:13px;color:#6b7280">Yeni bir izin talebi oluşturmak veya itirazda bulunmak için sisteme giriş yapabilirsiniz.</p>
        <a href="{{ config('app.url') }}/admin/leave" class="btn">Sisteme Git →</a>
    </div>
    <div class="foot">&copy; {{ now()->year }} MİYSOFT Teknoloji — Otomatik e-posta.</div>
</div>
</body>
</html>
