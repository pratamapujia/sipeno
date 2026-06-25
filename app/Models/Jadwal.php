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
    'kelas_id',
    'ruangan_id',
    'guru_id',
    'mapel_id'
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
        return $this->belongsTo(TahunAjaran::class, 'academic_year_id')->withTrashed();
    }

    public function slotJam(): BelongsTo
    {
        return $this->belongsTo(SlotJam::class, 'time_slot_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id')->withTrashed();
    }

    // public function ruangan(): BelongsTo
    // {
    //     return $this->belongsTo(Ruangan::class, 'ruangan_id')->withTrashed();
    // }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id')->withTrashed();
    }

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class, 'mapel_id')->withTrashed();
    }
}
