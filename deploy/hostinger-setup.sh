#!/usr/bin/env bash
# Pasang / update NOTAGO di Hostinger shared hosting (material.conweb.id).
# Jalankan di terminal SSH Hostinger:
#   bash <(curl -fsSL https://raw.githubusercontent.com/AndreasAle/Material-App-s-Ocr-Nota-Generation/main/deploy/hostinger-setup.sh)
# Aman dijalankan berulang kali: pertama = pasang, berikutnya = update.
set -euo pipefail

REPO="https://github.com/AndreasAle/Material-App-s-Ocr-Nota-Generation.git"
BASE="$HOME/domains/conweb.id"
APP="$BASE/notago"                      # kode aplikasi (di luar public_html, tidak bisa diakses langsung)
DOCROOT="$BASE/public_html/material"    # folder subdomain material.conweb.id
URL="https://material.conweb.id"

say() { printf '\n\033[1;33m==> %s\033[0m\n' "$*"; }

# 1. PHP >= 8.2
PHP=""
for c in php /opt/alt/php84/usr/bin/php /opt/alt/php83/usr/bin/php /opt/alt/php82/usr/bin/php; do
  if command -v "$c" >/dev/null 2>&1 && "$c" -r 'exit(PHP_VERSION_ID >= 80200 ? 0 : 1);' 2>/dev/null; then PHP="$(command -v "$c")"; break; fi
done
[ -n "$PHP" ] || { echo "PHP 8.2+ tidak ditemukan. Di hPanel: Website → PHP Configuration → pilih PHP 8.2/8.3, lalu jalankan ulang."; exit 1; }
say "Memakai $($PHP -v | head -1)"
COMPOSER="$(command -v composer || true)"
[ -n "$COMPOSER" ] || { curl -fsSL https://getcomposer.org/installer | "$PHP" -- --install-dir="$HOME" --filename=composer.phar >/dev/null; COMPOSER="$HOME/composer.phar"; }

# 2. Ambil kode
if [ -d "$APP/.git" ]; then
  say "Update kode"; git -C "$APP" pull --ff-only
else
  say "Unduh kode"; git clone "$REPO" "$APP"
fi
cd "$APP"

# 3. Dependensi (tanpa dev). public/build sudah ikut di repo, jadi tidak perlu Node.
say "Composer install"
"$PHP" "$COMPOSER" install --no-dev --optimize-autoloader --no-interaction

# 4. File .env (RAHASIA — tidak ada di GitHub, isi manual sekali saja)
if [ ! -f .env ]; then
  cp deploy/env.production.example .env
  sed -i "s|^APP_URL=.*|APP_URL=$URL|" .env
  "$PHP" artisan key:generate --force >/dev/null
  say "File .env dibuat. ISI DULU lalu jalankan skrip ini lagi:"
  cat <<'MSG'
   nano ~/domains/conweb.id/notago/.env
   Wajib isi:  DB_DATABASE, DB_USERNAME, DB_PASSWORD   (buat database MySQL di hPanel → Databases)
               OPENAI_API_KEY, FONNTE_TOKEN, OCR_SPACE_API_KEY   (salin dari .env di laptop)
               STORE_NAME, STORE_ADDRESS, STORE_PHONE
   Simpan: Ctrl+O, Enter, Ctrl+X
MSG
  exit 0
fi
grep -q '^DB_DATABASE=.\+' .env && grep -q '^DB_USERNAME=.\+' .env || { echo "DB_DATABASE / DB_USERNAME di .env masih kosong. Isi dulu lalu jalankan ulang."; exit 1; }

# 5. Database & folder tulis
say "Migrasi database"
"$PHP" artisan migrate --force
mkdir -p storage/app/private storage/app/public storage/app/backups storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chmod -R u+rwX,g+rX storage bootstrap/cache

# 6. Hubungkan subdomain ke folder public (symlink). Folder lama TIDAK dihapus, hanya diganti nama.
say "Hubungkan $DOCROOT → $APP/public"
if [ -L "$DOCROOT" ]; then
  ln -sfn "$APP/public" "$DOCROOT"
else
  [ -e "$DOCROOT" ] && mv "$DOCROOT" "${DOCROOT}.lama-$(date +%Y%m%d%H%M%S)"
  ln -s "$APP/public" "$DOCROOT"
fi
rm -rf public/storage && ln -s "$APP/storage/app/public" public/storage

# 7. Pindahkan file nota lama (jika ada) ke penyimpanan privat, lalu cache
"$PHP" artisan scans:secure-files || true
"$PHP" artisan config:cache && "$PHP" artisan route:cache && "$PHP" artisan view:cache
"$PHP" artisan queue:restart || true

say "SELESAI. Buka $URL"
cat <<MSG

Langkah terakhir di hPanel → Advanced → Cron Jobs (tambahkan 2 cron, tiap menit):
  * * * * *  $PHP $APP/artisan schedule:run >/dev/null 2>&1
  * * * * *  $PHP $APP/artisan queue:work --stop-when-empty --max-time=55 --tries=1 >/dev/null 2>&1

Buat akun pemilik pertama:
  $PHP artisan tinker --execute="App\Models\User::create(['name'=>'Pemilik','email'=>'EMAIL_ANDA','password'=>bcrypt('PASSWORD_KUAT'),'role'=>'admin','is_active'=>true]);"
MSG
