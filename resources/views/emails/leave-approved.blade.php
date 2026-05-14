<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body { font-family: 'Inter', Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
.container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,.08); }
.header { background: linear-gradient(135deg, #02E0FB, #00b8d9); padding: 32px 40px; text-align: center; }
.header h1 { color: #fff; margin: 0; font-size: 24px; font-weight: 800; }
.header p { color: rgba(255,255,255,.85); margin: 8px 0 0; font-size: 14px; }
.body { padding: 32px 40px; }
.badge { display: inline-block; background: #dcfce7; color: #16a34a; padding: 6px 16px; border-radius: 99px; font-size: 13px; font-weight: 700; margin-bottom: 20px; }
.info-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; margin: 20px 0; }
.info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f3f4f6; }
.info-row:last-child { border-bottom: none; }
.info-label { color: #6b7280; font-size: 13px; }
.info-value { color: #111827; font-size: 13px; font-weight: 600; }
.btn { display: block; text-align: center; background: #02E0FB; color: #0a0a0a; text-decoration: none; padding: 14px 32px; border-radius: 12px; font-weight: 700; font-size: 15px; margin: 24px 0; }
.footer { background: #f9fafb; padding: 20px 40px; text-align: center; color: #9ca3af; font-size: 12px; border-top: 1px solid #f3f4f6; }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>✅ İzin Talebiniz Onaylandı</h1>
        <p>MİYSOFT PTS — Personel Takip Sistemi</p>
    </div>
    <div class="body">
        <div class="badge">✓ ONAYLANDI</div>
        <p style="color:#374151;font-size:15px;">Sayın <strong>{{ $personelName }}</strong>,</p>
        <p style="color:#6b7280;font-size:14px;line-height:1.6;">İzin talebiniz onaylanmıştır. Detaylar aşağıda yer almaktadır.</p>

        <div class="info-box">
            <div class="info-row">
                <span class="info-label">İzin Türü</span>
                <span class="info-value">{{ $leaveTypeName }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Başlangıç Tarihi</span>
                <span class="info-value">{{ $startDate }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Bitiş Tarihi</span>
                <span class="info-value">{{ $endDate }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Toplam Gün</span>
                <span class="info-value">{{ $totalDays }} iş günü</span>
            </div>
            <div class="info-row">
                <span class="info-label">Onaylayan</span>
                <span class="info-value">{{ $approverName }}</span>
            </div>
            @if($note)
            <div class="info-row">
                <span class="info-label">Not</span>
                <span class="info-value">{{ $note }}</span>
            </div>
            @endif
        </div>

        <a href="{{ config('app.url') }}/admin/leave" class="btn">Sistemi Görüntüle →</a>

        <p style="color:#9ca3af;font-size:12px;margin-top:16px;">İzin sürenizin verimli geçmesini dileriz. Sorularınız için sistemden destek talebinde bulunabilirsiniz.</p>
    </div>
    <div class="footer">
        &copy; {{ now()->year }} MİYSOFT Teknoloji — Bu e-posta otomatik olarak gönderilmiştir.<br>
        <a href="{{ config('app.url') }}" style="color:#02E0FB;">{{ config('app.url') }}</a>
    </div>
</div>
</body>
</html>
