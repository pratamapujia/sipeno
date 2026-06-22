<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\BatchJadwal;

#[Fillable([
    'schedule_batch_id',
    'academic_year_id',
    'day',
    'time_slot_id',
    'classes_id',
    'room_id',
    'teacher_id',
    'subject_id'
])]
#[Table('schedules', key: 'id')]
class Jadwal extends Model
{
    public function batch(): BelongsTo
    {
        return $this->belongsTo(BatchJadwal::class, 'schedule_batch_id');
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function slotJam(): BelongsTo
    {
        return $this->belongsTo(SlotJam::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function nama_ruangan(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class);
    }
}
