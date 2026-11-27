# Skema Database Bumcash
Sumber acuan untuk tabel, kolom, relasi, status, dan aturan penyimpanan.

## 1. Tabel dan kolom

### `users`
`id`, `kode_pelanggan` nullable, `nama_lengkap`, `username` unique, `password` hashed, `role` enum (`PENGELOLA`, `ADMIN`, `WARGA`), `alamat_dusun`, `no_telepon` nullable, timestamps.

### `unit_usaha`
`id`, `kode_unit` unique, `nama_unit`, `penanggung_jawab`, `deskripsi` text nullable, `status_aktif` boolean.

### `layanan`
`id`, `unit_usaha_id` FK, `nama_layanan`, `satuan`, `tarif_dasar` decimal(12,2), `deskripsi` text.

### `tagihan`
`id`, `kode_tagihan` unique, `user_id` FK, `layanan_id` FK, `periode_bulan` integer 1–12, `periode_tahun`, `rincian_pemakaian`, `nominal` decimal(12,2), `jatuh_tempo` date, `status` enum (`BELUM_DIBAYAR`, `MENUNGGU_KONFIRMASI`, `SELESAI`), timestamps.

### `transaksi_pembayaran`
`id`, `kode_transaksi` unique, `tagihan_id` FK, `user_id` FK, `nominal_dibayar` decimal(12,2), `metode_pembayaran` enum (`TUNAI_LOKET`, `TRANSFER_BANK_DESA`), `status` enum (`MENUNGGU_KONFIRMASI`, `SELESAI`, `DITOLAK`), `catatan_pengelola` nullable, `waktu_bayar`, `waktu_konfirmasi` nullable, timestamps. Transaksi yang berhasil diverifikasi terhubung ke paling banyak satu jurnal kas.

### `arus_kas`
`id`, `kode_kas` unique, `unit_usaha_id` FK, `tanggal_transaksi` date, `jenis` enum (`PEMASUKAN`, `PENGELUARAN`), `sumber` enum (`OTOMATIS_LAYANAN_WARGA`, `INPUT_MANUAL_PENGELOLA`), `kategori_akun`, `keterangan`, `nominal` decimal(12,2) positif, `transaksi_pembayaran_id` nullable FK unique, `dicatat_oleh` FK pengguna pengelola.

### `penyertaan_modal`
`id`, `unit_usaha_id` nullable FK, `tanggal_terima`, `sumber_modal`, `nomor_perdes` nullable, `nominal` decimal(12,2), `keterangan`.

### `aset_inventaris`
`id`, `kode_aset` unique, `unit_usaha_id` FK, `nama_aset`, `tanggal_perolehan`, `nilai_perolehan` decimal(12,2), `kondisi` enum (`BAIK`, `PERLU_PERBAIKAN`, `RUSAK_BERAT`).

### `payment_transactions` dan `payment_webhook_events`
Gateway provider-agnostic yang additive dan nonaktif secara default. Amount decimal, ID internal UUID, referensi provider nullable, idempotency key unik per tagihan. Webhook event unik per provider/event ID. Lihat `docs/PAYMENT_INFRASTRUCTURE.md`.

### `tagihan_utilitas`
Catatan tagihan PLN/PDAM/PBB dengan Unit Usaha, penyedia, nomor objek opsional, periode, nominal, biaya tambahan, tanggal, tempo, status pencatatan dan referensi pembayaran. Pencatatan awal membuat utang usaha, bukan jurnal kas.

### `piutang_usaha`, `utang_usaha`, `settlement_usaha`
Piutang/utang dagang mencatat pihak, referensi dokumen, deskripsi, unit, tanggal, tempo, nominal dan saldo. Settlement dapat parsial; setiap settlement membuat jurnal kas dan audit petugas secara atomik. Tabel settlement menunjuk entitas melalui pasangan `jenis_entitas`/`entitas_id` (polymorphic tanpa FK langsung), sementara `arus_kas_id` memiliki FK unik.

### `transaksi_pembayaran` (audit pencatatan pengelola)
Kolom tambahan `dicatat_oleh` nullable untuk mempertahankan transaksi lama dan `referensi_admin` nullable unique untuk idempotency/manual audit.

## 2. Relasi
- Unit Usaha memiliki banyak layanan, jurnal kas, modal, dan aset.
- Layanan memiliki banyak tagihan; warga memiliki banyak tagihan dan transaksi.
- Tagihan memiliki banyak transaksi pembayaran; transaksi berhasil dapat memiliki satu jurnal kas.
- Pengelola memiliki banyak jurnal kas yang dicatat.
- Tagihan utilitas memiliki satu utang usaha; settlement utang utilitas memperbarui status tagihan.

## 3. Integritas
Tagihan dan transaksi warga harus konsisten statusnya. Jurnal kas layanan hanya dibuat pada verifikasi sukses, wajib merujuk ke Unit Usaha yang menyediakan layanan, dan tidak boleh tercatat lebih dari sekali untuk transaksi yang sama. Penolakan tidak membuat jurnal pemasukan. Nominal kas disimpan positif dan arah transaksi ditentukan oleh `jenis`.
