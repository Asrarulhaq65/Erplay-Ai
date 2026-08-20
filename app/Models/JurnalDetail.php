<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JurnalDetail extends Model
{
    use HasFactory;

    protected $table = 'jurnal_detail';

    protected $fillable = [
        'jurnal_id',
        'akun_id',
        'debit',
        'kredit',
        'memo',
    ];

    public function jurnal()
    {
        return $this->belongsTo(JurnalUmum::class, 'jurnal_id');
    }

    public function akun()
    {
        return $this->belongsTo(AkunAkuntansi::class, 'akun_id');
    }
}
