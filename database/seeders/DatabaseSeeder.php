<?php
namespace Database\Seeders;

use App\Models\{ArusKas,AsetInventaris,Layanan,PenyertaanModal,Tagihan,TransaksiPembayaran,UnitUsaha,User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $kataSandiAkunAwal = env('BUMCASH_SEED_PASSWORD');
        if (blank($kataSandiAkunAwal)) {
            throw new \RuntimeException('Seeder lokal memerlukan BUMCASH_SEED_PASSWORD.');
        }

        $pengelola=User::create(['nama_lengkap'=>'Rina Puspitasari','username'=>'pengelola','password'=>$kataSandiAkunAwal,'role'=>'PENGELOLA','alamat_dusun'=>'Kantor BUMDes']);
        $warga=[];
        foreach ([['WRG-001','Budi Santoso','budi'],['WRG-002','Siti Aminah','siti'],['WRG-003','Agus Setiawan','agus'],['WRG-004','Dewi Lestari','dewi'],['WRG-005','Joko Pranoto','joko']] as [$kode,$nama,$username]) {
            $warga[$username]=User::create(['kode_pelanggan'=>$kode,'nama_lengkap'=>$nama,'username'=>$username,'password'=>$kataSandiAkunAwal,'role'=>'WARGA','alamat_dusun'=>'RT 03 / RW 01, Dusun Krajan','no_telepon'=>'081234567890']);
        }
        $unitData=[
            ['UNIT-AIR','Unit Air Bersih (PAMSIMAS)','Slamet Riyadi'],['UNIT-SAMPAH','Unit Pengelolaan Sampah Desa','Wahyu Hidayat'],
            ['UNIT-SEWA','Unit Sewa Tenda & Alat Pesta','Dian Kurniawan'],['PUSAT','Manajemen Pusat BUMDes','Rina Puspitasari'],
        ];
        $units=[]; foreach($unitData as [$kode,$nama,$pj]) $units[$kode]=UnitUsaha::create(['kode_unit'=>$kode,'nama_unit'=>$nama,'penanggung_jawab'=>$pj,'status_aktif'=>true]);
        $air=Layanan::create(['unit_usaha_id'=>$units['UNIT-AIR']->id,'nama_layanan'=>'Iuran Bulanan Air Bersih PAMSIMAS','satuan'=>'Bulan','tarif_dasar'=>75000,'deskripsi'=>'Layanan air bersih rumah tangga.']);
        $sampah=Layanan::create(['unit_usaha_id'=>$units['UNIT-SAMPAH']->id,'nama_layanan'=>'Retribusi Sampah Rumah Tangga','satuan'=>'Bulan','tarif_dasar'=>30000,'deskripsi'=>'Pengangkutan sampah rumah tangga.']);
        $this->tagihanDanPembayaran($warga['budi'],$air,10,2026,'SELESAI','TRX-202610-001',$pengelola,75000);
        $this->tagihanDanPembayaran($warga['budi'],$air,11,2026,'SELESAI','TRX-202611-001',$pengelola,75000);
        $this->tagihanDanPembayaran($warga['siti'],$air,11,2026,'SELESAI','TRX-202611-002',$pengelola,82000);
        $this->tagihanDanPembayaran($warga['agus'],$air,11,2026,'MENUNGGU_KONFIRMASI','TRX-202611-003',$pengelola,75000);
        $this->tagihanDanPembayaran($warga['dewi'],$sampah,11,2026,'MENUNGGU_KONFIRMASI','TRX-202611-004',$pengelola,30000);
        $this->buatTagihan($warga['budi'],$air,9,2026,'BELUM_DIBAYAR');
        $this->buatTagihan($warga['joko'],$air,10,2026,'BELUM_DIBAYAR');
        $this->buatTagihan($warga['joko'],$air,11,2026,'BELUM_DIBAYAR');

        foreach ([
            ['2025-03-15','Penyertaan Modal APBDes TA 2025','Perdes No. 04 Tahun 2025',90000000,'Modal awal pengembangan unit usaha dan layanan desa.'],
            ['2026-02-10','Penyertaan Modal APBDes TA 2026','Perdes No. 02 Tahun 2026',60000000,'Penambahan modal untuk penguatan sarana layanan.'],
        ] as [$tanggal,$sumber,$perdes,$nominal,$keterangan]) PenyertaanModal::create(['unit_usaha_id'=>$sumber==='Penyertaan Modal APBDes TA 2025' ? $units['UNIT-AIR']->id : $units['PUSAT']->id,'tanggal_terima'=>$tanggal,'sumber_modal'=>$sumber,'nomor_perdes'=>$perdes,'nominal'=>$nominal,'keterangan'=>$keterangan]);
        foreach ([
            ['AST-AIR-001','UNIT-AIR','Mesin Pompa Submersible 3 HP','2025-04-02',35000000,'BAIK'],
            ['AST-SMP-001','UNIT-SAMPAH','Motor Roda Tiga Pengangkut Sampah','2025-04-18',28000000,'BAIK'],
            ['AST-SEWA-001','UNIT-SEWA','1 Set Tenda & 100 Kursi','2025-05-01',22000000,'PERLU_PERBAIKAN'],
            ['AST-PST-001','PUSAT','Komputer & Printer Kantor BUMDes','2025-05-10',9500000,'BAIK'],
        ] as [$kode,$unit,$nama,$tanggal,$nilai,$kondisi]) AsetInventaris::create(['kode_aset'=>$kode,'unit_usaha_id'=>$units[$unit]->id,'nama_aset'=>$nama,'tanggal_perolehan'=>$tanggal,'nilai_perolehan'=>$nilai,'kondisi'=>$kondisi]);

        $manual=[
            ['KAS-M-001','UNIT-AIR','2026-11-03','PEMASUKAN','Pendapatan Jasa Layanan','Penerimaan layanan sambungan baru',450000],
            ['KAS-M-002','UNIT-AIR','2026-11-06','PENGELUARAN','Beban Pemeliharaan Pipa/Alat','Penggantian sambungan pipa distribusi',325000],
            ['KAS-M-003','UNIT-SAMPAH','2026-11-08','PENGELUARAN','Beban BBM dan Operasional','BBM kendaraan pengangkut sampah',525000],
            ['KAS-M-004','UNIT-SEWA','2026-11-12','PEMASUKAN','Pendapatan Sewa Aset','Sewa tenda dan kursi kegiatan warga',1500000],
            ['KAS-M-005','PUSAT','2026-11-15','PENGELUARAN','Beban Gaji & Honor','Honor petugas administrasi BUMDes',950000],
            ['KAS-M-006','PUSAT','2026-11-17','PENGELUARAN','Beban ATK & Kantor','Pembelian perlengkapan administrasi',185000],
        ];
        foreach ($manual as [$kode,$unit,$tanggal,$jenis,$kategori,$keterangan,$nominal]) ArusKas::create(['kode_kas'=>$kode,'unit_usaha_id'=>$units[$unit]->id,'tanggal_transaksi'=>$tanggal,'jenis'=>$jenis,'sumber'=>'INPUT_MANUAL_PENGELOLA','kategori_akun'=>$kategori,'keterangan'=>$keterangan,'nominal'=>$nominal,'dicatat_oleh'=>$pengelola->id]);
    }

    private function buatTagihan(User $warga,Layanan $layanan,int $bulan,int $tahun,string $status): Tagihan
    {
        $nominal=(float)$layanan->tarif_dasar;
        return Tagihan::create(['kode_tagihan'=>sprintf('TGH-%04d%02d-%03d',$tahun,$bulan,Tagihan::count()+1),'user_id'=>$warga->id,'layanan_id'=>$layanan->id,'periode_bulan'=>$bulan,'periode_tahun'=>$tahun,'rincian_pemakaian'=>$layanan->nama_layanan.' - periode '.$bulan.'/'.$tahun,'nominal'=>$nominal,'jatuh_tempo'=>Carbon::create($tahun,$bulan,20),'status'=>$status]);
    }

    private function tagihanDanPembayaran(User $warga,Layanan $layanan,int $bulan,int $tahun,string $status,string $kode,User $pengelola,float $nominal): void
    {
        $tagihan=$this->buatTagihan($warga,$layanan,$bulan,$tahun,$status==='MENUNGGU_KONFIRMASI'?'MENUNGGU_KONFIRMASI':($status==='SELESAI'?'SELESAI':'BELUM_DIBAYAR'));
        $waktu=Carbon::create($tahun,$bulan,min(15,Carbon::create($tahun,$bulan,1)->daysInMonth),10);
        $transaksi=TransaksiPembayaran::create(['kode_transaksi'=>$kode,'tagihan_id'=>$tagihan->id,'user_id'=>$warga->id,'nominal_dibayar'=>$nominal,'metode_pembayaran'=>'TUNAI_LOKET','status'=>$status,'catatan_pengelola'=>$status==='SELESAI'?'Dikonfirmasi oleh Pengelola BUMDes.':null,'waktu_bayar'=>$waktu,'waktu_konfirmasi'=>$status==='SELESAI'?$waktu->copy()->addHours(2):null]);
        if ($status==='SELESAI') ArusKas::create(['kode_kas'=>'KAS-'.str_replace('TRX-','',$kode),'unit_usaha_id'=>$layanan->unit_usaha_id,'tanggal_transaksi'=>$waktu->toDateString(),'jenis'=>'PEMASUKAN','sumber'=>'OTOMATIS_LAYANAN_WARGA','kategori_akun'=>'Pendapatan Jasa Layanan','keterangan'=>'Pembayaran tagihan '.$tagihan->kode_tagihan.' telah dikonfirmasi.','nominal'=>$nominal,'transaksi_pembayaran_id'=>$transaksi->id,'dicatat_oleh'=>$pengelola->id]);
    }
}
