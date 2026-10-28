<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Throwable;

/**
 * Memeriksa kesiapan go-live. Jalankan di server tujuan: php artisan app:preflight
 * Kode keluar 1 bila ada pemeriksaan GAGAL (cocok dipakai di skrip deploy).
 */
class PreflightCommand extends Command
{
    protected $signature = 'app:preflight';

    protected $description = 'Memeriksa kesiapan konfigurasi sebelum go-live (produksi).';

    /** @var array<int, array{0: string, 1: string, 2: string}> */
    private array $rows = [];

    private int $failures = 0;

    private int $warnings = 0;

    public function handle(): int
    {
        $this->checkApp();
        $dbOk = $this->checkDatabase();
        $this->checkInfrastructure();
        $this->checkIntegrations();

        if ($dbOk) {
            $this->checkAccounts();
        }

        $this->table(['Status', 'Pemeriksaan', 'Keterangan'], $this->rows);
        $this->newLine();

        if ($this->failures > 0) {
            $this->error("{$this->failures} pemeriksaan GAGAL, {$this->warnings} peringatan. Perbaiki sebelum go-live.");

            return self::FAILURE;
        }

        if ($this->warnings > 0) {
            $this->warn("Tidak ada yang GAGAL, tetapi ada {$this->warnings} peringatan yang sebaiknya ditinjau.");

            return self::SUCCESS;
        }

        $this->info('Semua pemeriksaan lulus.');

        return self::SUCCESS;
    }

    private function pass(string $check, string $note = ''): void
    {
        $this->rows[] = ['OK', $check, $note];
    }

    private function caution(string $check, string $note): void
    {
        $this->warnings++;
        $this->rows[] = ['PERINGATAN', $check, $note];
    }

    // private function fail(string $check, string $note): void
    // {
    //     $this->failures++;
    //     $this->rows[] = ['GAGAL', $check, $note];
    // }
    private function recordFail(string $check, string $note): void
{
    $this->failures++;
    $this->rows[] = ['GAGAL', $check, $note];
}

    private function checkApp(): void
    {
        config('app.key')
            ? $this->pass('APP_KEY')
            : $this->recordFail('APP_KEY', 'Belum diatur. Jalankan php artisan key:generate.');

        config('app.debug')
            ? $this->recordFail('APP_DEBUG', 'APP_DEBUG=true membocorkan detail error. Ubah menjadi false.')
            : $this->pass('APP_DEBUG');

        config('app.env') === 'production'
            ? $this->pass('APP_ENV')
            : $this->caution('APP_ENV', 'Bernilai "'.config('app.env').'"; gunakan production di server.');

        str_starts_with((string) config('app.url'), 'https://')
            ? $this->pass('APP_URL HTTPS')
            : $this->caution('APP_URL HTTPS', 'APP_URL belum https://. Tautan email dan callback pembayaran memakainya.');

        config('app.locale') === 'id'
            ? $this->pass('Bahasa (APP_LOCALE)')
            : $this->caution('Bahasa (APP_LOCALE)', 'Bernilai "'.config('app.locale').'"; pesan validasi berbahasa Indonesia butuh APP_LOCALE=id.');

        config('app.timezone') === 'Asia/Jakarta'
            ? $this->pass('Zona waktu')
            : $this->caution('Zona waktu', 'Bernilai "'.config('app.timezone').'". Slot jadwal arsitek memakai zona waktu aplikasi (APP_TIMEZONE).');
    }

    private function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();
            $this->pass('Koneksi database');
        } catch (Throwable $e) {
            $this->recordFail('Koneksi database', 'Tidak dapat terhubung: '.$e->getMessage());

            return false;
        }

        try {
            $migrator = app('migrator');
            $files = $migrator->getMigrationFiles(array_merge($migrator->paths(), [database_path('migrations')]));
            $ran = $migrator->getRepository()->repositoryExists() ? $migrator->getRepository()->getRan() : [];
            $pending = array_diff(array_keys($files), $ran);

            $pending === []
                ? $this->pass('Migrasi database')
                : $this->recordFail('Migrasi database', count($pending).' migrasi belum dijalankan. Jalankan php artisan migrate --force.');
        } catch (Throwable $e) {
            $this->caution('Migrasi database', 'Tidak dapat diperiksa: '.$e->getMessage());
        }

        try {
            Role::where('name', 'admin')->exists()
                ? $this->pass('Peran & izin')
                : $this->recordFail('Peran & izin', 'Peran belum dibuat. Jalankan php artisan db:seed --class=RoleSeeder --force.');
        } catch (Throwable) {
            $this->recordFail('Peran & izin', 'Tabel peran tidak ditemukan. Jalankan migrasi.');
        }

        return true;
    }

    private function checkInfrastructure(): void
    {
        $queue = config('queue.default');
        $queue === 'sync'
            ? $this->caution('Antrean (QUEUE_CONNECTION)', 'sync: email dikirim saat permintaan berjalan sehingga lambat. Gunakan database/redis dan jalankan queue worker.')
            : $this->pass('Antrean (QUEUE_CONNECTION)', (string) $queue.' (pastikan queue worker berjalan)');

        in_array(config('mail.default'), ['log', 'array'], true)
            ? $this->recordFail('Email (MAIL_MAILER)', 'Mailer "'.config('mail.default').'" tidak mengirim email sungguhan. Atur SMTP/penyedia email.')
            : $this->pass('Email (MAIL_MAILER)', (string) config('mail.default'));

        in_array((string) config('mail.from.address'), ['', 'hello@example.com'], true)
            ? $this->caution('Alamat pengirim email', 'MAIL_FROM_ADDRESS masih bawaan. Gunakan alamat domain Anda.')
            : $this->pass('Alamat pengirim email');

        if (str_starts_with((string) config('app.url'), 'https://') && config('session.secure') !== true) {
            $this->caution('Cookie sesi aman', 'Atur SESSION_SECURE_COOKIE=true.');
        } else {
            $this->pass('Cookie sesi aman');
        }

        config('sanctum.expiration') === null
            ? $this->caution('Masa berlaku token API', 'Token tidak pernah kedaluwarsa. Atur "expiration" di config/sanctum.php.')
            : $this->pass('Masa berlaku token API', config('sanctum.expiration').' menit');

        file_exists(public_path('storage'))
            ? $this->pass('Tautan storage')
            : $this->recordFail('Tautan storage', 'public/storage belum ada. Jalankan php artisan storage:link.');

        if (file_exists(public_path('hot'))) {
            $this->recordFail('Aset front-end', 'public/hot ada (server dev Vite). Hapus berkasnya dan jalankan npm run build.');
        } elseif (file_exists(public_path('build/manifest.json'))) {
            $this->pass('Aset front-end');
        } else {
            $this->recordFail('Aset front-end', 'public/build belum ada. Jalankan npm run build.');
        }
    }

    private function checkIntegrations(): void
    {
        $key = (string) config('payments.midtrans.server_key');
        $isProduction = (bool) config('payments.midtrans.is_production');

        if ($key === '') {
            $this->recordFail('Midtrans: server key', 'MIDTRANS_SERVER_KEY belum diisi.');
        } elseif ($isProduction && str_starts_with($key, 'SB-')) {
            $this->recordFail('Midtrans: kunci', 'Mode produksi aktif tetapi memakai kunci sandbox (SB-...).');
        } elseif (! $isProduction && ! str_starts_with($key, 'SB-')) {
            $this->caution('Midtrans: kunci', 'Kunci produksi dipakai dengan MIDTRANS_IS_PRODUCTION=false.');
        } elseif (! $isProduction) {
            $this->caution('Midtrans: mode', 'Masih mode sandbox (MIDTRANS_IS_PRODUCTION=false). Pembayaran belum nyata.');
        } else {
            $this->pass('Midtrans: mode produksi');
        }

        config('payments.finish_url')
            ? $this->pass('Midtrans: PAYMENT_FINISH_URL')
            : $this->caution('Midtrans: PAYMENT_FINISH_URL', 'Belum diisi; pembeli tidak dikembalikan ke situs setelah membayar.');

        config('services.google_maps.server_key')
            ? $this->pass('Google Maps: key server')
            : $this->caution('Google Maps: key server', 'GOOGLE_MAPS_SERVER_KEY kosong; pencarian koordinat dari alamat tidak berfungsi.');

        config('services.google_maps.browser_key')
            ? $this->pass('Google Maps: key browser')
            : $this->caution('Google Maps: key browser', 'GOOGLE_MAPS_BROWSER_KEY kosong; peta interaktif tidak tampil.');
    }

    private function checkAccounts(): void
    {
        $demo = ['admin@example.com', 'buyer@example.com', 'seller@example.com', 'architect@example.com'];

        User::whereIn('email', $demo)->exists()
            ? $this->recordFail('Akun demo', 'Akun demo (@example.com, password "password") masih ada. Hapus sebelum go-live.')
            : $this->pass('Akun demo');

        User::role('admin')->exists()
            ? $this->pass('Administrator')
            : $this->recordFail('Administrator', 'Belum ada administrator. Jalankan php artisan app:create-admin email@anda.com.');
    }
}
