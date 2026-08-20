<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AkunAkuntansi extends Model
{
    use HasFactory;

    protected $table = 'akun_akuntansi';

    protected $fillable = [
        'toko_id',
        'kode_akun',
        'nama_akun',
        'tipe_akun',
        'saldo_normal',
        'saldo_awal',
        'is_header',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('akun_akuntansi.toko_id', auth()->user()->toko_id);
            }
        });

        static::creating(function (self $model) {
            if (auth()->check() && empty($model->toko_id)) {
                $model->toko_id = auth()->user()->toko_id;
            }
        });
    }

    public function jurnalDetails()
    {
        return $this->hasMany(JurnalDetail::class, 'akun_id');
    }
}
