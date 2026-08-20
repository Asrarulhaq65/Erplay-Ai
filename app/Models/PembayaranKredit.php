<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembayaranKredit extends Model
{
    protected $table = 'pembayaran_kredit';

    protected $fillable = [
        'penjualan_id',
        'toko_id',
        'user_id',
        'jumlah',
        'keterangan',
        'tanggal_bayar',
    ];

    protected $casts = [
        'jumlah'       => 'decimal:2',
        'tanggal_bayar' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->toko_id)) {
                $model->toko_id = auth()->user()?->toko_id;
            }
        });
    }

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
