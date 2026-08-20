<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model: PenjualanDetail
 *
 * Sales order line items — one row per product per transaction.
 *
 * IMPORTANT — Tenant Isolation Design:
 *   This table intentionally does NOT have a toko_id column.
 *   Tenant isolation is guaranteed transitively through penjualan_id:
 *     penjualan_detail → penjualan (toko_id + Global Scope) → tenant boundary.
 *   Therefore, no additional Global Scope is applied on this model.
 *   Always access detail records through the Penjualan relationship:
 *     $penjualan->details()->get()  ← correct, inherits tenant scope
 *
 * CRITICAL — harga_satuan is the ACTUAL selling price at the moment of
 * the transaction (based on the customer's tier). It is decoupled from
 * produk.harga_jual_* to ensure historical financial reports remain
 * accurate even after price changes. (PRD Pasal 5 requirement)
 *
 * @property int   $id
 * @property int   $penjualan_id
 * @property int   $produk_id
 * @property float $harga_satuan
 * @property int   $qty
 * @property float $subtotal
 */
class PenjualanDetail extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'penjualan_detail';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'penjualan_id',
        'produk_id',
        'harga_satuan',
        'qty',
        'subtotal',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'subtotal'     => 'decimal:2',
        'qty'          => 'integer',
    ];

    // ─── Relationships ──────────────────────────────────────────────────────

    /**
     * A line item belongs to its parent sales transaction (header).
     */
    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    /**
     * A line item references one product.
     * No cascade delete — product records must remain for historical P&L reporting.
     * The Laba Rugi formula uses harga_satuan (this model) minus produk.harga_modal
     * at the time of sale (recorded in LogStok or computed from this model).
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
