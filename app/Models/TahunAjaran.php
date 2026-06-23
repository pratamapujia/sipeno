<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['tahun_ajaran', 'semester', 'is_active'])]
#[Table('academic_years', key: 'id')]
class TahunAjaran extends Model
{
    use SoftDeletes;
    protected $casts = ['is_active' => 'boolean'];

    protected static function booted(): void
    {
        static::saved(function (TahunAjaran $tahunAjaran) {
            if ($tahunAjaran->is_active) {
                TahunAjaran::where('id', '!=', $tahunAjaran->id)->update(['is_active' => false]);
            }
        });

        static::deleting(function (TahunAjaran $tahunAjaran) {
            if ($tahunAjaran->is_active) {
                // Membatalkan proses delete dengan melemparkan error
                throw new \Exception('Tahun ajaran yang sedang aktif dilarang untuk dihapus.');
            }
        });
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }
}
