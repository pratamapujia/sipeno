<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['wali_kelas_id', 'nama_kelas', 'tingkat'])]
#[Table('classes', key: 'id')]
class Kelas extends Model
{
    use SoftDeletes;

    public function guruMapel(): HasMany
    {
        return $this->hasMany(GuruMapel::class);
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }

    public function waliKelas()
    {
        return $this->belongsTo(Guru::class, 'wali_kelas_id');
    }
}
