# Bumcash — Catatan Serah Terima

## Route penting
- `GET /` — halaman informasi publik.
- `GET /login` dan `POST /login` — autentikasi.
- `GET /pengelola` — dashboard Pengelola (`pengelola.dashboard`).
- `GET /pengelola/unit-usaha` — manajemen Unit Usaha (`pengelola.unit-usaha.index`).
- `POST /pengelola/unit-usaha` — tambah Unit Usaha.
- `PUT /pengelola/unit-usaha/{unitUsaha}` — ubah detail dan status Unit Usaha.
- `DELETE /pengelola/unit-usaha/{unitUsaha}` — hapus unit yang belum memiliki catatan terkait.
- `POST /pengelola/unit-usaha/{unitUsaha}/layanan` — tambah layanan dan tarif.
- `PUT /pengelola/unit-usaha/{unitUsaha}/layanan/{layanan}` — ubah layanan dan tarif.
- `DELETE /pengelola/unit-usaha/{unitUsaha}/layanan/{layanan}` — hapus layanan yang belum dipakai pada tagihan.

Seluruh route `/pengelola/*` memakai `auth` dan role `PENGELOLA`/`ADMIN`. Menu sidebar desktop dan drawer mobile menuju halaman manajemen serta memiliki penanda aktif. Dashboard menyediakan tautan Kelola Unit.

## Catatan implementasi Unit Usaha
- Tampilan: `resources/views/pengelola/unit-usaha/index.blade.php`.
- Aksi CRUD: `app/Http/Controllers/UnitUsahaController.php`.
- Model relasi dan agregasi performa mengikuti `UnitUsaha`, `Layanan`, dan `ArusKas`.
- Rapor menampilkan pemasukan, pengeluaran, dan laba/rugi dari buku kas untuk setiap unit.
- Kode unit baru dibuat otomatis dan tetap unik.
- Unit dengan layanan, kas, modal, atau aset tidak dihapus; nonaktifkan unit untuk menjaga jejak catatan.
- Layanan yang sudah digunakan pada tagihan tidak dihapus. Perubahan tarif hanya memengaruhi tagihan yang dibuat setelah perubahan.
- Kolom `deskripsi` Unit Usaha ditambahkan lewat migrasi `2026_11_26_000003_add_description_to_unit_usaha.php`.

## Pemeriksaan saat perubahan
Jalankan `php artisan route:list -v` untuk memeriksa route dan middleware. Kompilasi Blade dapat diperiksa melalui `php artisan view:cache`.
