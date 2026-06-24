<?php

namespace App\Imports;

use App\Models\Kelas;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class KelasImport implements ToModel, WithValidation, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Kelas([
            'nama_kelas' => $row['nama_kelas'],
            'tingkat' => $row['tingkat'],
        ]);
    }

    public function rules(): array
    {
        return [
            'nama_kelas' => 'required|string',
            'tingkat' => 'required|in:10,11,12',
        ];
    }

    public function customeMessage()
    {
        return [
            'nama_kelas.required' => 'Kelas tidak boleh kosong pada baris :attribute',
            'nama_kelas.string' => 'Kelas harus berupa string pada baris :attribute',
            'tingkat.required' => 'Tingkat tidak boleh kosong pada baris :attribute',
        ];
    }
}
