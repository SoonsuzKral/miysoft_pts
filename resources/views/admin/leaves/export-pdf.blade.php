<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>İzin Talepleri</title>
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
        .text-center { text-align: center; }
        .footer { text-align: center; margin-top: 15px; font-size: 9px; color: #999; }
    </style>
</head>
<body>
    <h2>İzin Talepleri Raporu</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Personel</th>
                <th>İzin Türü</th>
                <th>Başlangıç</th>
                <th>Bitiş</th>
                <th class="text-center">Gün</th>
                <th>Durum</th>
                <th>Onaylayan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($leaves as $l)
            <tr>
                <td>{{ $l->id }}</td>
                <td>{{ $l->personel?->first_name }} {{ $l->personel?->last_name }}</td>
                <td>{{ $l->leaveType?->name }}</td>
                <td>{{ $l->start_date instanceof \Carbon\Carbon ? $l->start_date->format('d.m.Y') : $l->start_date }}</td>
                <td>{{ $l->end_date instanceof \Carbon\Carbon ? $l->end_date->format('d.m.Y') : $l->end_date }}</td>
                <td class="text-center">{{ $l->total_days }}</td>
                <td class="status-{{ $l->status }}">
                    @switch($l->status)
                        @case('pending') Bekliyor @break
                        @case('approved') Onaylandı @break
                        @case('rejected') Reddedildi @break
                        @case('cancelled') İptal @break
                        @default {{ $l->status }}
                    @endswitch
                </td>
                <td>{{ $l->approver?->name ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">{{ now()->format('d.m.Y H:i') }} tarihinde oluşturuldu.</div>
</body>
</html>