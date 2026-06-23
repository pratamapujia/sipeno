<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['nip', 'nama_guru', 'jenis_kelamin', 'status'])]
#[Table('teachers', key: 'id')]
class Guru extends Model
{
    use SoftDeletes;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Target/plot mengajar guru ini
    public function guruMapel(): HasMany
    {
        return $this->hasMany(GuruMapel::class);
    }

    // Ketersediaan waktu mengajar guru ini
    public function free(): HasMany
    {
        return $this->hasMany(GuruFree::class);
    }

    // Hasil jadwal akhir guru ini
    public function jadwal(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }
}
