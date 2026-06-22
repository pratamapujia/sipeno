<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['teacher_id', 'time_slot_id', 'is_available'])]
#[Table('teacher_availibilities', key: 'id')]
class GuruFree extends Model
{
    protected $casts = [
        'is_available' => 'boolean',
    ];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function slotJam(): BelongsTo
    {
        return $this->belongsTo(SlotJam::class);
    }
}
