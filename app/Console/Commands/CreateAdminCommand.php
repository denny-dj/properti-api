<?php

namespace App\Console\Commands;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

/** Membuat administrator pertama dengan aman (password diketik tersembunyi, tidak masuk riwayat shell). */
class CreateAdminCommand extends Command
{
    protected $signature = 'app:create-admin {email : Alamat email administrator} {--name=Administrator} {--phone=}';

    protected $description = 'Membuat akun administrator baru.';

    public function handle(): int
    {
        $email = (string) $this->argument('email');

        $data = Validator::make(
            ['email' => $email, 'phone' => $this->option('phone') ?: null],
            [
                'email' => ['required', 'email:rfc', 'max:150', 'unique:users,email'],
                'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            ],
        );

        if ($data->fails()) {
            foreach ($data->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $password = (string) $this->secret('Password (minimal 12 karakter, huruf dan angka)');

        if ($this->secret('Ulangi password') !== $password) {
            $this->error('Password tidak sama.');

            return self::FAILURE;
        }

        $check = Validator::make(['password' => $password], ['password' => [Password::min(12)->letters()->numbers()]]);

        if ($check->fails()) {
            $this->error($check->errors()->first('password'));

            return self::FAILURE;
        }

        try {
            $user = User::create([
                'name' => (string) $this->option('name'),
                'email' => $email,
                'phone' => $this->option('phone') ?: null,
                'password' => $password,
                'status' => 'active',
            ]);

            $user->forceFill(['email_verified_at' => now()])->save();
            $user->assignRole(RoleName::Admin->value);
        } catch (RoleDoesNotExist) {
            $this->error('Peran admin belum ada. Jalankan: php artisan db:seed --class=RoleSeeder --force');
            User::where('email', $email)->forceDelete();

            return self::FAILURE;
        }

        $this->info("Administrator {$email} dibuat dan sudah terverifikasi.");

        return self::SUCCESS;
    }
}
