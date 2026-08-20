<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model: SelfServiceOrder
 *
 * Stores customer self-service orders placed via the mobile UI before
 * verification and finalization into a Penjualan record by the cashier.
 */
class SelfServiceOrder extends Model
{
    protected $table = 'self_service_orders';

    protected $fillable = [
        'toko_id',
        'nomor_pesanan',
        'nama_pelanggan',
        'items',
        'total_harga',
        'diskon',
        'total_bayar',
        'metode_pembayaran',
        'status',
        'user_id',
        'penjualan_id',
        'notes',
    ];

    protected $casts = [
        'items'        => 'array',
        'total_harga'  => 'decimal:2',
        'diskon'       => 'decimal:2',
        'total_bayar'  => 'decimal:2',
    ];

    /**
     * Boot the model.
     * Auto-filters queries by authenticated user's toko_id when logged in.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('self_service_orders.toko_id', auth()->user()->toko_id);
            }
        });

        static::creating(function (self $model) {
            if (auth()->check() && empty($model->toko_id)) {
                $model->toko_id = auth()->user()->toko_id;
            } elseif (empty($model->toko_id)) {
                $model->toko_id = 1; // Default tenant for public walk-in self service
            }
        });
    }

    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    /**
     * Generate unique nomor_pesanan format: SS-YYYYMMDD-XXXX
     */
    public static function generateNomorPesanan(): string
    {
        $prefix = 'SS-' . now()->format('Ymd') . '-';
        $last = static::withoutGlobalScope('tenant')
            ->where('nomor_pesanan', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('nomor_pesanan');

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
