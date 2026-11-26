<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'identifier' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $user = User::where('username', $data['identifier'])->orWhere('email', mb_strtolower($data['identifier']))->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['identifier' => 'Username atau kata sandi belum sesuai.'])->onlyInput('identifier');
        }

        if ($user->role === 'WARGA' && blank($user->email)) {
            Auth::login($user, (bool) ($data['remember'] ?? false));
            $request->session()->regenerate();

            return redirect()->route('warga.dashboard');
        }

        if ($user->role === 'WARGA' && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
            Auth::login($user, (bool) ($data['remember'] ?? false));
            $request->session()->regenerate();

            return redirect()->route('verification.notice')->with('status', 'Akun Anda sudah terdaftar tetapi email belum diverifikasi. Tautan verifikasi baru telah dikirim.');
        }

        Auth::login($user, (bool) ($data['remember'] ?? false));
        $request->session()->regenerate();

        return $this->redirectByRole($user);
    }

    private function redirectByRole(User $user): RedirectResponse
    {
        return redirect()->route($user->role === 'WARGA' ? 'warga.dashboard' : 'pengelola.dashboard');
    }
}
