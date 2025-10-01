Rapor Kokurikuler SMP

Aplikasi web responsif (mobile-first) untuk jurnal kokurikuler SMP. Bahasa Indonesia, ringan, dan aman.

Fitur
- Registrasi/Login (role: Guru, Kepala Sekolah, Pengawas)
- Guru pilih kelas saat registrasi
- Jurnal harian: tanggal, mapel, kelas, fokus dimensi DPL, tema, jenis kokurikuler, bentuk kegiatan, mapel terkait, progres per siswa, refleksi
- Autosave realtime
- Import siswa via CSV (nisn,nama,gender)
- Tombol "Kirim ke Kepala Sekolah"
- Kepala Sekolah: lihat detail, Print/Export PDF
- Pengawas: melihat semua jurnal sekolah

Tema & Footer
- Hijau Daun (#2e7d32) + putih
- Footer: "Developer : Muh Ichsan Tahun 2025-2026"

Struktur
- config.php, db.php, init.php, auth.php
- index.php, login.php, register.php, logout.php, dashboard.php
- guru_jurnal.php, import_siswa.php, jurnal_view.php
- api/jurnal_save.php, api/jurnal_submit.php
- templates/header.php, templates/footer.php
- public/css/style.css, public/js/app.js
- schema.sql

Kebutuhan
- PHP 8.0+
- MySQL 5.7+/MariaDB 10.3+
- Ekstensi pdo_mysql

Instalasi (Hosting)
1) Buat database MySQL dan import skema
   - mysql -u USER -p rapor_kokurikuler < schema.sql
2) Edit config.php: DB_HOST, DB_NAME, DB_USER, DB_PASS
3) Upload semua file ke public_html (atau dokumen root hosting)
4) Pastikan folder public/ dapat diakses (link CSS/JS sudah relatif)
5) Tambah data kelas awal (opsional)
   - INSERT INTO kelas (nama_kelas) VALUES ('VII A'),('VII B'),('VIII A'),('IX A');
6) Buka situs, registrasi akun sesuai peran

Keamanan
- CSRF token (form + header X-CSRF-Token)
- PDO prepared statements

Catatan
- Export PDF: gunakan dialog Print di halaman jurnal_view.php
- Warna dan tema mobile-first sudah diterapkan di public/css/style.css

# Ujian-Online