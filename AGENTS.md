# AGENTS.md — Panduan Utama Bumcash

## Tujuan
Bumcash adalah aplikasi web pengelolaan layanan dan keuangan BUMDes untuk digunakan oleh pengelola dan warga melalui akun terautentikasi. Gunakan Bahasa Indonesia yang jelas dan formal.

## Dokumen acuan
Sebelum mengubah alur bisnis atau data, rujuk `docs/PRD.md`, `docs/DATABASE_SCHEMA.md`, `docs/UI_DESIGN_SYSTEM.md`, dan `docs/TASK_ROADMAP.md`. Skema dan istilah pada dokumen-dokumen tersebut harus sejalan.

## Keamanan dan akses
- Semua halaman dan aksi pada `/pengelola/*` dan `/warga/*` wajib memakai middleware `auth` dan pemeriksaan peran.
- Peran `PENGELOLA` hanya dapat mengakses area pengelola; peran `WARGA` hanya dapat mengakses area warga. Tolak akses lintas peran dengan HTTP 403.
- Akun masuk hanya melalui form login. Jangan membuat endpoint, tombol, atau tautan yang menetapkan sesi pengguna atau mengganti peran tanpa verifikasi kredensial.
- Regenerasi sesi setelah login, invalidasi sesi saat logout, validasi input, lindungi permintaan perubahan dengan CSRF, dan jangan sertakan kredensial bawaan yang dapat dipakai pada deployment.
- Halaman `/` bersifat publik dan mengarahkan pengguna ke login.

## Integritas keuangan
- Tagihan belum lunas atau sedang menunggu konfirmasi merupakan piutang, bukan kas.
- Kas masuk dari layanan hanya dicatat setelah pembayaran diverifikasi. Perubahan status, konfirmasi, dan jurnal kas harus atomik dengan `DB::transaction` serta terhubung ke Unit Usaha dan Kategori Akun.
- Pencatatan kas manual wajib memiliki jenis, Unit Usaha, kategori, nominal, tanggal, keterangan, dan pencatat.
- Jangan mengintegrasikan layanan pembayaran, perbankan, atau sistem eksternal tanpa spesifikasi dan persetujuan eksplisit.

## Standar teknis dan tampilan
- Pisahkan kalkulasi laporan dari Blade ke service/helper yang sesuai.
- Pertahankan relasi, tipe data, dan terminologi sesuai `docs/DATABASE_SCHEMA.md`.
- Antarmuka responsif, mendukung Light/Dark Mode dan cetak formal, serta mengikuti token di `docs/UI_DESIGN_SYSTEM.md`.
- Jangan menampilkan kredensial atau data pribadi yang tidak diperlukan. Gunakan data produksi yang berasal dari akun dan pencatatan sah.
