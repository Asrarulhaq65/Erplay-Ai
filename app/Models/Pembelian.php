<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: Pembelian
 *
 * Purchase order header — one record per supplier invoice (faktur).
 * Scoped per tenant (toko_id).
 *
 * Business Automation (handled by PembelianController@store):
 *   1. On submit → increment produk.stok for each line item.
 *   2. On submit → update produk.harga_modal using Last Cost strategy.
 *   3. On submit → insert row into log_stok (tipe = 'Masuk_Barang').
 *   4. If metode_pembayaran = 'Tunai' → insert row into arus_kas (kategori = 'Pembelian Stok').
 *   5. If metode_pembayaran = 'Kredit' → set status_pembayaran = 'Hutang', fill jatuh_tempo.
 *
 * Global Scope: Auto-filters queries by authenticated user's toko_id.
 *
 * @property int         $id
 * @property int         $toko_id
 * @property int         $supplier_id
 * @property string      $nomor_faktur
 * @property float       $total_pembelian
 * @property string      $metode_pembayaran  Tunai|Kredit
 * @property string      $status_pembayaran  Lunas|Hutang
 * @property string|null $jatuh_tempo
 * @property int         $user_id
 * @property string      $tanggal_beli
 */
class Pembelian extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'pembelian';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'toko_id',
        'supplier_id',
        'nomor_faktur',
        'total_pembelian',
        'metode_pembayaran',
        'status_pembayaran',
        'jatuh_tempo',
        'user_id',
        'tanggal_beli',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'total_pembelian' => 'decimal:2',
        'jatuh_tempo'     => 'date',
        'tanggal_beli'    => 'date',
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
                $builder->where('pembelian.toko_id', auth()->user()->toko_id);
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
     * A purchase order belongs to one toko (tenant).
     */
    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    /**
     * A purchase order is placed with one supplier.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * A purchase order is entered by one user (staff).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * A purchase order has many line items (detail rows).
     */
    public function details(): HasMany
    {
        return $this->hasMany(PembelianDetail::class, 'pembelian_id');
    }

    // ─── Helper Methods ─────────────────────────────────────────────────────

    /**
     * Check whether this purchase is on credit (hutang).
     */
    public function isKredit(): bool
    {
        return $this->metode_pembayaran === 'Kredit';
    }

    /**
     * Check whether this purchase is fully paid.
     */
    public function isLunas(): bool
    {
        return $this->status_pembayaran === 'Lunas';
    }
}
