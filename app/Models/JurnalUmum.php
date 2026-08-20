<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JurnalUmum extends Model
{
    use HasFactory;

    protected $table = 'jurnal_umum';

    protected $fillable = [
        'toko_id',
        'nomor_jurnal',
        'tanggal',
        'keterangan',
        'ref_type',
        'ref_id',
        'user_id',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('jurnal_umum.toko_id', auth()->user()->toko_id);
            }
        });

        static::creating(function (self $model) {
            if (auth()->check() && empty($model->toko_id)) {
                $model->toko_id = auth()->user()->toko_id;
            }
        });
    }

    public function details()
    {
        return $this->hasMany(JurnalDetail::class, 'jurnal_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
