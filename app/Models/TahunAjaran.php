<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['tahun_ajaran', 'semester', 'is_active'])]
#[Table('academic_years', key: 'id')]
class TahunAjaran extends Model
{
    protected $casts = ['is_active' => 'boolean'];

    public function jadwal(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }
}
