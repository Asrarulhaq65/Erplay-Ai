<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model: LogStok
 *
 * Immutable append-only inventory movement audit trail.
 * Scoped per tenant (toko_id).
 *
 * DESIGN RULE: This table is NEVER updated — only inserted.
 * Each row is a permanent, tamper-evident record of a stock change event.
 * Do NOT add softDeletes or update triggers to this model.
 *
 * tipe_perubahan values and their triggers:
 *   'Masuk_Barang'     → Triggered by PembelianController@store (goods received)
 *   'Penjualan'        → Triggered by PenjualanController@store (stock consumed)
 *   'Retur'            → Triggered by return/retur module (goods returned in or out)
 *   'Penyesuaian_Stok' → Triggered by manual stock adjustment by Gudang/Owner
 *
 * stok_awal + stok_akhir snapshot the stock level at the exact moment of the
 * event, enabling full stock history reconstruction without relying on produk.stok.
 *
 * jumlah is signed: positive = stock added, negative = stock deducted.
 *
 * Global Scope: Auto-filters queries by authenticated user's toko_id.
 *
 * @property int         $id
 * @property int         $toko_id
 * @property int         $produk_id
 * @property string      $tipe_perubahan  Masuk_Barang|Penjualan|Retur|Penyesuaian_Stok
 * @property int         $jumlah
 * @property int         $stok_awal
 * @property int         $stok_akhir
 * @property string|null $keterangan
 */
class LogStok extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'log_stok';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'toko_id',
        'produk_id',
        'tipe_perubahan',
        'jumlah',
        'stok_awal',
        'stok_akhir',
        'keterangan',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'jumlah'     => 'integer',
        'stok_awal'  => 'integer',
        'stok_akhir' => 'integer',
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
                $builder->where('log_stok.toko_id', auth()->user()->toko_id);
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
     * A stock log entry belongs to one toko (tenant).
     */
    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    /**
     * A stock log entry references one product.
     * No cascade delete — log entries must remain even if a product is discontinued.
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
