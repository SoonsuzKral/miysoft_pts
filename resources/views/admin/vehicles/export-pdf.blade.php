<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Araç Listesi</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; margin: 15px; }
        h2 { text-align: center; margin-bottom: 12px; color: #333; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #02E0FB; color: #fff; padding: 5px 6px; text-align: left; font-size: 8px; text-transform: uppercase; }
        td { padding: 4px 6px; border-bottom: 1px solid #e0e0e0; }
        tr:nth-child(even) td { background: #f9f9f9; }
        .status-active { color: #059669; }
        .status-maintenance { color: #d97706; }
        .status-out_of_service { color: #dc2626; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer { text-align: center; margin-top: 10px; font-size: 8px; color: #999; }
    </style>
</head>
<body>
    <h2>Araç Listesi Raporu</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Plaka</th>
                <th>Marka</th>
                <th>Model</th>
                <th>Yıl</th>
                <th>Renk</th>
                <th>Yakıt</th>
                <th>KM</th>
                <th>Personel</th>
                <th>Durum</th>
                <th>Son Bakım</th>
                <th>Sigorta</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vehicles as $v)
            <tr>
                <td>{{ $v->id }}</td>
                <td>{{ $v->plate }}</td>
                <td>{{ $v->brand }}</td>
                <td>{{ $v->model }}</td>
                <td class="text-center">{{ $v->year ?? '-' }}</td>
                <td>{{ $v->color ?? '-' }}</td>
                <td>{{ $v->fuel_type ?? '-' }}</td>
                <td class="text-right">{{ $v->current_km ? number_format($v->current_km, 0) : '-' }}</td>
                <td>{{ $v->assignedPersonel?->first_name }} {{ $v->assignedPersonel?->last_name }}</td>
                <td class="status-{{ $v->status }}">
                    @switch($v->status)
                        @case('active') Aktif @break
                        @case('maintenance') Bakımda @break
                        @case('out_of_service') Hizmet Dışı @break
                        @default {{ $v->status }}
                    @endswitch
                </td>
                <td>{{ $v->last_maintenance_date ? $v->last_maintenance_date->format('d.m.Y') : '-' }}</td>
                <td>{{ $v->insurance_date ? $v->insurance_date->format('d.m.Y') : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">{{ now()->format('d.m.Y H:i') }} tarihinde oluşturuldu.</div>
</body>
</html>
