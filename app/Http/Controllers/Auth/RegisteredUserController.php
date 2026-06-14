<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Mail\WelcomeMail;
use App\Modules\Abonelik\Models\CompanySubscription;
use App\Modules\Abonelik\Models\SubscriptionPlan;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password'     => ['required', 'confirmed', Rules\Password::defaults()],
            'company_name' => ['nullable', 'string', 'max:255'],
        ]);

        // E-posta benzersizliğini zarifçe kontrol et
        if (User::where('email', $request->email)->exists()) {
            return back()->withErrors(['email' => 'Bu e-posta zaten kullanımda'])->withInput();
        }

        // Şirket oluştur (isim verilmişse)
        $company = null;
        if ($request->filled('company_name')) {
            $company = Company::create([
                'name'   => $request->company_name,
                'domain' => Str::slug($request->company_name) . '.miysoft.local',
                'email'  => $request->email,
                'status' => 'trial',
            ]);
        }

        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'company_id'        => $company?->id,
            'email_verified_at' => now(),
            'is_active'         => true,
        ]);

        $isFirstUser = User::count() === 1;
        if ($isFirstUser && Role::where('name', 'super_admin')->exists()) {
            $user->assignRole('super_admin');
        } elseif (Role::where('name', 'company_admin')->exists()) {
            $user->assignRole('company_admin');
        }

        // 15 günlük deneme aboneliği oluştur
        if ($company) {
            $trialPlan = SubscriptionPlan::where('slug', 'trial')->first()
                ?? SubscriptionPlan::active()->orderBy('price_monthly')->first();

            if ($trialPlan) {
                CompanySubscription::create([
                    'company_id'    => $company->id,
                    'plan_id'       => $trialPlan->id,
                    'status'        => 'trial',
                    'billing_cycle' => 'monthly',
                    'price'         => 0,
                    'started_at'    => now(),
                    'trial_ends_at' => now()->addDays(15),
                    'ends_at'       => now()->addDays(15),
                ]);
            }
        }

        event(new Registered($user));

        Auth::login($user);

        // Hoş geldin e-postası gönder
        try {
            Mail::to($user->email)->queue(new WelcomeMail(
                name: $user->name,
                companyName: $company?->name ?? 'MİYSOFT PTS',
                loginUrl: route('login'),
                trialEndsAt: $company?->trial_ends_at?->format('d.m.Y') ?? now()->addDays(15)->format('d.m.Y'),
            ));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('WelcomeMail gönderilemedi: ' . $e->getMessage());
        }

        return redirect()->route('admin.dashboard');
    }
}
