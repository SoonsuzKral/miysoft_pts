<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Personel Puantaj Detayı</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; margin: 15px; }
        h2 { text-align: center; margin-bottom: 3px; color: #333; font-size: 14px; }
        .subtitle { text-align: center; font-size: 10px; color: #666; margin-bottom: 12px; }
        .info-table { width: 100%; margin-bottom: 12px; }
        .info-table td { padding: 2px 8px; border: none; font-size: 9px; }
        .info-table .label { color: #888; font-weight: bold; width: 100px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th { background: #02E0FB; color: #fff; padding: 4px 5px; text-align: left; font-size: 8px; text-transform: uppercase; }
        td { padding: 3px 5px; border-bottom: 1px solid #e0e0e0; font-size: 8px; }
        tr:nth-child(even) td { background: #f9f9f9; }
        .text-center { text-align: center; }
        .summary-grid { display: flex; gap: 8px; margin-bottom: 12px; }
        .summary-item { flex: 1; background: #f0f9ff; padding: 6px 8px; border-radius: 4px; text-align: center; }
        .summary-item .val { font-size: 14px; font-weight: bold; color: #1e293b; }
        .summary-item .lbl { font-size: 7px; color: #64748b; text-transform: uppercase; }
        .status-present { color: #059669; }
        .status-late { color: #d97706; }
        .status-overtime { color: #2563eb; }
        .status-absent { color: #dc2626; }
        .status-weekend { color: #94a3b8; }
        .status-holiday { color: #7c3aed; }
        .status-leave { color: #f59e0b; }
        .footer { text-align: center; margin-top: 8px; font-size: 7px; color: #999; }
    </style>
</head>
<body>
    <h2>Personel Puantaj Detayı</h2>
    <p class="subtitle">{{ $monthName }}</p>

    <table class="info-table">
        <tr><td class="label">Ad Soyad:</td><td>{{ $personel->first_name }} {{ $personel->last_name }}</td></tr>
        <tr><td class="label">Departman:</td><td>{{ $personel->department?->name ?? '—' }}</td></tr>
        <tr><td class="label">Pozisyon:</td><td>{{ $personel->position?->name ?? '—' }}</td></tr>
        <tr><td class="label">E-posta:</td><td>{{ $personel->email ?? '—' }}</td></tr>
        <tr><td class="label">Telefon:</td><td>{{ $personel->phone ?? '—' }}</td></tr>
    </table>

    <div class="summary-grid">
        <div class="summary-item">
            <div class="val">{{ $summary['present_days'] ?? 0 }} / {{ $summary['working_days_in_month'] ?? 0 }}</div>
            <div class="lbl">Çalışma Günü</div>
        </div>
        <div class="summary-item">
            <div class="val">{{ $summary['total_work_hours'] ?? 0 }}</div>
            <div class="lbl">Toplam Saat</div>
        </div>
        <div class="summary-item">
            <div class="val">{{ $summary['total_overtime_hours'] ?? 0 }}</div>
            <div class="lbl">Fazla Mesai (sa)</div>
        </div>
        <div class="summary-item">
            <div class="val">{{ $summary['total_late_minutes'] ?? 0 }}</div>
            <div class="lbl">Gecikme (dk)</div>
        </div>
        <div class="summary-item">
            <div class="val">{{ $summary['absent_days'] ?? 0 }}</div>
            <div class="lbl">Devamsızlık</div>
        </div>
        <div class="summary-item">
            <div class="val">%{{ $summary['efficiency_pct'] ?? 0 }}</div>
            <div class="lbl">Verimlilik</div>
        </div>
    </div>

    <h3 style="font-size:10px; margin-bottom:6px;">Günlük Detay</h3>
    <table>
        <thead>
            <tr>
                <th>Gün</th>
                <th>Tarih</th>
                <th class="text-center">Giriş</th>
                <th class="text-center">Çıkış</th>
                <th class="text-center">Çalışma</th>
                <th class="text-center">Gecikme</th>
                <th class="text-center">Fazla Mesai</th>
                <th class="text-center">Durum</th>
            </tr>
        </thead>
        <tbody>
            @php
            $statusLabels = [
                'present' => 'Mevcut', 'late' => 'Geç Geldi', 'overtime' => 'Fazla Mesai',
                'absent' => 'Devamsız', 'incomplete' => 'Eksik', 'weekend' => 'Hafta Sonu',
                'holiday' => 'Tatil', 'leave' => 'İzinli', 'partial' => 'Eksik Mesai',
            ];
            $statusClasses = [
                'present' => 'status-present', 'late' => 'status-late', 'overtime' => 'status-overtime',
                'absent' => 'status-absent', 'weekend' => 'status-weekend', 'holiday' => 'status-holiday',
                'leave' => 'status-leave', 'incomplete' => 'status-leave', 'partial' => 'status-late',
            ];
            @endphp
            @foreach($summary['daily'] ?? [] as $date => $day)
            <tr>
                <td>{{ $day['day_name'] ?? '' }}</td>
                <td>{{ \Carbon\Carbon::parse($date)->format('d.m.Y') }}</td>
                <td class="text-center">{{ $day['check_in'] ?? '—' }}</td>
                <td class="text-center">{{ $day['check_out'] ?? '—' }}</td>
                <td class="text-center">{{ $day['net_work_hours'] ?? 0 }} sa</td>
                <td class="text-center {{ ($day['late_min'] ?? 0) > 0 ? 'status-late' : '' }}">{{ ($day['late_min'] ?? 0) > 0 ? $day['late_min'] . ' dk' : '—' }}</td>
                <td class="text-center {{ ($day['overtime_min'] ?? 0) > 0 ? 'status-overtime' : '' }}">{{ ($day['overtime_min'] ?? 0) > 0 ? round($day['overtime_min']/60*10)/10 . ' sa' : '—' }}</td>
                <td class="text-center {{ $statusClasses[$day['status'] ?? ''] ?? '' }}">{{ $statusLabels[$day['status'] ?? ''] ?? $day['status'] ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">{{ now()->format('d.m.Y H:i') }} tarihinde oluşturuldu · MIYSOFT PTS</div>
</body>
</html>
