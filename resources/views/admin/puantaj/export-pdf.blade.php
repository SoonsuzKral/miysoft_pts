<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Puantaj Raporu</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; margin: 15px; }
        h2 { text-align: center; margin-bottom: 5px; color: #333; font-size: 16px; }
        .subtitle { text-align: center; font-size: 10px; color: #666; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #02E0FB; color: #fff; padding: 5px 6px; text-align: left; font-size: 8px; text-transform: uppercase; }
        td { padding: 4px 6px; border-bottom: 1px solid #e0e0e0; font-size: 8px; }
        tr:nth-child(even) td { background: #f9f9f9; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer { text-align: center; margin-top: 10px; font-size: 8px; color: #999; }
        .text-emerald { color: #059669; }
        .text-red { color: #dc2626; }
        .text-blue { color: #2563eb; }
        .text-amber { color: #d97706; }
    </style>
</head>
<body>
    <h2>Puantaj Raporu</h2>
    <p class="subtitle">{{ $monthName }} · Tüm Personel</p>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Personel</th>
                <th class="text-center">Çalışma Günü</th>
                <th class="text-center">Toplam Saat</th>
                <th class="text-center">Ort. Saat</th>
                <th class="text-center">Fazla Mesai</th>
                <th class="text-center">Gecikme (dk)</th>
                <th class="text-center">Devamsızlık</th>
                <th class="text-center">İzin</th>
                <th class="text-center">Verim %</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summaries as $i => $s)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $s['personel_name'] ?? '—' }}</td>
                <td class="text-center">{{ $s['present_days'] ?? 0 }}/{{ $s['working_days_in_month'] ?? 0 }}</td>
                <td class="text-center">{{ $s['total_work_hours'] ?? 0 }}</td>
                <td class="text-center">{{ $s['avg_work_hours'] ?? 0 }}</td>
                <td class="text-center {{ ($s['total_overtime_hours'] ?? 0) > 0 ? 'text-blue' : '' }}">{{ $s['total_overtime_hours'] ?? 0 }}</td>
                <td class="text-center {{ ($s['total_late_minutes'] ?? 0) > 0 ? 'text-red' : '' }}">{{ $s['total_late_minutes'] ?? 0 }}</td>
                <td class="text-center {{ ($s['absent_days'] ?? 0) > 0 ? 'text-red' : '' }}">{{ $s['absent_days'] ?? 0 }}</td>
                <td class="text-center text-amber">{{ $s['leave_days'] ?? 0 }}</td>
                <td class="text-center">{{ $s['efficiency_pct'] ?? 0 }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">{{ now()->format('d.m.Y H:i') }} tarihinde oluşturuldu · MIYSOFT PTS</div>
</body>
</html>
