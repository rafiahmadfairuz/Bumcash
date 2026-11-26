<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function notice(Request $request): RedirectResponse|\Illuminate\View\View
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->intended(route('warga.dashboard'))
            : view('auth.verify-email');
    }

    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();
        return redirect()->route('warga.dashboard')->with('status', 'Alamat email berhasil diverifikasi.');
    }

    public function resend(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) return redirect()->route('warga.dashboard');
        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', 'Tautan verifikasi telah dikirim.');
    }
}
