# Sistem Monitoring Punch List Berbasis Web
### Mengurangi Keterlambatan Penyelesaian Masa Pemeliharaan Proyek Konstruksi

Aplikasi web manajemen dan monitoring daftar punch list proyek konstruksi yang dibangun secara utuh dan terstruktur menggunakan **Laravel 12**, **SQLite**, **Blade**, dan **AdminLTE v3 (Bootstrap 4)**.

---

## 1. Identitas & Latar Belakang Proyek

- **Judul Proyek Capstone:**  
  *Perancangan Sistem Monitoring Punch List Berbasis Web untuk Mengurangi Keterlambatan Penyelesaian Masa Pemeliharaan Proyek Konstruksi*
- **Stakeholder Utama:**
  1. **Manajemen Kontraktor Utama**: Memantau perkembangan masa pemeliharaan, memantau risiko keterlambatan, mengevaluasi kinerja teknisi, dan melakukan verifikasi hasil perbaikan.
  2. **Tim Pemeliharaan Lapangan (PIC)**: Menerima penugasan temuan punch list, menindaklanjuti perbaikan fisik, mengunggah dokumentasi bukti pekerjaan, dan mengajukan verifikasi.
- **Tujuan Sistem:**
  - Memusatkan seluruh data catatan punch list ke dalam satu platform daring terpadu.
  - Memantau masa pemeliharaan dan batas tenggat penyelesaian secara dinamis berdasarkan zona waktu `Asia/Jakarta`.
  - Mencegah temuan punch list yang terlambat atau terbengkalai hingga melewati batas resmi masa garansi pemeliharaan.
  - Menyediakan dokumentasi foto komparasi (*sebelum* dan *setelah* perbaikan) serta riwayat audit (*audit trail*).
  - Menyediakan pelaporan analitis serta ekspor data ke format CSV / Microsoft Excel.

---

## 2. Teknologi yang Digunakan

- **Backend Framework:** Laravel 12.x (PHP 8.2+)
- **Basis Data:** SQLite (Penyimpanan persisten lokal & foreign keys aktif)
- **Frontend Template:** AdminLTE v3.2 (Bootstrap 4.6 via CDN)
- **Ikon & Font:** Font Awesome 5 Free & Google Fonts Source Sans Pro
- **Grafik Interaktif:** Chart.js 3.9
- **Kalender Interaktif:** FullCalendar 5.11
- **Testing Framework:** PHPUnit 11 (16 Feature Tests Otomatis)
- **Zona Waktu & Bahasa:** `Asia/Jakarta` (WIB) & Bahasa Indonesia (`id`)

---

## 3. Akun Pengguna Demo Pengembangan

Aplikasi telah dilengkapi seeder data autentik dari dokumen **Capstone.docx** (15 item temuan nyata airfield lighting & kabel grounding trafo).

| Peran (Role) | Email Login | Kata Sandi Demo | Hak Akses Utama |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@punchlist.test` | `password` | Mengelola semua data punch list, tambah/edit/hapus, manajemen pengguna, log aktivitas, pengaturan sistem. |
| **Manajemen Kontraktor** | `manajemen@punchlist.test` | `password` | Monitoring masa pemeliharaan, verifikasi hasil pekerjaan (*approve/reject*), laporan analitis, ekspor data. |
| **Tim Lapangan (Anton)** | `anton@punchlist.test` | `password` | Mengakses tugas yang ditugaskan kepadanya, ubah status kerja, unggah foto bukti perbaikan, ajukan verifikasi. |
| **Tim Lapangan (Adul)** | `adul@punchlist.test` | `password` | PIC perbaikan konektor kabel pit box bandara. |
| **Tim Lapangan (Lala)** | `lala@punchlist.test` | `password` | PIC terminasi grounding trafo unit 1. |

> **Catatan Keamanan:** Kata sandi demo di atas hanya diperuntukkan bagi pengujian lokal. Sebelum melakukan publikasi ke server daring, ganti seluruh kata sandi akun melalui menu **Profil Saya** atau jalankan seeder dengan kredensial acak.

---

## 4. Panduan Menjalankan Aplikasi di Lingkungan Lokal

### Prasyarat:
- PHP versi 8.2 atau lebih tinggi dengan ekstensi `pdo_sqlite`, `sqlite3`, `mbstring`, `fileinfo`, `gd`.
- Composer 2.x

### Langkah Menjalankan:

1. **Buka Terminal / PowerShell** pada direktori workspace:
   ```powershell
   cd "c:\Tugas\Punch List"
   ```

2. **Salin Environment & Pasang Dependensi (jika belum dilakukan):**
   ```powershell
   Copy-Item .env.example .env
   composer install
   php artisan key:generate
   ```

3. **Jalankan Migrasi & Seeder Database SQLite:**
   ```powershell
   php artisan migrate:fresh --seed
   ```
   *(Perintah ini akan membuat database SQLite, 10 tabel sistem, 17 akun pengguna, proyek AFL bandara, dan 16 catatan punch list beserta berkas gambar asli dari dokumen capstone).*

4. **Hubungkan Symlink Penyimpanan Berkas:**
   ```powershell
   php artisan storage:link
   ```

5. **Jalankan Server Pengembangan Lokal:**
   ```powershell
   php artisan serve
   ```
   Buka peramban (browser) dan akses alamat:
   **`http://127.0.0.1:8000`**

---

## 5. Fitur Utama yang Telah Diimplementasikan & Diverifikasi

1. **Dashboard Monitoring Real-Time:**
   - 8 kartu ringkasan metrik: Total Catatan, Baru, Dalam Proses, Menunggu Verifikasi, Selesai, Terlambat Aktif, Prioritas Kritis/Tinggi, dan Tingkat Penyelesaian (%).
   - Tiga grafik analitis Chart.js: Distribusi Status, Distribusi Tingkat Prioritas, dan Tren Bulanan Temuan vs Penyelesaian.
   - Tabel peringatan pekerjaan paling mendesak & keterlambatan aktif.
2. **CRUD Punch List & Penomoran Otomatis:**
   - Format kode unik terstandarisasi: `PL-YYYY-XXXX` (contoh: `PL-2026-0001`).
   - Pencarian kata kunci, penyaringan multi-parameter (proyek, PIC, status, prioritas, rentang tanggal), serta pengurutan dinamis.
3. **Logika Monitoring Tenggat Waktu & Zona Waktu:**
   - Menghitung status berdasarkan tanggal aktual `Asia/Jakarta`: *Aman*, *Mendekati Tenggat (kuning)*, *Terlambat (merah)*, dan *Selesai*.
   - Ambang peringatan dapat diubah fleksibel melalui menu Pengaturan Sistem (default: 3 hari).
4. **Alur Kerja (Workflow) Verifikasi Ketat:**
   - Lapangan mengubah status ke *Dalam Proses* &rarr; Mengunggah bukti fisik &rarr; Mengajukan *Menunggu Verifikasi*.
   - Hanya Manajemen / Admin yang berhak menyetujui menjadi *Selesai* atau *Menolak Verifikasi* disertai alasan perbaikan lanjutan.
5. **Dokumentasi Fisik Temuan & Bukti Tuntas:**
   - Galeri komparasi berdampingan: *Foto Sebelum Perbaikan* vs *Foto Setelah Perbaikan*.
   - Validasi file (JPG/PNG/PDF maks 10MB) dan perlindungan unduhan.
6. **Kalender Tenggat Waktu (FullCalendar):**
   - Tampilan visual bulanan interaktif dengan kode warna status pekerjaan dan modal ringkasan langsung.
7. **Laporan, Versi Cetak, dan Ekspor CSV Excel:**
   - Filter parameter laporan, tampilan cetak ramah browser (print media), serta unduhan CSV dengan sanitasi formula Excel (*CSV Injection Prevention*).
8. **Notifikasi Internal & Log Audit (Activity Logs):**
   - Notifikasi otomatis penugasan, peringatan keterlambatan harian, dan pembaruan verifikasi.
   - Pencatatan seluruh mutasi data (*audit trail*) beserta alamat IP pengguna.

---

## 6. Menjalankan Pengujian Otomatis (Automated Tests)

Aplikasi telah dilengkapi **16 skenario pengujian otomatis** fitur inti menggunakan PHPUnit:

```powershell
php artisan test --filter=PunchListSystemTest
```

**Hasil Pengujian:**
```
✓ user can login successfully
✓ unauthenticated user cannot access internal pages
✓ role based access control
✓ authorized user can create punch list
✓ validation fails when required fields missing
✓ authorized user can update punch list
✓ only admin can delete punch list
✓ search and filter punch lists
✓ completion percentage calculation
✓ overdue calculation logic
✓ workflow status transitions
✓ verification rejection workflow
✓ valid document upload
✓ invalid document upload rejected
✓ report csv export
✓ activity log recorded

Tests: 16 passed (45 assertions)
```

---

## 7. Panduan Deployment Daring (GitHub & Hosting)

Panduan lengkap mengenai persiapan hosting VPS, Nginx, SSL, persistent disk volume, dan backup database SQLite dapat dibaca secara terperinci pada dokumen [**DEPLOYMENT.md**](file:///c:/Tugas/Punch%20List/DEPLOYMENT.md).

Pemeriksaan CI otomatis untuk GitHub Actions telah disiapkan pada direktori [`.github/workflows/laravel-tests.yml`](file:///c:/Tugas/Punch%20List/.github/workflows/laravel-tests.yml).
