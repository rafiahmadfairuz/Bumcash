# Instruksi Codex: Audit, Perbaikan, dan Pengembangan Modul BUMDes Laravel

## Tujuan
Audit dan perbaiki modul BUMDes pada aplikasi Laravel yang sudah ada. Fokus pada bug CRUD unit usaha/layanan, histori tagihan, buku kas dan transaksi, pembayaran tagihan utilitas, pencatatan pembayaran oleh admin, serta pengelolaan piutang dan utang. Lakukan debugging menyeluruh dan jangan merusak fitur existing.

## Aturan sebelum mulai
1. Audit struktur project, versi Laravel, database SQLite, model, migration, controller, route, middleware, role/permission, UI, relasi, dan alur transaksi.
2. Reproduksi masalah dan identifikasi root cause sebelum mengubah kode.
3. Jangan mereset, menghapus, atau mengosongkan database.
4. Hindari migration destruktif. Jika perubahan schema berpotensi kehilangan data, berhenti dan jelaskan risiko serta minta persetujuan.
5. Gunakan struktur dan konvensi project yang ada. Jangan rewrite seluruh modul jika perbaikan terarah sudah cukup.
6. Buat ringkasan audit dan rencana perubahan, lalu implementasikan perubahan aman secara langsung.

## Fase 1: Audit CRUD unit usaha dan layanan
Audit halaman BUMDes dan seluruh alur:
- Menambah unit usaha atau layanan.
- Melihat detail.
- Mengedit.
- Menghapus atau mengarsipkan.
- Validasi form.
- Sinkronisasi UI dan database.
- Authorization dan role.
- Relasi dengan order, bill, transaksi, buku kas, dan histori.

Perbaiki bug yang ditemukan. Pastikan form edit memuat data yang tepat, validasi konsisten, perubahan tersimpan, dan feedback UI jelas.

## Fase 2: Penghapusan layanan tanpa merusak histori
Saat ini muncul pesan:
> This service has already been recorded on the bill. The service cannot be deleted to preserve the resident’s history.

Evaluasi pendekatan yang tepat dan implementasikan agar admin dapat menghapus layanan dari katalog aktif, sementara histori penghuni dan transaksi tetap terjaga.

Persyaratan:
- Jangan menghapus histori tagihan, pembayaran, transaksi, atau laporan.
- Gunakan soft delete, archive, status inactive, atau snapshot histori sesuai struktur existing.
- Layanan yang sudah digunakan dalam histori sebaiknya diarsipkan/nonaktif, bukan dihapus permanen dari database.
- Layanan archived tidak boleh dipilih untuk order/tagihan baru.
- Tagihan dan histori lama harus tetap menampilkan informasi layanan saat transaksi dibuat, meskipun katalog berubah.
- Jika restore diimplementasikan, pastikan restore aman dan tidak menyebabkan duplikasi.
- Jangan gunakan cascade delete untuk menghapus data finansial atau histori penghuni.
- Uji layanan yang belum pernah digunakan dan layanan yang sudah memiliki histori.

## Fase 3: Audit buku kas dan halaman transaksi
Audit UX dan logic buku kas serta halaman transaksi.

Pastikan:
- Data pemasukan dan pengeluaran akurat.
- Status transaksi dan payment status tidak saling membingungkan.
- Filter tanggal, kategori, unit usaha, status, dan pencarian bekerja jika tersedia.
- Detail transaksi dapat ditelusuri ke sumber transaksi.
- Data laporan tidak berubah akibat perubahan katalog layanan.
- Transaksi tidak tercatat ganda.
- Pembatalan, koreksi, dan refund (jika ada) memiliki histori/audit yang jelas.
- Pemasukan dan pengeluaran kas hanya dicatat ketika ada pergerakan kas yang relevan.
- Piutang dan utang tidak otomatis dianggap sebagai kas masuk atau kas keluar sebelum settlement.

Perbaiki UX: konsistensi istilah, navigasi, feedback, empty state, loading, error state, validasi, dan layout responsive.

## Fase 4: Pembayaran tagihan utilitas
Siapkan fitur pencatatan/pembayaran tagihan untuk:
- PLN/listrik.
- PDAM/air.
- PBB.

Tahap ini tidak memerlukan integrasi API provider nyata. Siapkan struktur modular agar integrasi dapat dilakukan kemudian.

Persyaratan:
- Kategori utilitas dan data tagihan.
- Nomor pelanggan/objek tagihan dan periode, jika relevan.
- Nominal, biaya tambahan jika sah dan jelas, status, referensi, tanggal, dan catatan.
- Status yang membedakan tagihan tercatat, pembayaran internal diproses, dan pembayaran eksternal benar-benar terkonfirmasi.
- Pencatatan bukti/attachment jika fitur upload sudah tersedia atau dapat ditambahkan dengan aman.
- Validasi data dan akses.
- Tidak boleh menyatakan tagihan provider sudah lunas tanpa bukti atau konfirmasi yang sah.
- Siapkan adapter/service untuk integrasi mendatang tanpa mengunci ke provider tertentu.
- Jangan menyimpan data sensitif yang tidak diperlukan.

## Fase 5: Pembayaran yang dicatat oleh admin
Admin harus dapat membantu atau mencatat pembayaran warga melalui satu alur input.

Persyaratan:
- Admin memilih warga dan tagihan/layanan yang relevan.
- Nominal, metode, referensi, waktu, dan status dicatat dengan jelas.
- Identitas admin yang melakukan pencatatan tercatat dalam audit.
- Pencatatan admin tidak boleh otomatis dianggap sebagai settlement provider eksternal jika belum diverifikasi.
- Cegah pencatatan ganda.
- Histori transaksi tetap terhubung dengan warga dan sumber tagihan.
- Jika ada pembayaran gabungan/batch, setiap tagihan harus tetap dapat ditelusuri secara individual.
- Pastikan admin hanya dapat melakukan tindakan sesuai role dan scope aksesnya.

## Fase 6: Unit usaha dan piutang/utang (Accounts Receivable / Accounts Payable)
Admin dapat menambahkan dan mengelola unit usaha yang memiliki pencatatan piutang dan utang.

Audit dan implementasikan sesuai model bisnis yang relevan:
- Daftar piutang dan utang.
- Pihak terkait.
- Referensi dokumen dan deskripsi.
- Tanggal transaksi, jatuh tempo jika relevan, nominal, saldo, status.
- Settlement penuh atau parsial jika diperlukan.
- Status overdue yang berdasarkan tanggal dan status aktual.
- Bukti transaksi dan audit trail.
- Filter dan laporan.
- Hubungan dengan unit usaha.
- Konsistensi dengan buku kas dan transaksi.

Aturan:
- Piutang tidak otomatis menjadi kas masuk.
- Utang tidak otomatis menjadi kas keluar.
- Kas masuk/keluar dicatat saat settlement aktual sesuai alur yang benar.
- Jangan menganggap seluruh transaksi BUMDes sebagai aktivitas lending/financing.
- Jangan menambahkan bunga, denda, credit scoring, atau fitur pinjaman tanpa dasar hukum, kebutuhan bisnis yang jelas, dan pemeriksaan hukum yang memadai.
- Jangan mengklaim model pencatatan sudah memenuhi standar akuntansi atau hukum tertentu tanpa validasi.

## Fase 7: Tinjauan regulasi
Periksa kebutuhan legal/regulasi secara hati-hati. Jangan menyatakan bahwa semua aktivitas BUMDes otomatis berada di bawah pengawasan OJK atau bahwa semua pencatatan piutang/utang merupakan aktivitas jasa keuangan.

Bedakan secara jelas:
- Piutang dagang dan utang dagang dari transaksi barang/jasa.
- Aktivitas pinjaman, pembiayaan, penghimpunan dana, atau aktivitas lain yang mungkin memiliki aturan khusus.

Gunakan sumber hukum resmi dan terkini jika melakukan klaim regulasi, seperti peraturan pemerintah, kementerian terkait, dan OJK sesuai konteks. Dokumentasikan sumber dan batasan. Jangan mengaktifkan fitur yang berpotensi regulated sebelum dasar hukum dan model bisnis diverifikasi.

## Fase 8: Security audit
Audit:
- Authentication dan authorization.
- Role dan permission.
- Policy.
- IDOR.
- Validasi server-side.
- CSRF.
- Upload file.
- XSS.
- Manipulasi nominal, status, referensi, dan data transaksi.
- Audit log.
- Kerahasiaan data warga.
- Akses admin dan scope unit usaha.

Jangan expose data warga atau transaksi kepada role yang tidak berwenang. Jangan log credential atau data sensitif.

## Fase 9: Debugging menyeluruh
Untuk setiap bug:
1. Reproduksi bug.
2. Periksa route dan request.
3. Periksa validation.
4. Periksa controller/service.
5. Periksa model dan relationship.
6. Periksa database serta migration.
7. Periksa authorization.
8. Periksa log Laravel.
9. Periksa browser console dan network request.
10. Periksa UI state dan response error.
11. Identifikasi root cause.
12. Perbaiki root cause, bukan hanya menyembunyikan error.
13. Tambahkan test regresi.

Jangan menghapus error handling hanya agar halaman terlihat berhasil.

## Fase 10: Testing
Tambahkan atau perbarui test yang relevan:
- CRUD unit usaha dan layanan.
- Edit dan delete/archive layanan yang sudah digunakan.
- Histori tagihan tetap tersedia.
- Layanan archived tidak tersedia untuk transaksi baru.
- Buku kas dan transaksi.
- Piutang dan utang serta settlement.
- Pembayaran utilitas.
- Pencatatan pembayaran oleh admin.
- Duplicate submission.
- Authorization dan IDOR.
- Validation.
- Konsistensi status dan histori.

Gunakan SQLite sesuai konfigurasi project. Pastikan test tidak merusak database development.

## Fase 11: Batasan
- Jangan mereset database.
- Jangan menghapus data histori atau transaksi.
- Jangan menjalankan migration destruktif tanpa persetujuan.
- Jangan membuat dummy data di database development tanpa izin.
- Jangan menonaktifkan security check untuk membuat fitur bekerja.
- Jangan mengklaim fitur sudah benar jika belum diuji.
- Jangan mengintegrasikan API provider nyata pada tahap ini.

## Fase 12: Validasi akhir dan laporan
Setelah implementasi:
- Jalankan test relevan.
- Jalankan pemeriksaan Laravel yang tersedia.
- Periksa migration.
- Periksa log.
- Uji alur end-to-end yang relevan.
- Pastikan fitur existing tidak rusak.

Laporkan:
1. Bug yang ditemukan dan root cause.
2. Perubahan yang dibuat.
3. File yang diubah.
4. Migration/schema changes dan risiko.
5. Test yang dijalankan serta hasilnya.
6. Fitur yang belum dapat diverifikasi.
7. Risiko atau rekomendasi lanjutan.
