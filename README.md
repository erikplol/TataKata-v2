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

## Deskripsi Program

TataKata merupakan website berbasis AI yang membantu pengguna (mahasiswa) dalam menyempurnakan dokumen tugas akhir mereka sesuai dengan kaidah penulisan bahasa Indonesia baku. Dengan teknologi pemrosesan bahasa alami (NLP), TataKata mampu memeriksa kata baku, kalimat efektif, tanda baca, imbuhan, serta ejaan pada suatu karya tulis untuk memastikan apakah penulisan sudah sesuai dengan kaidah bahasa Indonesia yang baik dan benar.

Website TataKata dirancang agar mudah digunakan oleh seluruh pengguna yang terlibat. Pengguna cukup mendaftar dan login menggunakan akun email yang dimiliki, yang setelah itu masuk ke dashboard website untuk input file tugas akhir bertipe PDF. Sistem AI akan langsung memberikan analisis serta saran perbaikan yang detail. Output dari website ini adalah perbandingan dari isi file PDF asli dan hasil penulisan yang sudah diintegrasikan dengan AI. Pengguna kemudian dapat mengunduh hasil analisis penulisan tugas akhir oleh AI dengan format `.md` ke perangkat.

## How to Run
1. Jalankan instalasi untuk backend dengan command
   ```
   composer install
   ```
   dan instalasi frontend dengan command
   ```
   npm install && npm run build
   ```
   
2. Copy konfigurasi **.env** dengan command terlampir untuk perangkat Linux/macOS
   ```
   cp -n .env.example .env
   ```
   atau command di bawah untuk perangkat Windows
   ```
   Copy-Item .env.example .env
   ```

3. Jalankan command terlampir untuk enkripsi data
   ```
   php artisan key:generate
   ```

4. Jalankan command terlampir untuk ekspor database **mysql** ke sistem backend
   ```
   php artisan migrate:fresh
   ```
   
5. Jalankan command terlampir untuk menyimpan dokumen
   ```
   php artisan storage:link
   ```
   
6. Sediakan 2 terminal untuk run program.
   
7. Jalankan command terlampir pada terminal 1 untuk compile aset frontend
   ```
   npm run dev
   ```
   
8. Jalankan command terlampir pada terminal 2 untuk mengakses program backend di browser dan AI processing
   ```
   php artisan serve && php artisan queue:work --tries=3 --timeout=300
   ```

## Repositori Lama
Link repositori lama: [TataKata Lama](https://github.com/salpurn/TataKata)
