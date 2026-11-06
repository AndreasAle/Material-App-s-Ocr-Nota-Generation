# Scan satu nota di shared hosting

Target 10 detik harus dibuktikan lewat pengukuran; bukan batas waktu yang dijamin.
Model, koneksi HP, jumlah baris dan kondisi layanan AI memengaruhi hasil.

## Deploy

Upload kode yang berubah, termasuk `public/js/receipt-image.js`. Tidak perlu migrasi
database atau build Vite untuk perubahan ini. Di folder aplikasi NOTAGO:

```bash
php artisan config:cache
php artisan view:clear
php artisan queue:restart
```

Default baru `RECEIPT_SCAN_DISPATCH=sync` menjalankan scan satu nota dalam request
upload, sehingga tidak menunggu cron. Jika `.env` berisi nilai `queue`, ubah ke
`sync` untuk mengaktifkannya. `QUEUE_CONNECTION` tetap `database`: batch dan job
lain tetap membutuhkan cron. Jangan hapus cron aplikasi gorshop atau NOTAGO.

Mode ini menggunakan satu proses PHP selama scan. Batas request PHP/web server
harus cukup untuk pemanggilan AI; timeout klien AI memakai
`OPENAI_REQUEST_TIMEOUT` (default 30 detik), bukan `RECEIPT_AI_TIMEOUT`.
Jangan memasang timeout 10 detik untuk memaksakan target: scan hanya akan gagal.
Jika beban pengguna meningkat atau request hosting terlalu singkat, gunakan
`RECEIPT_SCAN_DISPATCH=queue` dengan worker persisten, lalu cache konfigurasi lagi.

Default `DB_QUEUE_RETRY_AFTER` kini 660 detik, lebih panjang dari job batch 600
detik. Jika `.env` menetapkan nilai lama secara eksplisit, ubah juga nilainya.

## Verifikasi di hosting

1. Foto satu nota dari HP. Ukur sejak menekan Scan sampai halaman hasil terbuka.
2. Periksa `storage/logs/receipt-scan-YYYY-MM-DD.log`. Kanal tersendiri ini tetap
   mencatat metrik meskipun `LOG_LEVEL=warning`, tanpa isi nota atau API key.
3. `wait_ms` mengukur waktu draft dibuat sampai job dimulai (presisi timestamp
   database sekitar satu detik); `processing_ms` adalah durasi job.
   `timings_ms.extraction` mencakup pembacaan file dan panggilan AI,
   `normalization` dan `matching` menunjukkan pengolahan lokal.
4. Upload HP dan rendering halaman tidak masuk durasi job. Bandingkan dengan
   waktu keseluruhan di browser. Uji beberapa nota, termasuk tulisan kecil,
   dan cocokkan qty/harga dengan foto untuk mengecek akurasi setelah kompresi.

Foto besar dikecilkan sebelum upload sesuai `RECEIPT_AI_MAX_IMAGE_SIDE` (default
1800), JPEG kualitas 90%; foto kecil dan file PDF tidak dikompresi ulang.
Jika konversi gagal atau hasilnya lebih besar, browser memakai file asli.
Foto yang tersimpan adalah versi upload tersebut. Naikkan batas dimensi jika
tulisan kecil sulit terbaca. Model dan detail penglihatan AI tetap sama.

Log lokal dan pengujian dengan AI tiruan tidak membuktikan durasi di hosting.
Jika extraction sendiri melebihi 10 detik, penghilangan jeda cron saja belum
cukup: perlu pengujian model/output menggunakan nota representatif sebelum
mengubah akurasi pembacaan.
