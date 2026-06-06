<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vardiya Atamaları</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 5px 7px; text-align: left; }
        th { background: #f5f5f5; font-size: 10px; }
        h1 { font-size: 16px; color: #333; }
        .summary { margin: 5px 0; font-size: 11px; color: #666; }
    </style>
</head>
<body>
    <h1>Vardiya Atama Takvimi</h1>
    <p>{{ now()->format('d.m.Y H:i') }}</p>
    @if($personel)
        <p class="summary">Personel: {{ $personel->first_name }} {{ $personel->last_name }}</p>
    @endif
    <table>
        <thead>
            <tr>
                <th>Tarih</th>
                <th>Personel</th>
                <th>Vardiya</th>
                <th>Saat</th>
                <th>Plan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assignments as $a)
            <tr>
                <td>{{ $a->date?->toDateString() }}</td>
                <td>{{ $a->personel?->first_name }} {{ $a->personel?->last_name }}</td>
                <td>{{ $a->shift?->name }}</td>
                <td>{{ $a->shift?->start_time }} - {{ $a->shift?->end_time }}</td>
                <td>{{ $a->shiftPlan?->name }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if($assignments->isEmpty())
        <p style="text-align:center;color:#999;margin-top:40px;">Atama bulunamadı.</p>
    @endif
</body>
</html>
