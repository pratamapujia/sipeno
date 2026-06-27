<?php

namespace App\Imports;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class GuruImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        foreach ($rows as $row) {
            // Lewati baris jika data email atau nama kosong
            if (!isset($row['email']) || !isset($row['nama_guru'])) {
                continue;
            }

            // Cek apakah email sudah ada agar tidak error duplikat
            $user = User::firstOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['nama_guru'],
                    'password' => Hash::make('guru123')
                ]
            );

            // Jika user ini baru dibuat dan belum punya role, beri role guru
            if (!$user->hasRole('guru')) {
                $user->assignRole('guru');
            }

            // Simpan atau update profil guru
            Guru::updateOrCreate(
                ['nip' => $row['nip']], // Acuan pencarian data
                [
                    'users_id' => $user->id,
                    'nama_guru' => $row['nama_guru'],
                    'jenis_kelamin' => $row['jenis_kelamin'] ?? 'L',
                    'status' => $row['status'] ?? 'Tetap',
                ]
            );
        }
    }
    public function rules(): array
    {
        return [
            'nama_guru'     => 'required|min:3|string',
            'jenis_kelamin' => 'required|in:L,P',
            'status'        => 'required|in:Tetap,Honorer',
        ];
    }

    public function customMessage()
    {
        return [
            'nama_guru.required' => 'Nama Guru tidak boleh kosong pada baris :attribute',
            'nama_guru.min' => 'Nama Guru minimal 3 karakter pada baris :attribute',
            'jenis_kelamin.required' => 'Jenis Kelamin tidak boleh kosong pada baris :attribute',
            'status.required' => 'Status Guru tidak boleh kosong pada baris :attribute',
        ];
    }
}
