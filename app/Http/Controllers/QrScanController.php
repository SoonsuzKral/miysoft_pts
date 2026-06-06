<?php

namespace App\Http\Controllers;

use App\Modules\Puantaj\Models\TimeRecord;
use App\Modules\Personel\Models\Personel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

class QrScanController extends Controller
{
    public function kiosk()
    {
        $companyId = auth()->user()->company_id;
        $personels = Personel::forCompany($companyId)->active()
            ->with(['department', 'position'])
            ->get();
        return view('admin.qr.kiosk', compact('personels'));
    }

    public function personelQrCode(Personel $personel)
    {
        $token = $personel->qr_token;
        if (!$token) {
            $token = Str::random(32);
            $personel->update(['qr_token' => $token]);
        }

        $url = route('qr.scan.submit', ['token' => $token]);

        $qrCode = QrCode::format('svg')
            ->size(300)
            ->errorCorrection('M')
            ->generate($url);

        return response($qrCode, 200, ['Content-Type' => 'image/svg+xml']);
    }

    public function scanView(string $token)
    {
        $personel = Personel::where('qr_token', $token)->first();

        if (!$personel) {
            return view('admin.qr.scan', ['error' => 'Geçersiz QR kod.']);
        }

        $lastRecord = TimeRecord::where('personel_id', $personel->id)
            ->whereDate('recorded_at', today())
            ->orderByDesc('recorded_at')
            ->first();

        $nextType = $lastRecord?->type === 'in' ? 'out' : 'in';

        return view('admin.qr.scan', compact('personel', 'nextType', 'lastRecord'));
    }

    public function submit(Request $request, string $token): JsonResponse
    {
        $personel = Personel::where('qr_token', $token)->first();

        if (!$personel) {
            return response()->json(['success' => false, 'message' => 'Geçersiz QR kod.'], 404);
        }

        $lastRecord = TimeRecord::where('personel_id', $personel->id)
            ->whereDate('recorded_at', today())
            ->orderByDesc('recorded_at')
            ->first();

        $type = $lastRecord?->type === 'in' ? 'out' : 'in';

        $record = TimeRecord::create([
            'company_id'  => $personel->company_id,
            'personel_id' => $personel->id,
            'type'        => $type,
            'recorded_at' => now(),
            'source'      => TimeRecord::SOURCE_QR,
            'note'        => null,
            'created_by'  => null,
        ]);

        $message = $type === 'in' ? 'Giriş kaydedildi' : 'Çıkış kaydedildi';

        return response()->json([
            'success' => true,
            'message' => "{$personel->first_name} {$personel->last_name} - {$message}",
            'data'    => [
                'personel' => $personel->only(['id', 'first_name', 'last_name']),
                'type'     => $type,
                'time'     => $record->recorded_at->format('H:i:s'),
            ],
        ]);
    }
}
