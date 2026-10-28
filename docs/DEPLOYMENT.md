# Panduan & Daftar Periksa Deployment

Panduan ini untuk server Linux (Ubuntu 22.04/24.04) dengan Nginx + PHP-FPM + MySQL. Perintah `php artisan app:preflight`
memeriksa otomatis sebagian besar butir di bawah dan harus dijalankan di server tujuan sebelum membuka situs ke pengguna.

## 1. Ringkasan daftar periksa

| # | Butir | Cara memastikan |
|---|---|---|
| 1 | `APP_DEBUG=false`, `APP_ENV=production`, `APP_URL` https | `app:preflight` |
| 2 | HTTPS aktif (sertifikat Let's Encrypt) | Buka situs, cek gembok; `app:preflight` |
| 3 | Kunci Midtrans **produksi** + Payment Notification URL di dashboard Midtrans | Bagian 8 |
| 4 | Email sungguhan (SMTP/penyedia) | Daftar akun uji, cek email masuk |
| 5 | Queue worker berjalan (Supervisor) | `supervisorctl status` |
| 6 | Cron scheduler berjalan | Bagian 7 |
| 7 | `php artisan storage:link` dan penyimpanan foto yang persisten | Unggah foto uji |
| 8 | Hasil `npm run build` ada di `public/build` | `app:preflight` |
| 9 | Akun demo dihapus, administrator asli dibuat | `app:preflight` |
| 10 | Masa berlaku token Sanctum | Bagian 12 |
| 11 | Backup database + foto terjadwal dan **pernah diuji dipulihkan** | Bagian 10 |
| 12 | Log harian + pemantauan error + pemantauan uptime | Bagian 11 |
| 13 | Kebijakan bisnis: booking fee, komisi, refund, syarat & privasi | Diputuskan pemilik proyek |

## 2. Kebutuhan server

- PHP 8.2+ dengan ekstensi: `bcmath ctype curl dom fileinfo gd intl mbstring openssl pdo_mysql tokenizer xml zip`
- MySQL 8 / MariaDB 10.6+, Nginx, Composer 2, Node.js 20+ (hanya untuk build aset), Supervisor, Certbot.

## 3. Pemasangan awal

```bash
git clone <repo-anda> /var/www/properti && cd /var/www/properti
composer install --no-dev --optimize-autoloader
cp .env.production.example .env   # lalu isi semua nilai <...>
php artisan key:generate --force

npm ci && npm run build

php artisan migrate --force
php artisan db:seed --class=RoleSeeder --force     # HANYA RoleSeeder. Jangan jalankan DatabaseSeeder (berisi akun demo)
php artisan app:create-admin email@anda.com --name="Nama Anda"
php artisan storage:link

php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache
chown -R www-data:www-data storage bootstrap/cache
php artisan app:preflight
```

Uji `php artisan route:cache` juga di komputer pengembangan sebelum deploy pertama; bila gagal, biasanya ada rute berbentuk closure.

## 4. Nginx

```nginx
server {
    listen 80;
    server_name <domain-anda>;
    root /var/www/properti/public;
    index index.php;

    client_max_body_size 20M;          # unggah foto

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    location / { try_files $uri $uri/ /index.php?$query_string; }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* { deny all; }
}
```

Aktifkan HTTPS: `certbot --nginx -d <domain-anda>`. Sesuaikan `upload_max_filesize` dan `post_max_size` di `php.ini` (minimal 20M).

## 5. Queue worker (Supervisor)

Email (verifikasi, reset password, notifikasi) dikirim lewat antrean. **Tanpa worker, email tidak akan terkirim.**

`/etc/supervisor/conf.d/properti-worker.conf`:

```ini
[program:properti-worker]
command=php /var/www/properti/artisan queue:work --sleep=3 --tries=3 --max-time=3600
directory=/var/www/properti
user=www-data
autostart=true
autorestart=true
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/properti-worker.log
stopwaitsecs=3600
```

```bash
supervisorctl reread && supervisorctl update && supervisorctl status
```

Pantau antrean gagal: `php artisan queue:failed`. Ulangi: `php artisan queue:retry all`.

Reverb (opsional, hanya bila klien lain memakai WebSocket; web memakai polling): buat program serupa dengan
`command=php /var/www/properti/artisan reverb:start --host=0.0.0.0 --port=8080` dan proksikan lewat Nginx/HTTPS.

## 6. Penyimpanan foto

- **Server tunggal:** foto tersimpan di `storage/app/public` (`php artisan storage:link`). Pastikan disk server persisten dan ikut dibackup.
- **Beberapa server / object storage (opsional):** pasang `composer require league/flysystem-aws-s3-v3 "^3.0"`, lalu di
  `config/filesystems.php` ubah definisi disk `public` agar memakai driver `s3` (isi `key`, `secret`, `region`, `bucket`, `url`).
  Seluruh kode memakai disk bernama `public`, jadi tidak ada kode lain yang berubah.

## 7. Cron scheduler

```bash
* * * * * cd /var/www/properti && php artisan schedule:run >> /dev/null 2>&1
```

Menjalankan: `payments:expire-stale` (tiap 15 menit, melepas properti yang tertahan tagihan kedaluwarsa), pembersihan antrean gagal,
token Sanctum kedaluwarsa, dan token reset password. Tanpa cron, properti bisa tertahan lebih lama dari 24 jam.

## 8. Midtrans produksi

1. Selesaikan aktivasi akun produksi di Midtrans, aktifkan metode yang dipakai (VA, kartu kredit + cicilan per bank, GoPay/ShopeePay, QRIS).
2. Isi `MIDTRANS_SERVER_KEY` / `MIDTRANS_CLIENT_KEY` produksi dan `MIDTRANS_IS_PRODUCTION=true`.
3. Di dashboard Midtrans (Settings > Configuration) isi **Payment Notification URL**: `https://<domain-anda>/api/v1/payments/notification`
   dan **Finish URL**: `https://<domain-anda>/pembayaran/selesai`.
4. Lakukan 1 transaksi nyata nominal kecil untuk setiap metode, lalu pastikan status transaksi menjadi *Lunas* dan email terkirim.
5. Refund dilakukan manual di dashboard Midtrans; catat di admin lewat "Catat pengembalian dana".

## 9. Google Maps

- Key **server** (Geocoding API): batasi berdasarkan alamat IP server.
- Key **browser** (Maps JavaScript API): batasi berdasarkan HTTP referrer `https://<domain-anda>/*`.
- Aktifkan penagihan dan pasang batas kuota/anggaran harian agar tidak ada tagihan tak terduga.

## 10. Backup

Contoh cron harian (database + foto), simpan di tempat terpisah dari server:

```bash
0 2 * * * mysqldump --single-transaction -u <user_db> -p'<password>' <nama_db> | gzip > /backup/db-$(date +\%F).sql.gz
30 2 * * * tar -czf /backup/foto-$(date +\%F).tar.gz -C /var/www/properti/storage/app public
0 3 * * * find /backup -type f -mtime +14 -delete
```

**Uji pemulihan** minimal sekali: impor backup ke database kosong dan pastikan aplikasi berjalan. Backup yang belum pernah diuji belum bisa diandalkan.

## 11. Log & pemantauan

- `LOG_STACK=daily`, `LOG_LEVEL=warning`, simpan 14 hari. Baca: `tail -f storage/logs/laravel-$(date +%F).log`.
- Pantau uptime ke `https://<domain-anda>/up` (UptimeRobot, Better Stack, dsb).
- Pemantauan error (disarankan): Sentry atau Flare. Contoh Sentry: `composer require sentry/sentry-laravel` lalu `php artisan sentry:publish --dsn=<dsn>`.
- Periksa berkala: `php artisan queue:failed` dan `storage/logs` untuk peringatan "Notifikasi email gagal dikirim".

## 12. Keamanan

- **Hapus akun demo** dan jangan menjalankan `DatabaseSeeder` di produksi.
- Token API Sanctum: di `config/sanctum.php` ubah `'expiration' => null,` menjadi `'expiration' => env('SANCTUM_EXPIRATION', 10080),`.
- **CORS:** bila ada klien web/mobile dari domain lain, jalankan `php artisan config:publish cors` dan isi `allowed_origins` dengan domain yang diizinkan (jangan `*`).
- Di belakang load balancer/CDN: percayai proksi di `bootstrap/app.php` dalam `withMiddleware`: `$middleware->trustProxies(at: '*');` (batasi ke IP proksi bila memungkinkan).
- Perbarui dependensi berkala: `composer outdated`, `npm audit`.
- Batasi akses SSH (kunci publik, nonaktifkan login password), aktifkan firewall (hanya 22/80/443), dan MySQL hanya mendengar di localhost.

## 13. Pembaruan berikutnya

```bash
bash deploy/deploy.sh
```

Skrip membuka mode pemeliharaan, menarik kode, membangun aset, memigrasi database, menyegarkan cache, memulai ulang worker, dan menjalankan `app:preflight`.

## 14. Rollback

```bash
php artisan down
git checkout <commit-sebelumnya>
composer install --no-dev --optimize-autoloader && npm ci && npm run build
php artisan migrate:status        # migrasi baru tidak otomatis dibatalkan; pulihkan dari backup bila perlu
php artisan optimize:clear && php artisan config:cache && php artisan route:cache
php artisan queue:restart && php artisan up
```

## 15. Uji asap setelah deploy

1. Daftar akun baru -> email verifikasi masuk -> klik tautan.
2. Lupa password -> email masuk -> password berhasil diubah.
3. Seller: buat properti + foto + ajukan; admin menyetujui; properti tampil di situs dan peta.
4. Pembeli: chat penjual; bayar booking fee nominal kecil; status menjadi *Lunas*; properti menjadi *Dipesan*; penjual & pembeli menerima email.
5. Arsitek: atur jadwal; pembeli memesan slot; arsitek mengonfirmasi; pembeli membayar.
6. Admin: tinjau transaksi, catat pengembalian dana uji, suspend lalu aktifkan kembali sebuah akun.
