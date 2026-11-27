# Instruksi Codex: Implementasi Fondasi Payment Gateway Laravel

## Tujuan
Implementasikan fondasi payment gateway yang modular pada aplikasi Laravel BUMDes tanpa mengunci aplikasi pada provider tertentu. Jangan mengintegrasikan API provider nyata pada tahap ini. Siapkan struktur agar provider dapat ditambahkan kemudian tanpa mengubah alur transaksi utama secara besar-besaran.

## Aturan sebelum mulai
1. Audit struktur project, Laravel version, database (SQLite), model, migration, controller, route, middleware, role/permission, transaksi, dan UI pembayaran yang sudah ada.
2. Pertahankan fitur dan data yang sudah berjalan. Jangan melakukan rewrite besar tanpa alasan yang jelas.
3. Jangan menghapus, mengosongkan, atau mereset database. Jangan menjalankan migration destruktif.
4. Buat daftar temuan dan rencana perubahan singkat sebelum implementasi. Lanjutkan implementasi setelah audit tanpa menunggu persetujuan, kecuali ditemukan operasi destruktif atau perubahan yang tidak dapat dipulihkan.
5. Gunakan pola dan gaya kode project yang sudah ada. Hindari duplikasi alur pembayaran.

## Persyaratan implementasi

### 1. Konfigurasi environment
Buat konfigurasi provider-agnostic yang dikelola melalui `.env` dan `config/payment.php`. Gunakan nama variabel generik, contohnya:

```dotenv
PAYMENT_ENABLED=false
PAYMENT_MODE=development
PAYMENT_PROVIDER=mock
PAYMENT_CURRENCY=IDR
PAYMENT_WEBHOOK_SECRET=
PAYMENT_CALLBACK_URL=
PAYMENT_WEBHOOK_URL=
PAYMENT_TIMEOUT=30
```

Jangan menyimpan secret di source code. Jangan mencetak secret atau token ke log. Jika variabel tidak tersedia, pembayaran eksternal harus tetap nonaktif dengan aman.

### 2. Arsitektur modular
Implementasikan struktur yang sesuai dengan project, dengan konsep berikut (nama/path boleh menyesuaikan):

- Payment service sebagai entry point proses pembayaran.
- Interface/contract payment gateway.
- Adapter provider terpisah.
- Mock/sandbox adapter untuk pengujian lokal.
- Konfigurasi dan factory/manager pemilihan adapter.
- Status pembayaran dan pemetaan status provider.
- Validasi, exception handling, dan logging aman.

Contoh konsep:
- `PaymentGatewayInterface`
- `PaymentManager`
- `PaymentService`
- `MockPaymentGateway`

Jangan membuat implementasi yang bergantung pada satu provider tertentu.

### 3. Data pembayaran
Audit tabel yang ada terlebih dahulu. Gunakan tabel yang sudah ada jika sesuai. Hindari membuat tabel duplikat.

Data pembayaran harus dapat mencatat, jika relevan:
- ID transaksi internal.
- Referensi pembayaran internal yang unik.
- Provider dan metode pembayaran.
- Jumlah dan mata uang.
- Status internal.
- Referensi dari provider, bila tersedia.
- Waktu dibuat, dibayar, gagal, kedaluwarsa, atau dibatalkan.
- Metadata yang aman dan tidak memuat secret.
- Riwayat perubahan status/audit yang relevan.

Status internal minimal:
- `pending`
- `paid`
- `failed`
- `expired`
- `cancelled`
- `refunded` jika refund memang didukung oleh domain aplikasi.

Status internal harus menjadi sumber status yang konsisten. Jangan menerima status `paid` hanya dari request browser atau form pengguna.

### 4. Alur pembayaran
- Buat transaksi internal terlebih dahulu secara konsisten.
- Validasi nominal, referensi, dan kepemilikan transaksi.
- Pastikan status pembayaran tidak dapat berubah secara sembarangan.
- Gunakan database transaction untuk perubahan data yang saling terkait.
- Cegah pembayaran atau callback duplikat.
- Gunakan idempotency key atau mekanisme idempotensi yang sesuai.
- Pastikan perubahan status pembayaran dan pencatatan transaksi tidak menghasilkan data ganda.
- Jangan menganggap redirect pengguna kembali ke halaman aplikasi sebagai bukti pembayaran.

### 5. Webhook dan callback
Siapkan handler webhook provider-neutral yang nantinya dapat diadaptasi:
- Validasi signature atau mekanisme autentikasi callback sebelum mempercayai payload.
- Validasi referensi, nominal, mata uang, dan status.
- Cegah replay dan callback duplikat.
- Tangani callback yang datang terlambat atau tidak berurutan.
- Simpan audit/event dengan aman.
- Jangan expose stack trace, secret, atau data sensitif.
- Jika belum ada provider, mock webhook hanya untuk pengujian lokal.

Jangan mengarang metode signature universal. Implementasi signature harus berada pada adapter provider saat provider dipilih.

### 6. UI dan UX
- Tampilkan status pembayaran dengan jelas.
- Jangan menampilkan metode/provider yang belum dikonfigurasi.
- Ketika `PAYMENT_ENABLED=false`, nonaktifkan alur pembayaran eksternal dengan pesan yang informatif.
- Jangan mengubah alur transaksi yang tidak terkait pembayaran.
- UI harus membedakan status internal dan status provider jika diperlukan.

### 7. Keamanan
- Periksa authentication, authorization, policy, dan ownership setiap transaksi.
- Cegah IDOR dan manipulasi ID, nominal, status, atau referensi.
- Validasi input server-side.
- Lindungi webhook.
- Jangan log credential, token, signature, OTP, atau data sensitif.
- Jangan menyimpan data pembayaran sensitif yang tidak diperlukan.
- Jangan menganggap pembayaran sukses berdasarkan data client.
- Audit perubahan status dan aksi admin.
- Terapkan prinsip least privilege.

### 8. Pengujian
Buat atau perbarui test untuk:
- Mock payment initiation.
- Transaksi pending.
- Status paid, failed, expired, cancelled.
- Nominal atau referensi tidak valid.
- Callback/webhook invalid.
- Callback duplikat dan idempotensi.
- Akses tanpa izin.
- Pembayaran eksternal nonaktif.
- Konsistensi data transaksi dan payment record.

Gunakan SQLite sesuai konfigurasi project. Perhatikan keterbatasan SQLite pada concurrent locking dan perbedaan fitur dengan database lain.

### 9. Dokumentasi
Dokumentasikan:
- Arsitektur dan alur.
- Environment variable.
- Cara menjalankan mock/sandbox.
- Cara menguji.
- Cara menambahkan adapter provider baru.
- Batasan implementasi saat belum ada provider nyata.

## Batasan
- Jangan integrasikan API provider nyata.
- Jangan mengarang credential.
- Jangan mengaktifkan pembayaran eksternal secara default.
- Jangan melakukan migration destruktif.
- Jangan merusak alur transaksi, kas, order, atau fitur existing.
- Jangan menandai pembayaran lunas tanpa bukti valid dari mekanisme yang dipercaya.

## Validasi akhir
Jalankan test yang relevan, lint/check bila tersedia, dan audit perubahan. Laporkan:
1. Temuan audit.
2. File yang dibuat/diubah.
3. Migration yang dibuat dan dampaknya.
4. Test yang dijalankan dan hasilnya.
5. Cara mengaktifkan mock.
6. Risiko atau pekerjaan yang masih belum dilakukan.
