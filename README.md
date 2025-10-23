# TataKata: Big Project Kelompok 6 PBKK C 2025

## Anggota Kelompok 6
|    NRP     |      Nama      |
| :--------: | :------------: |
| 5025231041 | Amelia Nova Safitri |
| 5025231043 | Tarisha Falah Basuki |
| 5025231045 | Putriani Pirma A S |
| 5025231059 | Dinda Ayu Ningratu P |
| 5025231134 | Hana Azizah Nurhadi |
| 5025231138 | Aqila Zahira Naia P A |
| 5025231146 | Salsabila Purnama |
| 5025231307 | Rosidah Darman |

## Deskripsi
  TataKata adalah website berbasis AI yang membantu mahasiswa dalam menyempurnakan dokumen tugas akhir mereka agar sesuai dengan kaidah bahasa Indonesia yang baik dan benar. Dengan teknologi pemrosesan bahasa alami (NLP), TataKata mampu memeriksa kata baku, kalimat efektif, tanda baca, imbuhan, serta ejaan untuk memastikan tulisan sesuai dengan kaidah bahasa Indonesia. Selain itu, TataKata juga dilengkapi dengan fitur deteksi salah ketik (typo) dan translasi ke bahasa Indonesia sehingga sangat membantu mahasiswa.
Website ini dirancang agar mudah digunakan. Mahasiswa cukup mendaftar dan login menggunakan akun email yang dimiliki. Lalu pengguna bisa memasukkan teks atau PDF, lalu sistem AI akan langsung memberikan analisis serta saran perbaikan yang detail. Tujuannya bukan hanya mengoreksi, tetapi juga mendidik pengguna agar lebih peka terhadap penggunaan bahasa Indonesia yang baik dan benar.

## How to Run
1. Jalankan instalasi `composer install` untuk backend dan `npm install` `npm run build` untuk frontend.
2. Copy konfigurasi **.env** dengan command `cp -n .env.example .env` untuk Linux/macOS dan `Copy-Item .env.example .env` untuk Windows.
3. Jalankan command `php artisan key:generate` untuk enkripsi data.
4. Jalankan command `php artisan migrate:fresh` untuk ekspor database **mysql** ke sistem backend.
5. Jalankan command `php artisan storage:link` untuk menyimpan dokumen.
6. Sediakan 2 terminal untuk run program pada folder **Laravel**.
7. Jalankan command `npm run dev` pada terminal 1 untuk compile aset frontend.
8. Jalankan command `php artisan serve && php artisan queue:work --tries=3 --timeout=300` pada terminal 2 untuk mengakses program **Laravel** di browser dan  AI processing.