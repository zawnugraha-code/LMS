<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\Admin\ActivityLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'arunika:buat-admin
                            {--name= : Nama lengkap administrator}
                            {--email= : Alamat email administrator}
                            {--password= : Kata sandi akun}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat akun Administrator baru untuk LMS Arunika secara interaktif dan aman';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=== Pembuatan Akun Administrator LMS Arunika ===');

        $name = $this->option('name') ?: $this->ask('Nama Lengkap Administrator', 'Administrator Kampus');

        $email = $this->option('email');
        while (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || User::where('email', $email)->exists()) {
            if (!empty($email) && User::where('email', $email)->exists()) {
                $this->error("Email '{$email}' sudah terdaftar. Silakan gunakan email lain.");
            } elseif (!empty($email)) {
                $this->error("Format email '{$email}' tidak valid.");
            }
            $email = $this->ask('Alamat Email Administrator');
        }

        $password = $this->option('password');
        if (empty($password)) {
            while (true) {
                $password = $this->secret('Kata Sandi Administrator (min. 8 karakter)');
                if (strlen($password) < 8) {
                    $this->error('Kata sandi harus memiliki panjang minimal 8 karakter.');
                    continue;
                }
                $confirm = $this->secret('Ulangi Kata Sandi');
                if ($password !== $confirm) {
                    $this->error('Konfirmasi kata sandi tidak cocok. Silakan coba lagi.');
                    continue;
                }
                break;
            }
        } elseif (strlen($password) < 8) {
            $this->error('Kata sandi dari opsi --password harus memiliki panjang minimal 8 karakter.');
            return self::FAILURE;
        }

        $admin = User::create([
            'name' => trim($name),
            'email' => trim(strtolower($email)),
            'password' => Hash::make($password),
            'role' => UserRole::Admin,
            'is_active' => true,
            'must_change_password' => false,
            'email_verified_at' => now(),
            'password_changed_at' => now(),
        ]);

        ActivityLogger::log(
            aksi: 'buat_admin',
            kategori: 'keamanan',
            subjek: $admin,
            deskripsi: "Pembuatan akun administrator {$admin->email} melalui CLI artisan",
            properti: ['email' => $admin->email, 'name' => $admin->name]
        );

        $this->newLine();
        $this->info("Berhasil! Akun Administrator [{$admin->name}] ({$admin->email}) telah dibuat.");
        $this->line("Peran: Administrator LMS Arunika");
        $this->line("Rute Dasbor: " . route('admin.dashboard', [], false));

        return self::SUCCESS;
    }
}
