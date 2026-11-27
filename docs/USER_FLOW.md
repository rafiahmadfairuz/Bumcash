# User Flow Bumcash

Ilustrasi berikut merangkum alur publik, akun warga, dan pengelola sesuai rute aplikasi saat ini.

```mermaid
flowchart TD
    A[Halaman utama /] --> B[Login]
    A --> C[Daftar warga]
    B --> D{Kredensial valid?}
    D -- Tidak --> B
    D -- Ya --> E{Peran akun}
    E -- WARGA dengan email belum terverifikasi --> F[Verifikasi email]
    F -->|Tautan email sah| G[Dashboard warga]
    F -->|Kirim ulang| F
    E -- WARGA tanpa email / sudah terverifikasi --> G
    E -- PENGELOLA atau ADMIN --> H[Dashboard pengelola]
    C --> I[Isi nama, username, email, kata sandi]
    I --> J{Validasi berhasil?}
    J -- Tidak --> I
    J -- Ya --> K[Akun WARGA dibuat]
    K --> F
    B --> L[Lupa kata sandi]
    L --> M[Masukkan email]
    M --> N{Email terdaftar?}
    N -- Tidak --> O[Pesan netral]
    N -- Ya --> P[Email tautan reset]
    P --> Q[Buka tautan bertoken]
    Q --> R{Token masih sah?}
    R -- Tidak --> L
    R -- Ya --> S[Atur kata sandi baru]
    S --> B

    G --> T[Lihat dashboard dan tagihan milik sendiri]
    T --> U[Detail tagihan]
    U --> V[Ajukan informasi pembayaran]
    V --> W[Menunggu konfirmasi pengelola]
    W --> X{Hasil verifikasi}
    X -- Disetujui --> Y[Tagihan lunas dan bukti tersedia]
    X -- Ditolak --> Z[Tagihan kembali belum dibayar]
    G --> AA[Riwayat transaksi]
    AA --> Y
    G --> AB[Logout]

    H --> AC[Dashboard keuangan]
    H --> AD[Verifikasi pembayaran]
    AD --> W
    H --> AE[Catat arus kas manual]
    H --> AF[Kelola modal dan aset]
    H --> AG[Lihat piutang dan laporan Musdes]
    H --> AB
    AB --> A
```

## Ringkasan alur

- **Pengunjung:** halaman utama → login atau daftar sebagai warga. Registrasi tidak menyediakan pilihan peran.
- **Warga:** verifikasi email jika akun memiliki email → dashboard → detail tagihan → ajukan pembayaran → tunggu verifikasi → lihat status dan bukti pembayaran jika disetujui.
- **Pengelola:** login → dashboard → verifikasi pembayaran, pencatatan kas, pengelolaan modal/aset, piutang, dan laporan.
- **Pemulihan akun:** minta tautan reset lewat email → buka token yang masih berlaku → tetapkan kata sandi baru → login kembali.
- **Sesi:** logout mengakhiri sesi dan kembali ke halaman utama.

> Catatan: role ADMIN mengikuti akses pengelola pada rute saat ini. Akun warga yang tidak memiliki email (misalnya data seeder lama) dapat melewati verifikasi email.
