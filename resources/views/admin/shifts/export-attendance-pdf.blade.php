<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vardiya Yoklama</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 5px 7px; text-align: left; }
        th { background: #f5f5f5; font-size: 10px; }
        h1 { font-size: 16px; color: #333; }
    </style>
</head>
<body>
    <h1>Vardiya Yoklama Kayıtları</h1>
    <p>{{ now()->format('d.m.Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Tarih</th>
                <th>Personel</th>
                <th>Departman</th>
                <th>Vardiya</th>
                <th>Giriş</th>
                <th>Çıkış</th>
                <th>Süre</th>
                <th>Geç</th>
                <th>Erken</th>
                <th>Durum</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $r)
            <tr>
                <td>{{ $r->date?->toDateString() }}</td>
                <td>{{ $r->personel?->first_name }} {{ $r->personel?->last_name }}</td>
                <td>{{ $r->personel?->department?->name ?? '-' }}</td>
                <td>{{ $r->shift?->name ?? '-' }}</td>
                <td>{{ $r->clock_in?->format('H:i') ?? '-' }}</td>
                <td>{{ $r->clock_out?->format('H:i') ?? '-' }}</td>
                <td>{{ $r->duration_minutes > 0 ? intdiv($r->duration_minutes, 60) . 's ' . ($r->duration_minutes % 60) . 'dk' : '-' }}</td>
                <td>{{ $r->late_minutes > 0 ? $r->late_minutes . ' dk' : '-' }}</td>
                <td>{{ $r->early_leave_minutes > 0 ? $r->early_leave_minutes . ' dk' : '-' }}</td>
                <td>{{ $r->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if($records->isEmpty())
        <p style="text-align:center;color:#999;margin-top:40px;">Kayıt bulunamadı.</p>
    @endif
</body>
</html>
