<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['tahun_ajaran_id', 'guru_id', 'hari'])]
#[Table('guru_pikets', key: 'id')]
class GuruPiket extends Model
{
    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function thnAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }
}
