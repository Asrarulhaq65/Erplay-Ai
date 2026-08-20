<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    protected $fillable = [
        'toko_id',
        'user_id',
        'aktivitas',
        'modul',
        'ip_address',
        'user_agent',
        'payload_before',
        'payload_after',
    ];

    protected $casts = [
        'payload_before' => 'array',
        'payload_after'  => 'array',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('audit_logs.toko_id', auth()->user()->toko_id);
            }
        });

        static::creating(function (self $model) {
            if (auth()->check() && empty($model->toko_id)) {
                $model->toko_id = auth()->user()->toko_id;
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Helper to log system activity
     */
    public static function log($aktivitas, $modul = 'General', $payloadBefore = null, $payloadAfter = null)
    {
        $user = auth()->user();
        return static::create([
            'toko_id'        => $user?->toko_id ?? 1,
            'user_id'        => $user?->getKey(),
            'aktivitas'      => $aktivitas,
            'modul'          => $modul,
            'ip_address'     => request()->ip(),
            'user_agent'     => substr(request()->userAgent() ?? '', 0, 255),
            'payload_before' => $payloadBefore,
            'payload_after'  => $payloadAfter,
        ]);
    }
}
