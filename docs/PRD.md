# Product Requirements Document — Bumcash
**Versi:** 3.0 — Aplikasi terautentikasi dengan kontrol akses berbasis peran

## 1. Tujuan
Bumcash mendukung layanan tagihan warga dan pengelolaan keuangan BUMDes multi-unit secara akuntabel. Halaman `/` memperkenalkan Bumcash dan layanan BUMDes kepada publik serta menyediakan tautan menuju `/login`.

## 2. Peran dan akses
- **Warga:** melihat tagihan milik akun sendiri, mengirim informasi pembayaran untuk diverifikasi, melihat riwayat dan bukti pembayaran yang sah.
- **Pengelola:** mengelola verifikasi pembayaran, buku kas, unit usaha, modal/aset, piutang, dashboard, serta laporan Musdes.
- Semua route area warga dan pengelola memerlukan login serta middleware peran. Warga tidak dapat melihat data warga lain; pengelola tidak mengakses portal warga. Login mengarahkan akun ke dashboard sesuai peran. Logout mengakhiri sesi.

## 3. Modul warga
1. Dashboard menampilkan profil pelanggan, ringkasan tagihan, layanan yang diikuti, dan daftar tagihan per unit.
2. Rincian tagihan menampilkan layanan, periode, pemakaian, nominal, jatuh tempo, dan status.
3. Warga menyampaikan metode pembayaran yang digunakan. Pengajuan tercatat sebagai `MENUNGGU_KONFIRMASI`; sistem tidak menyatakan kas diterima sebelum pengelola memverifikasi.
4. Riwayat menampilkan status transaksi. Bukti pembayaran sah hanya tersedia setelah status `SELESAI`.

## 4. Modul pengelola
1. Dashboard menampilkan saldo buku kas, pemasukan, pengeluaran, piutang, rasio penagihan, dan kinerja unit.
2. Pengelola memeriksa pengajuan yang menunggu. Konfirmasi atomik memperbarui tagihan dan transaksi menjadi `SELESAI`, serta mencatat pemasukan ke buku kas pada Unit Usaha layanan terkait. Penolakan menyimpan alasan, mengubah transaksi menjadi `DITOLAK`, dan mengembalikan tagihan ke `BELUM_DIBAYAR` tanpa jurnal pemasukan.
3. Pencatatan kas manual mewajibkan Unit Usaha, jenis arus, Kategori Akun, tanggal, nominal, uraian, dan pencatat.
4. Pengelolaan mencakup Penyertaan Modal Desa dan aset inventaris yang teralokasi ke Unit Usaha.
5. Manajemen Unit Usaha menyediakan pembuatan, perubahan, penonaktifan/pengaktifan, serta penghapusan aman untuk Unit Usaha dan layanan beserta tarifnya. Unit yang sudah memiliki transaksi/layanan/aset/modal tidak dihapus agar riwayat keuangan tetap utuh.
6. Laporan menyajikan laba/rugi per unit, piutang, rekap modal/aset, serta alokasi SHU/PADes menurut persentase AD/ART yang ditetapkan.

## 5. Aturan keuangan
- Pendapatan unit = jumlah jurnal `PEMASUKAN` untuk unit dan periode.
- Beban unit = jumlah jurnal `PENGELUARAN` untuk unit dan periode.
- Laba bersih = seluruh pemasukan dikurangi seluruh pengeluaran.
- Piutang = nominal tagihan yang belum berstatus `SELESAI`.
- Collection rate = nominal tagihan `SELESAI` dibagi nominal seluruh tagihan terbit dalam periode, dikali 100%.
- Konfirmasi dan jurnal terkait harus berlangsung dalam satu transaksi basis data agar tidak terjadi pencatatan ganda.

## 6. Keamanan
Autentikasi wajib memakai kata sandi tersimpan dalam bentuk hash, regenerasi sesi saat login, CSRF untuk perubahan data, pembatasan akses berdasarkan peran, dan pembatasan data warga berdasarkan pemilik akun. Deployment tidak boleh menyediakan akun atau kata sandi bawaan.
