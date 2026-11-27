<?php

namespace App\Http\Controllers;

use App\Models\PiutangUsaha;
use App\Models\UnitUsaha;
use App\Models\UtangUsaha;
use App\Services\PembukuanUsahaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PembukuanUsahaController extends Controller
{
    public function index(): View
    {
        $this->authorizePengelola();
        return view('pengelola.kewajiban-usaha', [
            'unitUsaha' => UnitUsaha::where('status_aktif', true)->orderBy('nama_unit')->get(),
            'piutang' => PiutangUsaha::with('unitUsaha')->latest('tanggal_transaksi')->paginate(10, ['*'], 'piutang_page'),
            'utang' => UtangUsaha::with('unitUsaha')->latest('tanggal_transaksi')->paginate(10, ['*'], 'utang_page'),
        ]);
    }

    public function catatUtilitas(Request $request, PembukuanUsahaService $service): RedirectResponse
    {
        $actor = $this->authorizePengelola();
        $data = $request->validate([
            'unit_usaha_id' => ['required', 'integer', Rule::exists('unit_usaha', 'id')->where('status_aktif', true)],
            'kategori' => ['required', Rule::in(['PLN', 'PDAM', 'PBB'])],
            'nama_penyedia' => ['required', 'string', 'max:120'], 'nomor_objek' => ['nullable', 'string', 'max:100'],
            'periode' => ['nullable', 'string', 'max:30'], 'referensi_tagihan' => ['nullable', 'string', 'max:100', 'unique:tagihan_utilitas,referensi_tagihan'],
            'nominal' => ['required', 'regex:/\A\d+(?:\.\d{1,2})?\z/', 'gt:0', 'max:9999999999.99'],
            'biaya_tambahan' => ['nullable', 'regex:/\A\d+(?:\.\d{1,2})?\z/', 'min:0', 'max:9999999999.99'],
            'tanggal_tagihan' => ['required', 'date'], 'jatuh_tempo' => ['nullable', 'date'], 'catatan' => ['nullable', 'string', 'max:2000'],
        ]);
        $service->catatUtilitas($data, $actor->id);
        return back()->with('success', 'Tagihan utilitas dicatat sebagai utang; saldo kas belum berubah sebelum settlement.');
    }

    public function catatPiutang(Request $request, PembukuanUsahaService $service): RedirectResponse
    {
        $actor = $this->authorizePengelola();
        $data = $request->validate($this->validasiTagihanUsaha($request, 'piutang_usaha'));
        $service->catatPiutang($data, $actor->id);
        return back()->with('success', 'Piutang dagang dicatat. Kas akan bertambah hanya ketika settlement diterima.');
    }

    public function catatUtang(Request $request, PembukuanUsahaService $service): RedirectResponse
    {
        $actor = $this->authorizePengelola();
        $data = $request->validate($this->validasiTagihanUsaha($request, 'utang_usaha'));
        $service->catatUtang($data, $actor->id);
        return back()->with('success', 'Utang dagang dicatat. Kas akan berkurang hanya ketika settlement dibayarkan.');
    }

    public function settlement(Request $request, string $jenis, int $id, PembukuanUsahaService $service): RedirectResponse
    {
        $actor = $this->authorizePengelola();
        abort_unless(in_array($jenis, ['piutang', 'utang'], true), 404);
        $data = $request->validate([
            'nominal' => ['required', 'regex:/\A\d+(?:\.\d{1,2})?\z/', 'gt:0', 'max:9999999999.99'],
            'tanggal_settlement' => ['required', 'date'], 'metode' => ['required', 'string', 'max:50'],
            'referensi' => ['required', 'string', 'max:100', 'unique:settlement_usaha,referensi'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);
        $service->settlement($jenis, $id, $data, $actor->id);
        return back()->with('success', 'Settlement dicatat dan jurnal kas dibuat secara atomik.');
    }

    private function validasiTagihanUsaha(Request $request, string $table): array
    {
        return [
            'unit_usaha_id' => ['required', 'integer', Rule::exists('unit_usaha', 'id')->where('status_aktif', true)],
            'pihak' => ['required', 'string', 'max:150'], 'referensi_dokumen' => ['nullable', 'string', 'max:100', Rule::unique($table, 'referensi_dokumen')->where(fn ($query) => $query->where('unit_usaha_id', $request->input('unit_usaha_id')))],
            'deskripsi' => ['required', 'string', 'max:2000'], 'tanggal_transaksi' => ['required', 'date'],
            'jatuh_tempo' => ['nullable', 'date'], 'nominal' => ['required', 'regex:/\A\d+(?:\.\d{1,2})?\z/', 'gt:0', 'max:9999999999.99'],
        ];
    }

    private function authorizePengelola()
    {
        abort_unless(Auth::check() && in_array(Auth::user()->role, ['PENGELOLA', 'ADMIN'], true), 403);
        return Auth::user();
    }
}
