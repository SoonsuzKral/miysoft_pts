<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Zimmet Belgesi</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #02E0FB; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { color: #222; font-size: 20px; margin: 0 0 5px; }
        .header p { color: #666; font-size: 11px; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 8px 10px; text-align: left; border: 1px solid #ddd; }
        th { background: #f5f5f5; font-weight: 600; width: 30%; }
        td { width: 70%; }
        .footer { margin-top: 40px; border-top: 1px solid #ddd; padding-top: 15px; font-size: 10px; color: #999; }
        .signatures { margin-top: 50px; display: flex; justify-content: space-between; }
        .signature-box { text-align: center; width: 40%; }
        .signature-line { border-top: 1px solid #333; margin-top: 50px; padding-top: 8px; font-size: 11px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 10px; font-weight: bold; }
        .badge-active { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="header">
        <h1>ZİMMET BELGESİ</h1>
        <p>MİYSOFT Personel Takip Sistemi | {{ now()->format('d.m.Y') }}</p>
    </div>

    <table>
        <tr><th>Zimmet No</th><td>ZIM-{{ str_pad($assignment->id, 6, '0', STR_PAD_LEFT) }}</td></tr>
        <tr><th>Durum</th><td><span class="badge badge-active">{{ $assignment->isActive() ? 'AKTİF' : 'İADE EDİLDİ' }}</span></td></tr>
    </table>

    <h3 style="margin-top: 20px;">Personel Bilgileri</h3>
    <table>
        <tr><th>Ad Soyad</th><td>{{ $assignment->personel->first_name }} {{ $assignment->personel->last_name }}</td></tr>
        <tr><th>E-posta</th><td>{{ $assignment->personel->email ?? '—' }}</td></tr>
        <tr><th>Departman</th><td>{{ $assignment->personel->department->name ?? '—' }}</td></tr>
    </table>

    <h3 style="margin-top: 20px;">Zimmetlenen Varlık</h3>
    <table>
        <tr><th>Varlık Adı</th><td>{{ $assignment->asset->name ?? '—' }}</td></tr>
        <tr><th>Marka / Model</th><td>{{ $assignment->asset->brand ?? '' }} {{ $assignment->asset->model ?? '' }}</td></tr>
        <tr><th>Seri No</th><td>{{ $assignment->asset->serial_number ?? '—' }}</td></tr>
        <tr><th>Tür</th><td>{{ $assignment->asset->type->name ?? '—' }}</td></tr>
        <tr><th>Durum</th><td>{{ $assignment->asset->status ?? '—' }}</td></tr>
    </table>

    <h3 style="margin-top: 20px;">Zimmet Detayı</h3>
    <table>
        <tr><th>Zimmet Tarihi</th><td>{{ $assignment->assigned_at->format('d.m.Y') }}</td></tr>
        @if($assignment->returned_at)
        <tr><th>İade Tarihi</th><td>{{ $assignment->returned_at->format('d.m.Y') }}</td></tr>
        @endif
        <tr><th>Zimmet Süresi</th><td>{{ $assignment->days_assigned }} gün</td></tr>
        <tr><th>Teslim Eden</th><td>{{ $assignment->assignedBy->name ?? '—' }}</td></tr>
        <tr><th>Notlar</th><td>{{ $assignment->notes ?? '—' }}</td></tr>
    </table>

    <div class="signatures">
        <div class="signature-box">
            <div class="signature-line">Personel İmzası</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">Yetkili İmzası</div>
        </div>
    </div>

    <div class="footer">
        <p>Bu belge MİYSOFT PTS tarafından otomatik oluşturulmuştur. Zimmet No: ZIM-{{ str_pad($assignment->id, 6, '0', STR_PAD_LEFT) }} | Tarih: {{ now()->format('d.m.Y H:i') }}</p>
    </div>
</body>
</html>
