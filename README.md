# 🗓️ SmartSched: Sistem Informasi Penjadwalan Guru Otomatis

[![Laravel Version](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg)](CONTRIBUTING.md)

**SmartSched** adalah aplikasi sistem informasi berbasis web yang dirancang untuk menyelesaikan masalah kompleks dalam penyusunan jadwal pelajaran di sekolah. Dengan memanfaatkan algoritma optimasi (seperti _Genetic Algorithm_ / Algoritma Genetika), aplikasi ini mampu menghasilkan jadwal bentrok-nol secara otomatis, memperhatikan ketersediaan guru, ruang kelas, batas jam mengajar, serta preferensi waktu berhalangan.

---

## ✨ Fitur Unggulan

- **⚡ Otomatisasi Penjadwalan (Auto-Generate):** Menyusun ribuan kombinasi jadwal hanya dengan satu klik menggunakan algoritma cerdas tanpa risiko bentrok (_Zero-Conflict_).
- **🔒 Manajemen Hak Akses (Multi-user):** Pembagian peran yang jelas untuk **Admin/Kurikulum** (mengelola data & generate jadwal) dan **Guru** (melihat jadwal & mengajukan waktu berhalangan).
- **🛠️ Manajemen Data Master Fleksibel:** Pengelolaan data Guru, Mata Pelajaran, Kelas, Ruangan, dan Tahun Akademik secara dinamis.
- **📅 Batasan & Parameter Kustom (Constraints):**
    - Maksimum jam mengajar per hari untuk tiap guru.
    - Kombinasi hari dan jam berhalangan bagi guru tertentu.
    - Penguncian ruangan khusus (misal: Laboratorium, Lapangan).
- **📊 Dasbor Analitik:** Visualisasi total jam mengajar, jumlah guru aktif, dan status kesiapan jadwal dalam bentuk grafik interaktif.
- **📥 Ekspor Laporan Mudah:** Cetak dan ekspor hasil jadwal pelajaran ke format **PDF** dan **Excel** per kelas atau per guru.

---

## 🛠️ Teknologi yang Digunakan

Aplikasi ini dibangun menggunakan _modern web stack_ untuk memastikan performa tinggi dan kemudahan pemeliharaan:

- **Backend Framework:** [Laravel 11](https://laravel.com)
- **Frontend Interface:** [Tailwind CSS](https://tailwindcss.com) & [Livewire](https://livewire.laravel.com) / [FilamentPHP](https://filamentphp.com)
- **Database:** MySQL / PostgreSQL
- **In-Memory Cache (Optional):** Redis (untuk mempercepat komputasi pencarian jadwal)
- **Package Pendukung:** Spatie Laravel Permission (Manajemen Role), Maatwebsite Excel (Ekspor Data).

---

## 🚀 Panduan Penginstalan (Installation Guide)

Ikuti langkah-langkah berikut untuk menjalankan aplikasi **SmartSched** di lingkungan lokal Anda:

### 📋 Prasyarat Sistem

Pastikan perangkat Anda sudah terinstal:

- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL Server

### 🛠️ Langkah-Langkah Instalasi

1.  **Kloning Repositori**

    ```bash
    git clone https://github.com/username/smartsched.git
    cd smartsched
    ```

2.  **Instalasi Dependensi PHP**

    ```bash
    composer install
    ```

3.  **Instalasi Dependensi Frontend**

    ```bash
    npm install
    npm run build
    ```

4.  **Konfigurasi Environment (`.env`)**
    Salin file `.env.example` menjadi `.env`:

    ```bash
    cp .env.example .env
    ```

    Buka file `.env` menggunakan teks editor pilihan Anda, kemudian sesuaikan konfigurasi database Anda:

    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=db_smartsched
    DB_USERNAME=root
    DB_PASSWORD=
    ```

5.  **Generate Application Key**

    ```bash
    php artisan key:generate
    ```

6.  **Migrasi Database & Seeding Data Utama**
    Jalankan perintah ini untuk membuat struktur tabel beserta data awal (_default admin_, waktu hari, dan jam pelajaran):

    ```bash
    php artisan migrate --seed
    ```

7.  **Membuat Storage Link**
    Hubungkan folder storage agar file media/foto profil guru dapat diakses secara publik:

    ```bash
    php artisan storage:link
    ```

8.  **Menjalankan Server Lokal**
    Aplikasi Anda sekarang siap digunakan! Jalankan perintah berikut untuk memulai server development:
    ```bash
    php artisan serve
    ```
    Buka browser Anda dan akses tautan: `http://127.0.0.1:8000`

---

## 💡 Cara Penggunaan Singkat

1.  **Login ke Sistem:** Gunakan akun admin hasil _seeding_ awal (misal: `admin@smartsched.com` / password: `password`).
2.  **Input Data Master:** Masukkan data Ruangan, Kelas, Mata Pelajaran, dan Guru beserta beban jam mengajarnya.
3.  **Atur Batasan (Constraints):** Masukkan waktu berhalangan guru jika ada.
4.  **Generate Jadwal:** Masuk ke menu _Penjadwalan_, pilih Tahun Akademik/Semester, lalu klik tombol **"Generate Jadwal"**. Sistem akan memproses alokasi terbaik secara otomatis.
5.  **Publikasi:** Setelah hasil keluar dan divalidasi, klik _Publish_ agar jadwal dapat dilihat oleh seluruh guru dan siswa.

---

## 🤝 Kontribusi

Kontribusi selalu terbuka! Jika Anda ingin meningkatkan performa algoritma atau menambahkan fitur baru, silakan lakukan langkah berikut:

1. Fork Repositori ini.
2. Buat fitur Branch baru (`git checkout -b fitur/FiturKeren`).
3. Lakukan Commit perubahan Anda (`git commit -m 'Menambahkan fitur keren'`).
4. Push ke Branch tersebut (`git push origin fitur/FiturKeren`).
5. Buat _Pull Request_.

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah **Lisensi MIT** - lihat file [LICENSE](LICENSE) untuk detail lebih lanjut.

---

_Dibuat dengan 💻 dan ☕ untuk kemajuan efisiensi administrasi pendidikan._
