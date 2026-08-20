<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model: PembelianDetail
 *
 * Purchase order line items — one row per product per faktur.
 *
 * IMPORTANT — Tenant Isolation Design:
 *   This table intentionally does NOT have a toko_id column.
 *   Tenant isolation is guaranteed transitively through pembelian_id:
 *     pembelian_detail → pembelian (toko_id + Global Scope) → tenant boundary.
 *   Therefore, no additional Global Scope is applied on this model.
 *   Always access detail records through the Pembelian relationship:
 *     $pembelian->details()->get()  ← correct, inherits tenant scope
 *     PembelianDetail::all()        ← safe; same transitivity via join/eager-load
 *
 * harga_beli_satuan is recorded at the moment of purchase and later used
 * to update produk.harga_modal via the Last Cost strategy.
 *
 * @property int   $id
 * @property int   $pembelian_id
 * @property int   $produk_id
 * @property float $harga_beli_satuan
 * @property int   $qty
 * @property float $subtotal
 */
class PembelianDetail extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'pembelian_detail';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'pembelian_id',
        'produk_id',
        'harga_beli_satuan',
        'qty',
        'subtotal',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'harga_beli_satuan' => 'decimal:2',
        'subtotal'          => 'decimal:2',
        'qty'               => 'integer',
    ];

    // ─── Relationships ──────────────────────────────────────────────────────

    /**
     * A line item belongs to its parent purchase order (header).
     */
    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_id');
    }

    /**
     * A line item references one product.
     * No cascade delete — product records must remain for historical reporting.
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
