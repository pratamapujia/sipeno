<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['kelas', 'tingkat'])]
#[Table('classes', key: 'id')]
class Kelas extends Model
{
    public function guruMapel(): HasMany
    {
        return $this->hasMany(GuruMapel::class);
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }
}
