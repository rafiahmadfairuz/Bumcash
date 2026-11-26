<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;
use App\Rules\StrongPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Validation\ValidationException;

class RegistrationController extends Controller
{
    public function create(): View { return view('auth.register'); }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'alpha_dash', 'max:50', 'unique:users,username'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', new StrongPassword],
        ]);

        $data['username'] = mb_strtolower($data['username']);
        try {
            $user = DB::transaction(function () use ($data): User {
                $user = User::create([
                    'nama_lengkap' => $data['nama_lengkap'],
                    'username' => $data['username'],
                    'email' => mb_strtolower($data['email']),
                    'password' => $data['password'],
                    'role' => 'WARGA',
                ]);

                $user->sendEmailVerificationNotification();

                return $user;
            });
        } catch (\Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'email' => 'Akun belum dapat dibuat karena email verifikasi gagal dikirim. Periksa pengaturan email lalu coba lagi.',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('warga.dashboard')->with('status', 'Akun dibuat. Silakan verifikasi alamat email melalui tautan yang kami kirimkan.');
    }
}
