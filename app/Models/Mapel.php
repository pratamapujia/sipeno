<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['nama_mapel', 'type', 'beban_jam', 'status'])]
#[Table('subjects', key: 'id')]
class Mapel extends Model
{
    use SoftDeletes;
    public function guruMapel(): HasMany
    {
        return $this->hasMany(GuruMapel::class);
    }
}
