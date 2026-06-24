<?php

namespace App\Imports;

use App\Models\Mapel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class MapelImport implements ToModel, WithValidation, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Mapel([
            'nama_mapel' => $row['nama_mapel'],
            'type'       => $row['type'],
            'beban_jam'  => $row['beban_jam'],
        ]);
    }

    public function rules(): array
    {
        return [
            'nama_mapel' => 'required|min:3',
            'beban_jam'  => 'required|numeric',
        ];
    }

    public function customeMessage()
    {
        return [
            'nama_mapel.required' => 'Nama mapel harus diisi',
            'nama_mapel.min' => 'Nama mapel minimal 3 karakter',
            'beban_jam.required' => 'Beban jam harus diisi',
            'beban_jam.numeric' => 'Beban jam harus angka',
        ];
    }
}
