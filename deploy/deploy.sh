#!/usr/bin/env bash
# Skrip deploy pembaruan di server (jalankan dari root proyek). Pengaturan awal: lihat docs/DEPLOYMENT.md
set -euo pipefail

php artisan down --retry=60 || true

git pull --ff-only
composer install --no-dev --optimize-autoloader --no-interaction

npm ci
npm run build

php artisan migrate --force
php artisan storage:link 2>/dev/null || true

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

php artisan queue:restart   # worker memuat kode baru
php artisan up

php artisan app:preflight || echo ">> Ada pemeriksaan GAGAL pada app:preflight, tinjau sebelum membuka ke pengguna."
