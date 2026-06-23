<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['nama_ruangan', 'keterangan'])]
#[Table('rooms', key: 'id')]
class Ruangan extends Model
{
    use SoftDeletes;
    public function jadwal(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }
}
