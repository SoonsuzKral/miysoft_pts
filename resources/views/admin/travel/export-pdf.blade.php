<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Seyahat Talepleri</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; margin: 20px; }
        h2 { text-align: center; margin-bottom: 15px; color: #333; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #02E0FB; color: #fff; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
        td { padding: 5px 8px; border-bottom: 1px solid #e0e0e0; }
        tr:nth-child(even) td { background: #f9f9f9; }
        .status-pending { color: #d97706; }
        .status-approved { color: #059669; }
        .status-rejected { color: #dc2626; }
        .status-cancelled { color: #6b7280; }
        .status-completed { color: #2563eb; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer { text-align: center; margin-top: 15px; font-size: 9px; color: #999; }
    </style>
</head>
<body>
    <h2>Seyahat Talepleri Raporu</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Personel</th>
                <th>Gidilecek Yer</th>
                <th>Gidiş</th>
                <th>Dönüş</th>
                <th>Ulaşım</th>
                <th>Tahmini Maliyet</th>
                <th>Durum</th>
                <th>Onaylayan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($travels as $t)
            <tr>
                <td>{{ $t->id }}</td>
                <td>{{ $t->personel?->first_name }} {{ $t->personel?->last_name }}</td>
                <td>{{ $t->destination }}</td>
                <td>{{ $t->departure_date instanceof \Carbon\Carbon ? $t->departure_date->format('d.m.Y') : $t->departure_date }}</td>
                <td>{{ $t->return_date instanceof \Carbon\Carbon ? $t->return_date->format('d.m.Y') : $t->return_date }}</td>
                <td>{{ $t->transportation_mode ?: '-' }}</td>
                <td class="text-right">{{ $t->estimated_cost ? number_format($t->estimated_cost, 2) . ' ' . ($t->currency ?? 'TRY') : '-' }}</td>
                <td class="status-{{ $t->status }}">
                    @switch($t->status)
                        @case('pending') Bekliyor @break
                        @case('approved') Onaylandı @break
                        @case('rejected') Reddedildi @break
                        @case('cancelled') İptal @break
                        @case('completed') Tamamlandı @break
                        @default {{ $t->status }}
                    @endswitch
                </td>
                <td>{{ $t->approver?->name ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">{{ now()->format('d.m.Y H:i') }} tarihinde oluşturuldu.</div>
</body>
</html>
