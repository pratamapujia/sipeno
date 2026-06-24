<?php

namespace App\Imports;

use App\Models\Guru;
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
        return new Guru([
            'nip'           => $row['nip'] ?? null,
            'nama_guru'     => $row['nama_guru'],
            'jenis_kelamin' => $row['jenis_kelamin'] == 'L' ? 'L' : 'P',
            'status'        => $row['status'] == 'Tetap' ? 'Tetap' : 'Honorer',
        ]);
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
