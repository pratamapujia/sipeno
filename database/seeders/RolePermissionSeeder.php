<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roleAdmin = Role::create(['name' => 'admin']);
        $roleGuru = Role::create(['name' => 'guru']);
        $roleKepsek = Role::create(['name' => 'kepsek']);

        // =========================================================
        // 2. Buat Akun ADMIN / KURIKULUM
        // =========================================================
        $admin = User::create([
            'name' => 'Admin Kurikulum',
            'email' => 'admin@sekolah.com',
            'password' => Hash::make('admin123'),
        ]);
        $admin->assignRole($roleAdmin);


        // =========================================================
        // 3. Buat Akun GURU (Satu paket dengan profil Teacher-nya)
        // =========================================================
        $guru = User::create([
            'name' => 'Budi Santoso',
            'email' => 'guru@sekolah.com',
            'password' => Hash::make('guru123'),
        ]);
        $guru->assignRole($roleGuru);

        // Langsung buatkan profil biodatanya di tabel teachers
        Guru::create([
            'users_id'      => $guru->id,
            'nip'           => '198001012005011001',
            'nama_guru'     => 'Budi Santoso, S.Pd.',
            'jenis_kelamin' => 'L',
            'status'        => 'Tetap',
        ]);


        // =========================================================
        // 4. Buat Akun KEPALA SEKOLAH
        // =========================================================
        $kepsek = User::create([
            'name' => 'Kepala Sekolah',
            'email' => 'kepsek@sekolah.com',
            'password' => Hash::make('kepsek123'),
        ]);
        $kepsek->assignRole($roleKepsek);


        // =========================================================
        // 5. Buat Akun MULTI-ROLE (Admin yang juga seorang Guru)
        // =========================================================
        $superGuru = User::create([
            'name' => 'Siti Kurikulum',
            'email' => 'kurikulum@sekolah.com',
            'password' => Hash::make('kurikulum123'),
        ]);

        $superGuru->assignRole([$roleAdmin, $roleGuru]);

        Guru::create([
            'users_id'      => $superGuru->id,
            'nip'           => '198502022010012002',
            'nama_guru'     => 'Siti Kurikulum, M.Pd.',
            'jenis_kelamin' => 'P',
            'status'        => 'Tetap',
        ]);

        $this->command->info('Seeder Role, User, dan Teacher berhasil dijalankan!');
    }
}
