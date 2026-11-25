<?php

namespace App\Http\Controllers;

use App\Models\ArusKas;
use App\Models\UnitUsaha;
use App\Services\LaporanKeuanganService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardKeuanganController extends Controller
{
    public function index(Request $request, LaporanKeuanganService $laporan): View
    {
        $this->pastikanPengelola();
        $filter = $request->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'unit_usaha_id' => ['nullable', 'integer', 'exists:unit_usaha,id'],
        ]);
        $tanggalMulai = Carbon::parse($filter['tanggal_mulai'] ?? now()->startOfMonth()->toDateString())->startOfDay();
        $tanggalAkhir = Carbon::parse($filter['tanggal_akhir'] ?? now()->toDateString())->endOfDay();
        $unitUsahaId = isset($filter['unit_usaha_id']) ? (int) $filter['unit_usaha_id'] : null;

        $query = ArusKas::with(['unitUsaha', 'transaksiPembayaran.tagihan', 'pencatat'])
            ->whereBetween('tanggal_transaksi', [$tanggalMulai->toDateString(), $tanggalAkhir->toDateString()]);
        if ($unitUsahaId) $query->where('unit_usaha_id', $unitUsahaId);
        $unitUsaha = UnitUsaha::where('status_aktif', true)->orderBy('nama_unit')->get();

        return view('pengelola.index', [
            ...$laporan->ringkasanDashboard($tanggalMulai, $tanggalAkhir, $unitUsahaId),
            'aktivitasTerbaru' => $laporan->aktivitasTerbaru($tanggalMulai, $tanggalAkhir, $unitUsahaId),
            'arusKas' => $query->latest('tanggal_transaksi')->latest('id')->paginate(8)->withQueryString(),
            'unitUsaha' => $unitUsaha,
            'unitTerpilih' => $unitUsaha->firstWhere('id', $unitUsahaId)?->nama_unit ?? 'Semua Unit',
            'filter' => $filter,
            'tanggalMulai' => $tanggalMulai,
            'tanggalAkhir' => $tanggalAkhir,
            'jumlahMenunggu' => \App\Models\TransaksiPembayaran::where('status', 'MENUNGGU_KONFIRMASI')->count(),
        ]);
    }

    public function piutang(Request $request, LaporanKeuanganService $laporan): View
    {
        $this->pastikanPengelola();
        $filter = $request->validate(['unit_usaha_id' => ['nullable', 'integer', 'exists:unit_usaha,id']]);
        $unitUsahaId = isset($filter['unit_usaha_id']) ? (int) $filter['unit_usaha_id'] : null;

        return view('pengelola.piutang', [
            'daftarPiutang' => $laporan->daftarPiutang($unitUsahaId),
            'unitUsaha' => UnitUsaha::where('status_aktif', true)->orderBy('nama_unit')->get(),
            'filter' => $filter,
        ]);
    }

    public function laporanMusdes(Request $request, LaporanKeuanganService $laporan): View
    {
        $this->pastikanPengelola();
        $filter = $request->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ]);
        $tanggalMulai = Carbon::parse($filter['tanggal_mulai'] ?? now()->startOfYear()->toDateString())->startOfDay();
        $tanggalAkhir = Carbon::parse($filter['tanggal_akhir'] ?? now()->toDateString())->endOfDay();

        return view('pengelola.laporan-musdes', [
            ...$laporan->laporanMusdes($tanggalMulai, $tanggalAkhir),
            'tanggalMulai' => $tanggalMulai,
            'tanggalAkhir' => $tanggalAkhir,
            'filter' => $filter,
        ]);
    }

    private function pastikanPengelola(): void
    {
        abort_unless(Auth::check() && in_array(Auth::user()->role, ['PENGELOLA', 'ADMIN'], true), 403, 'Halaman ini hanya tersedia untuk Pengelola BUMDes.');
    }
}
