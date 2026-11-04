# Panduan Pasang NOTAGO (Toko Material) di VPS

Target: VPS Ubuntu 22.04/24.04, 1–2 vCPU, RAM 2 GB, domain sendiri. Perkiraan biaya VPS lokal Rp 100–300 rb/bulan.

## 1. Siapkan server (sekali saja)

```bash
sudo apt update && sudo apt -y upgrade
sudo apt -y install nginx mysql-server supervisor git unzip curl \
  php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl \
  php8.2-zip php8.2-gd php8.2-intl php8.2-bcmath
curl -sS https://getcomposer.org/installer | php && sudo mv composer.phar /usr/local/bin/composer
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash - && sudo apt -y install nodejs
```

Batas upload PHP (scan nota & dokumen) di `/etc/php/8.2/fpm/php.ini`:

```
upload_max_filesize = 40M
post_max_size = 50M
memory_limit = 512M
```

## 2. Database

```bash
sudo mysql -e "CREATE DATABASE notago CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'notago'@'localhost' IDENTIFIED BY 'GANTI_PASSWORD_KUAT';
GRANT ALL ON notago.* TO 'notago'@'localhost'; FLUSH PRIVILEGES;"
```

Kalau memindahkan data lama dari XAMPP: `mysqldump -u root sistem_rab_material > lama.sql` di komputer lama, lalu `mysql -u notago -p notago < lama.sql` di server.

## 3. Pasang aplikasi

```bash
sudo mkdir -p /var/www/notago && sudo chown $USER:www-data /var/www/notago
git clone <repo> /var/www/notago   # atau upload zip
cd /var/www/notago
cp deploy/.env.production.example .env   # isi DB_PASSWORD, APP_URL, OPENAI_API_KEY, FONNTE_TOKEN, STORE_*
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan key:generate
php artisan migrate --force
php artisan storage:link
sudo chown -R www-data:www-data storage bootstrap/cache
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Buat akun pemilik pertama:

```bash
php artisan tinker --execute="App\Models\User::create(['name'=>'Pemilik','email'=>'pemilik@toko.com','password'=>bcrypt('GANTI'),'role'=>'admin','is_active'=>true]);"
```

Akun kasir dibuat dari menu **Manajemen User** (role *Kasir*).

## 4. Nginx + HTTPS

```bash
sudo cp deploy/nginx.conf /etc/nginx/sites-available/notago   # ganti domain di dalamnya
sudo ln -s /etc/nginx/sites-available/notago /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
sudo apt -y install certbot python3-certbot-nginx && sudo certbot --nginx -d toko.contoh.com
```

## 5. Queue worker (wajib untuk Scan Nota)

```bash
sudo cp deploy/supervisor-notago-worker.conf /etc/supervisor/conf.d/
sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl status
```

## 6. Jadwal otomatis (pengingat WA, backup, stok)

`sudo crontab -u www-data -e` lalu tambahkan:

```
* * * * * cd /var/www/notago && php artisan schedule:run >> /dev/null 2>&1
```

Yang berjalan otomatis:
| Jam | Tugas |
|---|---|
| 07:00 | Peringatan stok menipis |
| 08:00 | Pengingat invoice jatuh tempo |
| 08:30 | **Pengingat WA bon pelanggan & hutang supplier** (`store:send-reminders`) |
| 23:30 | **Backup database** ke `storage/app/backups` (14 hari terakhir) |

Uji manual: `php artisan store:send-reminders --dry-run` dan `php artisan store:backup-db`.

Backup juga sebaiknya disalin ke luar server (Google Drive/rclone atau unduh berkala).

## 7. Update versi berikutnya

```bash
cd /var/www/notago && bash deploy/deploy.sh
```

## Checklist sebelum serah terima ke klien

- [ ] `APP_DEBUG=false`, `APP_ENV=production`, HTTPS aktif
- [ ] `STORE_MODE=true`, `STORE_NAME/ADDRESS/PHONE` terisi (tampil di struk)
- [ ] `OPENAI_API_KEY` terisi & coba 1 scan nota
- [ ] `FONNTE_TOKEN` terisi, nomor WA pemilik diisi di **Pengaturan**, coba tombol tes WhatsApp
- [ ] Worker supervisor `RUNNING`, cron `schedule:run` terpasang
- [ ] `php artisan store:backup-db` berhasil
- [ ] Akun pemilik & kasir dibuat, password default sudah diganti
- [ ] Data barang: harga jual, satuan lain (ikat/dus), stok minimum
- [ ] Stok awal dimasukkan lewat **Stok → Input Stok** (jenis *Koreksi/Masuk*, isi harga beli agar laba akurat)
