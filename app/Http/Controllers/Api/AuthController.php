<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string|max:191',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Geçersiz kimlik bilgileri.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Hesabınız pasif durumda.'],
            ]);
        }

        $token = $user->createToken($request->device_name ?? 'api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => now()->addDays(30)->toISOString(),
            'user' => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'company_id'    => $user->company_id,
                'roles'         => $user->getRoleNames()->toArray(),
                'permissions'   => $user->getAllPermissions()->pluck('name')->toArray(),
                'personel_id'   => $user->personel?->id,
            ],
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|string|lowercase|email|max:255|unique:users',
            'password'     => 'required|confirmed|min:8',
            'company_name' => 'nullable|string|max:255',
        ]);

        $company = null;
        if ($request->filled('company_name')) {
            $company = \App\Models\Company::create([
                'name'   => $request->company_name,
                'domain' => \Illuminate\Support\Str::slug($request->company_name) . '.miysoft.local',
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

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'company_id'    => $user->company_id,
                'roles'         => $user->getRoleNames()->toArray(),
            ],
        ], 201);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('roles.permissions', 'company', 'personel');

        return response()->json([
            'user' => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'company_id'    => $user->company_id,
                'company'       => $user->company,
                'personel'      => $user->personel,
                'roles'         => $user->getRoleNames()->toArray(),
                'permissions'   => $user->getAllPermissions()->pluck('name')->toArray(),
                'email_verified_at' => $user->email_verified_at,
                'created_at'    => $user->created_at,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Başarıyla çıkış yapıldı.']);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Tüm cihazlardan çıkış yapıldı.']);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Şifre sıfırlama bağlantısı gönderildi.'])
            : response()->json(['message' => 'Bağlantı gönderilemedi.'], 500);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'                 => 'required',
            'email'                 => 'required|email|exists:users,email',
            'password'              => 'required|confirmed|min:8',
            'password_confirmation' => 'required',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(\Illuminate\Support\Str::random(60));
                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Şifre başarıyla sıfırlandı.'])
            : response()->json(['message' => 'Şifre sıfırlanamadı.'], 500);
    }
}