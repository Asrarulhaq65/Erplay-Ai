<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model: ArusKas
 *
 * Single-entry digital cash ledger scoped per tenant (toko_id).
 * Records every cash inflow (Masuk) and outflow (Keluar).
 *
 * Automated insertions triggered by:
 *   - Pembelian Tunai → tipe='Keluar', kategori='Pembelian Stok'
 *   - Penjualan Tunai → tipe='Masuk',  kategori='Penjualan'
 *   Manual entries can be added for operational expenses, salary, etc.
 *
 * Used by the Laba Rugi and Buku Kas Digital reports.
 *
 * Global Scope: Auto-filters queries by authenticated user's toko_id.
 *
 * @property int         $id
 * @property int         $toko_id
 * @property string      $tipe       Masuk|Keluar
 * @property string      $kategori   e.g. Penjualan, Pembelian Stok, Operasional
 * @property float       $nominal
 * @property string|null $keterangan
 * @property string      $tanggal
 * @property int         $user_id
 */
class ArusKas extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'arus_kas';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'toko_id',
        'tipe',
        'kategori',
        'nominal',
        'keterangan',
        'tanggal',
        'user_id',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'nominal' => 'decimal:2',
        'tanggal' => 'date',
    ];

    /**
     * Boot the model.
     * Registers the tenant Global Scope and auto-assigns toko_id on creation.
     */
    protected static function booted(): void
    {
        // ── Global Scope: auto-filter by the authenticated tenant ──────────
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('arus_kas.toko_id', auth()->user()->toko_id);
            }
        });

        // ── Creating Event: auto-assign toko_id from the logged-in user ───
        static::creating(function (self $model) {
            if (auth()->check() && empty($model->toko_id)) {
                $model->toko_id = auth()->user()->toko_id;
            }
        });
    }

    // ─── Relationships ──────────────────────────────────────────────────────

    /**
     * A cash flow entry belongs to one toko (tenant).
     */
    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    /**
     * A cash flow entry is recorded by one user (staff).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ─── Helper Methods ─────────────────────────────────────────────────────

    /**
     * Check whether this is a cash inflow entry.
     */
    public function isMasuk(): bool
    {
        return $this->tipe === 'Masuk';
    }

    /**
     * Check whether this is a cash outflow entry.
     */
    public function isKeluar(): bool
    {
        return $this->tipe === 'Keluar';
    }
}
