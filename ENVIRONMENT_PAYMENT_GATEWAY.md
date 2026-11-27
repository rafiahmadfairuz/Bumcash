# Environment Payment Gateway Laravel (Provider-Agnostic)

## Tujuan
Menyiapkan environment pembayaran Laravel yang generik, tanpa memilih atau mengintegrasikan provider payment gateway tertentu. Konfigurasi ini menjadi fondasi untuk integrasi provider di kemudian hari.

## Contoh `.env`
Tambahkan konfigurasi berikut jika belum tersedia. Sesuaikan dengan kebutuhan implementasi dan jangan menduplikasi key yang sudah ada.

```dotenv
# Payment Gateway
PAYMENT_ENABLED=false
PAYMENT_MODE=development
PAYMENT_PROVIDER=mock
PAYMENT_CURRENCY=IDR

# URL
PAYMENT_CALLBACK_URL=
PAYMENT_WEBHOOK_URL=

# Security / credentials
PAYMENT_WEBHOOK_SECRET=
PAYMENT_API_KEY=
PAYMENT_API_SECRET=

# Operational settings
PAYMENT_TIMEOUT=30
PAYMENT_RETRY_ENABLED=false
```

## Arti konfigurasi

| Variable | Fungsi | Nilai awal yang disarankan |
|---|---|---|
| `PAYMENT_ENABLED` | Mengaktifkan atau menonaktifkan pembayaran eksternal | `false` |
| `PAYMENT_MODE` | Mode lingkungan | `development` |
| `PAYMENT_PROVIDER` | Adapter yang dipilih | `mock` |
| `PAYMENT_CURRENCY` | Mata uang | `IDR` |
| `PAYMENT_CALLBACK_URL` | URL redirect/callback pengguna jika provider mendukungnya | Kosong |
| `PAYMENT_WEBHOOK_URL` | Endpoint webhook aplikasi | Kosong, ditentukan dari routing |
| `PAYMENT_WEBHOOK_SECRET` | Secret verifikasi webhook, spesifik provider | Kosong |
| `PAYMENT_API_KEY` | API key provider, jika diperlukan | Kosong |
| `PAYMENT_API_SECRET` | Secret provider, jika diperlukan | Kosong |
| `PAYMENT_TIMEOUT` | Timeout request provider dalam detik | `30` |
| `PAYMENT_RETRY_ENABLED` | Pengaturan retry request, harus mempertimbangkan idempotensi | `false` |

## Catatan penting
1. Ini hanya konfigurasi generik. Provider tertentu mungkin memerlukan variable, format signature, metode autentikasi, atau konfigurasi tambahan.
2. Jangan menganggap semua provider memiliki API key dan API secret dengan pola yang sama.
3. Jangan menyimpan credential asli di repository, dokumentasi publik, screenshot, log, atau commit.
4. Gunakan environment development/sandbox provider ketika integrasi provider nyata nanti dibuat.
5. Jangan mengaktifkan pembayaran eksternal sebelum adapter, webhook verification, idempotency, dan pengujian selesai.
6. `PAYMENT_ENABLED=false` harus membuat alur pembayaran eksternal tidak aktif.
7. Mock provider hanya untuk development dan test. Jangan digunakan sebagai bukti pembayaran produksi.
8. Untuk deployment, atur variable melalui environment server atau secret management yang sesuai.
9. Hindari mengubah status pembayaran menjadi paid berdasarkan redirect browser.
10. SQLite merupakan database lokal yang digunakan saat ini. Periksa perilaku transaction, locking, migration, dan test sebelum mempertimbangkan deployment dengan concurrency tinggi.

## Langkah setelah mengubah `.env`
Jika Laravel menggunakan cache konfigurasi, jalankan:

```bash
php artisan config:clear
```

Jika aplikasi memakai configuration cache pada deployment, setelah konfigurasi benar:

```bash
php artisan config:cache
```

## Checklist sebelum integrasi provider nyata
- [ ] Provider dan model bisnis telah dipilih.
- [ ] Credential sandbox tersedia.
- [ ] Adapter provider terisolasi.
- [ ] Signature webhook diverifikasi sesuai dokumentasi provider.
- [ ] Nominal, referensi, dan status divalidasi.
- [ ] Idempotency dan duplicate callback ditangani.
- [ ] Error dan retry ditangani.
- [ ] Secret tidak masuk log atau repository.
- [ ] Status pembayaran tidak ditentukan oleh client.
- [ ] Test berhasil.
- [ ] Pembayaran eksternal baru diaktifkan setelah verifikasi.
