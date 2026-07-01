<?php

namespace App\Imports;

use App\Models\SlotJam;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class SlotJamImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function prepareForValidation($data, $index)
    {
        if (isset($data['start_time']) && is_numeric($data['start_time'])) {
            $data['start_time'] = Date::excelToDateTimeObject($data['start_time'])->format('H:i');
        }

        if (isset($data['end_time']) && is_numeric($data['end_time'])) {
            $data['end_time'] = Date::excelToDateTimeObject($data['end_time'])->format('H:i');
        }

        return $data;
    }

    public function model(array $row)
    {
        // Lewati baris jika data slot kosong
        if (!isset($row['slot_number']) && !isset($row['slot'])) {
            return null;
        }

        // Deteksi nilai is_istirahat (misal di excel ditulis 'ya' atau 'tidak', atau '1'/'0')
        $is_istirahat = false;
        if (isset($row['is_istirahat'])) {
            $val = strtolower(trim($row['is_istirahat']));
            $is_istirahat = in_array($val, ['ya', 'y', '1', 'true', 'istirahat']);
        }

        // Gunakan updateOrCreate agar tidak error jika ada slot ganda
        // Cocokkan berdasarkan kolom 'slot_number' (atau 'slot' dari Excel)
        return SlotJam::updateOrCreate(
            ['slot_number' => $row['slot_number'] ?? $row['slot']],
            [
                'start_time' => $row['start_time'] ?? $row['mulai'],
                'end_time' => $row['end_time'] ?? $row['selesai'],
                'is_istirahat' => $is_istirahat,
            ]
        );
    }

    public function rules(): array
    {
        return [
            'slot_number'    => 'required|numeric|unique:time_slots,slot_number',
            'start_time'   => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'slot_number.required'       => 'Kolom nomor slot wajib diisi.',
            'slot_number.numeric'        => 'Kolom nomor slot harus berupa angka.',
            'slot_number.unique'         => 'Nomor slot sudah terdaftar.',
            'start_time.required'      => 'Kolom jam mulai wajib diisi.',
            'start_time.date_format'   => 'Format jam mulai salah (Gunakan HH:MM, contoh: 07:00).',
            'end_time.required'    => 'Kolom jam selesai wajib diisi.',
            'end_time.date_format' => 'Format jam selesai salah (Gunakan HH:MM, contoh: 08:30).',
        ];
    }
}
