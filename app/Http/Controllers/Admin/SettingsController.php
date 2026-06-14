<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;
        $settings = Setting::forCompany($companyId)->get()->pluck('typed_value', 'key');

        return view('admin.ayarlar.index', compact('settings'));
    }

    public function load(Request $request): JsonResponse
    {
        $this->authorize('settings.view');
        $companyId = auth()->user()->company_id;
        $keys = $request->input('keys', []);

        $settings = Setting::forCompany($companyId)
            ->when(count($keys), fn ($q) => $q->whereIn('key', $keys))
            ->get()
            ->pluck('typed_value', 'key');

        // Merge with defaults from company (including companies.settings JSON)
        $company = auth()->user()->company;
        $companySettings = $company?->settings ?? [];
        $defaults = [
            'company_name'    => $company?->name ?? '',
            'tax_number'      => $company?->tax_number ?? '',
            'tax_office'      => $company?->tax_office ?? '',
            'company_email'   => $company?->email ?? '',
            'company_phone'   => $company?->phone ?? '',
            'company_address' => $company?->address ?? '',
            'timezone'        => $company?->timezone ?? 'Europe/Istanbul',
            'currency'        => $company?->locale === 'en' ? 'USD' : 'TRY',
            'work_start'      => $companySettings['work_start'] ?? '09:00',
            'work_end'        => $companySettings['work_end'] ?? '18:00',
            'work_days'       => $companySettings['working_days'] ?? [1, 2, 3, 4, 5],
            'overtime_threshold' => 8,
            'late_tolerance'  => 15,
            'session_lifetime' => 60,
        ];

        foreach ($defaults as $key => $default) {
            if (!isset($settings[$key])) {
                $settings[$key] = $default;
            }
        }

        return response()->json(['data' => $settings]);
    }

    public function save(Request $request): JsonResponse
    {
        $this->authorize('settings.manage');
        $companyId = auth()->user()->company_id;

        $request->validate([
            'settings' => 'required|array',
        ]);

        Setting::setMany($companyId, $request->settings);

        // Update company fields and settings JSON if provided
        $company = auth()->user()->company;
        if ($company) {
            $companyFields = ['name', 'tax_number', 'tax_office', 'email', 'phone', 'address', 'timezone'];
            $updates = [];
            foreach ($companyFields as $field) {
                $key = 'company_' . $field;
                if (isset($request->settings[$key])) {
                    $companyField = $field === 'company_name' ? 'name' : $field;
                    $companyField = match ($key) {
                        'company_name' => 'name',
                        'company_email' => 'email',
                        'company_phone' => 'phone',
                        default => $field,
                    };
                    $updates[$companyField] = $request->settings[$key];
                }
            }

            // Sync work settings to companies.settings JSON column
            // (attendance services read from companies.settings, not settings table)
            $companySettings = $company->settings ?? [];
            $workKeys = ['work_start', 'work_end', 'work_days', 'overtime_threshold', 'late_tolerance'];
            foreach ($workKeys as $key) {
                if (array_key_exists($key, $request->settings)) {
                    $companyKey = $key === 'work_days' ? 'working_days' : $key;
                    $companySettings[$companyKey] = $request->settings[$key];
                }
            }
            $updates['settings'] = $companySettings;

            if (!empty($updates)) {
                $company->update($updates);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Ayarlar başarıyla kaydedildi.',
        ]);
    }
}
