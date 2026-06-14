<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class InstallController extends Controller
{
    public function index()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }
        return view('install.index', [
            'requirements' => $this->checkRequirements(),
        ]);
    }

    public function store(Request $request)
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        $validated = $request->validate([
            'app_name'     => 'required|string|max:255',
            'app_url'      => 'required|url|max:255',
            'db_host'      => 'required|string|max:255',
            'db_port'      => 'required|numeric',
            'db_database'  => 'required|string|max:255',
            'db_username'  => 'required|string|max:255',
            'db_password'  => 'nullable|string|max:255',
            'admin_email'  => 'required|email|max:255',
            'admin_password' => 'required|string|min:6|max:255',
        ]);

        try {
            // .env'yi güncelle
            $this->updateEnv($validated);

            // Config'i yeniden yükle (yeni DB ayarları için)
            Artisan::call('config:clear');
            Artisan::call('config:cache');

            // Key generate
            Artisan::call('key:generate', ['--force' => true]);

            // Migration + Seed
            Artisan::call('migrate', ['--force' => true, '--seed' => true]);

            // Admin kullanıcı oluştur (tinker ile)
            \Illuminate\Support\Facades\DB::table('users')->updateOrInsert(
                ['email' => $validated['admin_email']],
                [
                    'name'       => 'Admin',
                    'email'      => $validated['admin_email'],
                    'password'   => bcrypt($validated['admin_password']),
                    'company_id' => 1,
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // Installed flag
            File::put(storage_path('app/installed'), date('Y-m-d H:i:s'));

            // Symlink oluştur
            Artisan::call('storage:link', ['--force' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Kurulum başarıyla tamamlandı!',
                'redirect' => '/login',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kurulum hatası: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function isInstalled(): bool
    {
        return File::exists(storage_path('app/installed'));
    }

    private function checkRequirements(): array
    {
        $required = ['php', 'pdo', 'pdo_mysql', 'mbstring', 'openssl', 'json', 'curl', 'fileinfo', 'xml', 'tokenizer', 'bcmath'];
        $optional = ['gd', 'zip', 'intl'];

        $result = [];

        foreach ($required as $ext) {
            $result[$ext] = ($ext === 'php')
                ? version_compare(PHP_VERSION, '8.1.0', '>=')
                : extension_loaded($ext);
        }

        foreach ($optional as $ext) {
            $result[$ext . ' (opsiyonel)'] = extension_loaded($ext);
        }

        return $result;
    }

    private function updateEnv(array $data): void
    {
        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            File::copy(base_path('.env.example'), $envPath);
        }

        $replacements = [
            'APP_NAME'     => '"' . $data['app_name'] . '"',
            'APP_URL'      => $data['app_url'],
            'DB_HOST'      => $data['db_host'],
            'DB_PORT'      => $data['db_port'],
            'DB_DATABASE'  => $data['db_database'],
            'DB_USERNAME'  => $data['db_username'],
            'DB_PASSWORD'  => $data['db_password'] ?? '',
        ];

        $env = File::get($envPath);

        foreach ($replacements as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}={$value}";
            if (preg_match($pattern, $env)) {
                $env = preg_replace($pattern, $replacement, $env);
            } else {
                $env .= "\n{$replacement}";
            }
        }

        File::put($envPath, $env);
    }
}
