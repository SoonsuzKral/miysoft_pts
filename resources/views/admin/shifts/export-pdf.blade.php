<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vardiyalar</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f5f5f5; }
        h1 { font-size: 18px; color: #333; }
    </style>
</head>
<body>
    <h1>Vardiya Listesi</h1>
    <p>{{ now()->format('d.m.Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Vardiya Adı</th>
                <th>Saat</th>
                <th>Süre</th>
                <th>Gece</th>
                <th>Durum</th>
                <th>Atama Sayısı</th>
            </tr>
        </thead>
        <tbody>
            @foreach($shifts as $s)
            <tr>
                <td>{{ $s->name }}</td>
                <td>{{ $s->start_time }} - {{ $s->end_time }}</td>
                <td>{{ $s->duration_label }}</td>
                <td>{{ $s->is_night_shift ? 'Evet' : 'Hayır' }}</td>
                <td>{{ $s->is_active ? 'Aktif' : 'Pasif' }}</td>
                <td>{{ $s->assignments_count }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
