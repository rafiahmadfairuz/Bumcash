# Roadmap Pengembangan Bumcash

Roadmap mencatat status modul dan kriteria penyelesaian aplikasi dengan autentikasi dan pembatasan peran.

## Fase 1 — Fondasi, database, dan akun
- [x] Migrasi tabel pengguna, unit, layanan, tagihan, transaksi pembayaran, buku kas, modal, dan aset.
- [ ] Pastikan pembuatan akun dilakukan melalui proses terkontrol dan tidak menyertakan kata sandi bawaan pada deployment.
- [x] Relasi model dan pencatatan unit usaha.
- Selesai bila migrasi berjalan dan akun dengan peran yang sah dapat masuk.

## Fase 2 — Layanan warga
- [x] Dashboard warga, daftar dan detail tagihan.
- [x] Pengajuan informasi pembayaran dan riwayat.
- [ ] Bukti pembayaran hanya dapat dilihat setelah konfirmasi pengelola.
- Selesai bila warga hanya melihat data miliknya sendiri.

## Fase 3 — Pengelolaan keuangan
- [x] Verifikasi pembayaran dengan pencatatan kas atomik.
- [x] Penolakan beserta alasan tanpa penambahan kas.
- [x] Pencatatan kas manual terikat unit dan kategori.
- [x] Pengelolaan penyertaan modal dan aset.

## Fase 4 — Dashboard dan laporan
- [x] KPI, grafik, buku besar, dan filter.
- [x] Piutang dan tunggakan warga.
- [x] Laporan Musdes dan kalkulasi SHU/PADes.
- [ ] Cetak hitam-putih formal dan responsif di berbagai ukuran layar.

## Fase 5 — Kesiapan demo terautentikasi
- [ ] Halaman publik `/` menuju login; route privat dilindungi autentikasi.
- [ ] Middleware memisahkan akses peran pengelola dan warga.
- [ ] Profil hanya berisi identitas dan logout.
- [ ] Audit istilah, dropdown, tema, tautan, dan tampilan cetak.
- Selesai bila alur masuk, pembatasan lintas peran, pencatatan keuangan, dan logout tervalidasi.
