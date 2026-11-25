# UI & UX Design System — Bumcash

## 1. Arah visual
Gunakan tata letak fintech Clean Bento yang ringkas, responsif, mudah dipindai, dan nyaman pada layar ponsel maupun desktop. Pertahankan dukungan Light Mode, Dark Mode, dan gaya cetak formal. Kanvas terang bernuansa hangat, kartu memiliki sudut membulat, dan aksen amber digunakan secara terarah.

## 2. Warna dan tipografi
- Kanvas terang: `#FAF8F1`; permukaan: `#FFFFFF`; teks utama: `#1C1917`; teks sekunder: `#6B665C`; garis: `#ECE8DA`.
- Aksen utama: Amber Gold `#E09F09`, hover `#C78A05`; aksen Dark Mode `#F5B316`; teks CTA `#1A1302`.
- Kontras gelap: `#12110E`; permukaan bertingkat `#1B1915` dan `#24211B`; teks terang `#F5F2EA`.
- Kartu informasi dapat memakai gradasi butter yellow `#FEF3C7`, `#FDE68A`, `#FBD34D` dengan pasangan gradasi gelap yang tetap kontras.
- Dark Mode harus menggunakan latar gelap, permukaan bertingkat, teks terang, dan border kontras pada seluruh komponen, termasuk form, tabel, modal, dan kartu.
- Gunakan Plus Jakarta Sans dari Google Fonts. Skala teks mengikuti breakpoint bertahap; nominal memakai angka tabular dan judul tidak boleh terpotong.
- Warna status: belum dibayar merah lembut, menunggu konfirmasi amber, selesai hijau, ditolak slate, selalu disertai label teks.

## 3. Komponen dan ikon
- Gunakan ikon outline Lucide untuk UI yang sudah dibangun. Pertahankan ukuran responsif, `shrink-0`, dan label untuk kontrol yang hanya berupa ikon.
- Komponen utama: kartu KPI, badge unit usaha, badge status pembayaran, kartu saldo, modal aksi, tabel responsif, dan notifikasi hasil aksi.
- Tombol dan tautan interaktif harus memiliki target klik yang memadai, fokus keyboard terlihat, serta ikon dan teks sejajar.
- Dropdown harus dapat diakses keyboard dan klik, berada di atas konten lain, serta tertutup saat klik di luar atau Escape.

## 4. Tata letak
- Area pengelola memakai sidebar desktop yang dapat diciutkan dan drawer mobile dengan breakpoint konsisten `lg` (1024px). Tombol hamburger dan drawer hanya tampil di bawah breakpoint tersebut.
- Area warga memakai dashboard responsif dengan navigasi yang hanya menuju halaman terpisah.
- Kartu memakai padding adaptif, anak flex/grid diberi batas lebar minimum yang aman, dan tabel dapat digulir horizontal pada layar sempit.
- Profile dropdown hanya menampilkan identitas nama/peran dan aksi logout.

## 5. Cetak
Laporan dan bukti pembayaran memakai media print hitam-putih, tanpa navigasi, bayangan, dekorasi, atau warna latar. Sertakan judul dokumen, periode, tabel bergaris, tanggal cetak, serta blok tanda tangan bila relevan.
