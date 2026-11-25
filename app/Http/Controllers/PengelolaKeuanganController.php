<?php

namespace App\Http\Controllers;

use App\Models\ArusKas;
use App\Models\AsetInventaris;
use App\Models\PenyertaanModal;
use App\Models\TransaksiPembayaran;
use App\Models\UnitUsaha;
use App\Services\ArusKasService;
use App\Services\PembayaranVerifikasiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PengelolaKeuanganController extends Controller
{
    private const KATEGORI = [
        'PEMASUKAN' => ['Pendapatan Sewa Aset', 'Pendapatan Usaha Lain', 'Pendapatan Non-Layanan'],
        'PENGELUARAN' => ['Beban Pemeliharaan Pipa/Alat', 'Beban Gaji & Honor', 'Beban Listrik & Utilitas', 'Beban ATK & Kantor', 'Beban BBM dan Operasional', 'Beban Perlengkapan/ATK'],
    ];

    public function verifikasi(): View
    {
        $this->pastikanPengelola();
        $transaksi = TransaksiPembayaran::with(['warga', 'tagihan.layanan.unitUsaha'])
            ->where('status', 'MENUNGGU_KONFIRMASI')
            ->oldest('waktu_bayar')
            ->paginate(10);

        return view('pengelola.verifikasi', compact('transaksi'));
    }

    public function konfirmasi(int $transaksi, PembayaranVerifikasiService $service): RedirectResponse
    {
        $pengelola = $this->pastikanPengelola();
        $service->konfirmasiLunas($transaksi, $pengelola->id);

        return back()->with('success', 'Pembayaran dikonfirmasi lunas. Pemasukan telah dicatat otomatis pada Unit Usaha terkait.');
    }

    public function tolak(Request $request, int $transaksi, PembayaranVerifikasiService $service): RedirectResponse
    {
        $this->pastikanPengelola();
        $data = $request->validate(['catatan_pengelola' => ['required', 'string', 'max:255']]);
        $service->tolak($transaksi, $data['catatan_pengelola']);

        return back()->with('success', 'Pengajuan ditolak. Tagihan dikembalikan ke status Belum Dibayar dan tidak ada pemasukan kas yang dibuat.');
    }

    public function arusKas(Request $request): View
    {
        $this->pastikanPengelola();
        $filter = $request->validate([
            'unit_usaha_id' => ['nullable', 'integer', 'exists:unit_usaha,id'],
            'jenis' => ['nullable', Rule::in(['PEMASUKAN', 'PENGELUARAN'])],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ]);
        $query = ArusKas::with(['unitUsaha', 'transaksiPembayaran.tagihan', 'pencatat'])->latest('tanggal_transaksi')->latest('id');
        if (! empty($filter['unit_usaha_id'])) $query->where('unit_usaha_id', $filter['unit_usaha_id']);
        if (! empty($filter['jenis'])) $query->where('jenis', $filter['jenis']);
        if (! empty($filter['tanggal_mulai'])) $query->whereDate('tanggal_transaksi', '>=', $filter['tanggal_mulai']);
        if (! empty($filter['tanggal_akhir'])) $query->whereDate('tanggal_transaksi', '<=', $filter['tanggal_akhir']);

        $arusKasMasuk = (float) (clone $query)->reorder()->where('jenis', 'PEMASUKAN')->sum('nominal');
        $arusKasKeluar = (float) (clone $query)->reorder()->where('jenis', 'PENGELUARAN')->sum('nominal');

        return view('pengelola.arus-kas', [
            'arusKas' => $query->paginate(12)->withQueryString(),
            'unitUsaha' => UnitUsaha::where('status_aktif', true)->orderBy('nama_unit')->get(),
            'kategori' => self::KATEGORI,
            'filter' => $filter,
            'arusKasMasuk' => $arusKasMasuk,
            'arusKasKeluar' => $arusKasKeluar,
            'saldoKas' => (float) ArusKas::where('jenis', 'PEMASUKAN')->sum('nominal') - (float) ArusKas::where('jenis', 'PENGELUARAN')->sum('nominal'),
        ]);
    }

    public function simpanArusKas(Request $request, ArusKasService $service): RedirectResponse
    {
        $pengelola = $this->pastikanPengelola();
        $jenis = $request->input('jenis');
        $kategoriDiizinkan = self::KATEGORI[$jenis] ?? [];
        $data = $request->validate([
            'unit_usaha_id' => ['required', 'integer', Rule::exists('unit_usaha', 'id')->where(fn ($query) => $query->where('status_aktif', true))],
            'tanggal_transaksi' => ['required', 'date'],
            'jenis' => ['required', Rule::in(['PEMASUKAN', 'PENGELUARAN'])],
            'kategori_akun' => ['required', 'string', Rule::in($kategoriDiizinkan)],
            'keterangan' => ['required', 'string', 'max:5000'],
            'nominal' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
        ]);
        $service->catatManual($data, $pengelola->id);

        return back()->with('success', 'Transaksi operasional berhasil dicatat pada Unit Usaha dan kategori akun yang dipilih.');
    }

    public function modalAset(): View
    {
        $this->pastikanPengelola();
        return view('pengelola.modal-aset', [
            'unitUsaha' => UnitUsaha::where('status_aktif', true)->orderBy('nama_unit')->get(),
            'penyertaanModal' => PenyertaanModal::with('unitUsaha')->latest('tanggal_terima')->paginate(10, ['*'], 'halaman_modal'),
            'aset' => AsetInventaris::with('unitUsaha')->latest('tanggal_perolehan')->paginate(10, ['*'], 'halaman_aset'),
            'totalModal' => (float) PenyertaanModal::sum('nominal'),
            'totalAset' => (float) AsetInventaris::sum('nilai_perolehan'),
        ]);
    }

    public function simpanModal(Request $request): RedirectResponse
    {
        $this->pastikanPengelola();
        $data = $request->validate([
            'unit_usaha_id' => ['required', 'integer', Rule::exists('unit_usaha', 'id')->where(fn ($query) => $query->where('status_aktif', true))],
            'tanggal_terima' => ['required', 'date'],
            'sumber_modal' => ['required', 'string', 'max:100'],
            'nomor_perdes' => ['required', 'string', 'max:100'],
            'nominal' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'keterangan' => ['required', 'string', 'max:5000'],
        ]);
        PenyertaanModal::create($data);

        return back()->with('success', 'Penyertaan modal berhasil dicatat dan ditautkan ke Unit Usaha.');
    }

    public function alokasikanModal(Request $request, int $modal): RedirectResponse
    {
        $this->pastikanPengelola();
        $data = $request->validate([
            'unit_usaha_id' => ['required', 'integer', Rule::exists('unit_usaha', 'id')->where(fn ($query) => $query->where('status_aktif', true))],
        ]);
        $penyertaanModal = PenyertaanModal::findOrFail($modal);
        $penyertaanModal->update(['unit_usaha_id' => $data['unit_usaha_id']]);

        return back()->with('success', 'Penyertaan modal berhasil dialokasikan ke Unit Usaha.');
    }

    public function simpanAset(Request $request): RedirectResponse
    {
        $this->pastikanPengelola();
        $data = $request->validate([
            'kode_aset' => ['required', 'string', 'max:30', 'unique:aset_inventaris,kode_aset'],
            'unit_usaha_id' => ['required', 'integer', Rule::exists('unit_usaha', 'id')->where(fn ($query) => $query->where('status_aktif', true))],
            'nama_aset' => ['required', 'string', 'max:100'],
            'tanggal_perolehan' => ['required', 'date'],
            'nilai_perolehan' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'kondisi' => ['required', Rule::in(['BAIK', 'PERLU_PERBAIKAN', 'RUSAK_BERAT'])],
        ]);
        AsetInventaris::create($data);

        return back()->with('success', 'Aset inventaris berhasil dicatat pada Unit Usaha yang dipilih.');
    }

    private function pastikanPengelola()
    {
        abort_unless(Auth::check() && in_array(Auth::user()->role, ['PENGELOLA', 'ADMIN'], true), 403, 'Halaman ini hanya tersedia untuk Pengelola BUMDes.');

        return Auth::user();
    }
}
