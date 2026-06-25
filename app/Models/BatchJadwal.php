<?php

namespace App\Models;

use App\Models\Jadwal;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama', 'status', 'final_fitness_score'])]
#[Table('schedule_batches', key: 'id')]
class BatchJadwal extends Model
{
    public function jadwal(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'schedule_batch_id');
    }
}
