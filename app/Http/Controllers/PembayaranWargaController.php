<?php

namespace App\Http\Controllers;

use App\Models\Tagihan;
use App\Models\Layanan;
use App\Models\TransaksiPembayaran;
use App\Models\PermintaanTagihanVariabel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PembayaranWargaController extends Controller
{
    public function index(): View
    {
        $warga = $this->wargaAktif();
        $tagihanAktif = Tagihan::with(['layanan.unitUsaha'])
            ->where('user_id', $warga->id)
            ->whereIn('status', ['BELUM_DIBAYAR', 'MENUNGGU_KONFIRMASI'])
            ->orderBy('jatuh_tempo')
            ->get();

        $layananTerdaftar = Tagihan::where('user_id', $warga->id)->distinct()->pluck('layanan_id');
        $langgananAktif = DB::table('langganan_layanan')->where('user_id', $warga->id)->where('status', 'AKTIF')->pluck('layanan_id');
        $layanan = Layanan::with('unitUsaha')
            ->where('status_aktif', true)->where('tampil_di_katalog', true)
            ->whereHas('unitUsaha', fn ($query) => $query->where('status_aktif', true))
            ->orderBy('nama_layanan')->get();
        $transaksiTerbaru = TransaksiPembayaran::with(['tagihan.layanan.unitUsaha'])
            ->where('user_id', $warga->id)->latest('waktu_bayar')->take(5)->get();
        $totalLunas = TransaksiPembayaran::where('user_id', $warga->id)
            ->where('status', 'SELESAI')->sum('nominal_dibayar');
        $layananAktif = $layananTerdaftar->count();

        return view('warga.tagihan', [
            'warga' => $warga,
            'tagihanAktif' => $tagihanAktif,
            'totalPiutang' => $tagihanAktif->sum(fn (Tagihan $tagihan) => (float) $tagihan->nominal),
            'totalLunas' => (float) $totalLunas,
            'layananAktif' => $layananAktif,
            'layananTerdaftar' => $layananTerdaftar,
            'langgananAktif' => $langgananAktif,
            'layanan' => $layanan,
            'transaksiTerbaru' => $transaksiTerbaru,
        ]);
    }

    public function detail(int $tagihan): View
    {
        $warga = $this->wargaAktif();
        $tagihan = Tagihan::with(['layanan.unitUsaha', 'transaksiPembayaran'])
            ->where('user_id', $warga->id)
            ->findOrFail($tagihan);

        return view('warga.detail-tagihan', compact('warga', 'tagihan'));
    }

    public function beli(Request $request, int $layanan): RedirectResponse
    {
        $warga = $this->wargaAktif();
        $service = Layanan::where('status_aktif', true)->where('tampil_di_katalog', true)->whereHas('unitUsaha', fn ($query) => $query->where('status_aktif', true))->findOrFail($layanan);
        if ($service->jenis_layanan === 'TAGIHAN_VARIABEL') {
            $data = $request->validate(['nomor_objek' => ['required', 'string', 'max:100']]);
            PermintaanTagihanVariabel::create(['user_id' => $warga->id, 'layanan_id' => $service->id, 'nomor_objek' => $data['nomor_objek']]);
            return back()->with('success', 'Permintaan pengecekan terkirim. Pengelola akan memeriksa tagihan melalui kanal provider resmi.');
        }
        if ($service->jenis_layanan === 'BULANAN') {
            $request->validate(['setuju_langganan' => ['accepted']]);
        }
        if ($service->jenis_layanan === 'BULANAN') {
            DB::table('langganan_layanan')->updateOrInsert(
                ['user_id' => $warga->id, 'layanan_id' => $service->id, 'status' => 'AKTIF'],
                ['disetujui_pada' => now(), 'updated_at' => now(), 'created_at' => now()]
            );
        }
        $tagihan = DB::transaction(function () use ($warga, $layanan) {
            $layanan = Layanan::with('unitUsaha')->where('status_aktif', true)->where('tampil_di_katalog', true)->whereHas('unitUsaha', fn ($query) => $query->where('status_aktif', true))->lockForUpdate()->findOrFail($layanan);
            abort_unless($layanan->jenis_layanan !== 'TAGIHAN_VARIABEL', 422, 'Tagihan variabel harus melalui pengecekan provider.');
            $bulan = now()->month;
            $tahun = now()->year;
            return Tagihan::create([
                'kode_tagihan' => 'BEL-'.now()->format('YmdHis').'-'.Str::upper(Str::random(5)),
                'user_id' => $warga->id, 'layanan_id' => $layanan->id,
                'periode_bulan' => $bulan, 'periode_tahun' => $tahun,
                'rincian_pemakaian' => ($layanan->jenis_layanan === 'BULANAN' ? 'Langganan ' : 'Pembelian ').$layanan->nama_layanan,
                'nominal' => $layanan->tarif_dasar, 'jatuh_tempo' => now()->toDateString(), 'status' => 'BELUM_DIBAYAR',
            ]);
        });
        return redirect()->route('warga.tagihan.detail', $tagihan)->with('success', 'Pesanan dibuat. Lanjutkan pembayaran dari rincian tagihan.');
    }

    public function ajukanPembayaran(Request $request, int $tagihan): RedirectResponse
    {
        $warga = $this->wargaAktif();
        $data = $request->validate([
            'metode_pembayaran' => ['required', 'in:TUNAI_LOKET,TRANSFER_BANK_DESA'],
        ]);

        $transaksi = DB::transaction(function () use ($warga, $tagihan, $data) {
            $tagihan = Tagihan::with('layanan')
                ->where('user_id', $warga->id)
                ->lockForUpdate()
                ->findOrFail($tagihan);

            abort_unless($tagihan->status === 'BELUM_DIBAYAR', 422, 'Tagihan ini sudah memiliki pengajuan atau telah lunas.');

            $transaksi = TransaksiPembayaran::create([
                'kode_transaksi' => 'TRX-'.now()->format('YmdHis').'-'.Str::upper(Str::random(6)),
                'tagihan_id' => $tagihan->id,
                'user_id' => $warga->id,
                'nominal_dibayar' => $tagihan->nominal,
                'metode_pembayaran' => $data['metode_pembayaran'],
                'status' => 'MENUNGGU_KONFIRMASI',
                'waktu_bayar' => now(),
            ]);

            $tagihan->update(['status' => 'MENUNGGU_KONFIRMASI']);

            return $transaksi;
        });

        return redirect()->route('warga.riwayat')->with('success', 'Konfirmasi pembayaran telah dikirim. Tagihan menunggu verifikasi Pengelola BUMDes. Nomor transaksi: '.$transaksi->kode_transaksi);
    }

    public function riwayat(): View
    {
        $warga = $this->wargaAktif();
        $transaksi = TransaksiPembayaran::with(['tagihan.layanan.unitUsaha'])
            ->where('user_id', $warga->id)
            ->latest('waktu_bayar')
            ->paginate(10);

        return view('warga.riwayat', compact('warga', 'transaksi'));
    }

    public function struk(int $transaksi): View
    {
        $warga = $this->wargaAktif();
        $transaksi = TransaksiPembayaran::with(['warga', 'tagihan.layanan.unitUsaha'])
            ->where('user_id', $warga->id)
            ->findOrFail($transaksi);

        abort_unless($transaksi->status === 'SELESAI', 404);

        return view('warga.struk', compact('warga', 'transaksi'));
    }

    private function wargaAktif()
    {
        abort_unless(Auth::check() && Auth::user()->role === 'WARGA', 403, 'Halaman ini hanya tersedia untuk akun Warga.');

        return Auth::user();
    }
}
