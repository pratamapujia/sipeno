<?php

namespace App\Models;

use App\Models\Jadwal;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['slot_number', 'start_time', 'end_time', 'is_istirahat'])]
#[Table('time_slots', key: 'id')]
class SlotJam extends Model
{
    protected $casts = ['is_istirahat' => 'boolean'];

    public function free(): HasMany
    {
        return $this->hasMany(GuruFree::class);
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }
}
