<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('admin.dashboard');
    }
}
