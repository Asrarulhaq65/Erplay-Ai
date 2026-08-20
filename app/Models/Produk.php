<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: Produk
 *
 * Core product / inventory item scoped per tenant (toko_id).
 *
 * Multi-tier pricing: The correct harga_jual_* is selected at POS time
 * based on the locked pelanggan.status_pelanggan:
 *   Umum     → harga_jual_umum
 *   Member   → harga_jual_member
 *   Rekan    → harga_jual_rekan
 *   Motoris  → harga_jual_motoris
 *
 * harga_modal is updated via Last Cost strategy on every purchase
 * (see Pembelian automation trigger).
 *
 * stok_minimum triggers the low-stock visual warning (table-danger row)
 * in the product list UI.
 *
 * Global Scope: Auto-filters queries by authenticated user's toko_id.
 *
 * @property int    $id
 * @property int    $toko_id
 * @property int    $kategori_id
 * @property string $barcode
 * @property string $nama_produk
 * @property string $satuan
 * @property float  $harga_modal
 * @property float  $harga_jual_umum
 * @property float  $harga_jual_member
 * @property float  $harga_jual_rekan
 * @property float  $harga_jual_motoris
 * @property int    $stok
 * @property int    $stok_minimum
 */
class Produk extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'produk';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'toko_id',
        'kategori_id',
        'barcode',
        'nama_produk',
        'satuan',
        'harga_modal',
        'harga_jual_umum',
        'harga_jual_member',
        'harga_jual_rekan',
        'harga_jual_motoris',
        'stok',
        'stok_minimum',
        'gambar',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'harga_modal'          => 'decimal:2',
        'harga_jual_umum'      => 'decimal:2',
        'harga_jual_member'    => 'decimal:2',
        'harga_jual_rekan'     => 'decimal:2',
        'harga_jual_motoris'   => 'decimal:2',
        'stok'                 => 'integer',
        'stok_minimum'         => 'integer',
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
                $builder->where('produk.toko_id', auth()->user()->toko_id);
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
     * A product belongs to one toko (tenant).
     */
    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    /**
     * A product belongs to one kategori (Level-2 category).
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriProduk::class, 'kategori_id');
    }

    /**
     * A product appears in many purchase order line items.
     */
    public function pembelianDetails(): HasMany
    {
        return $this->hasMany(PembelianDetail::class, 'produk_id');
    }

    /**
     * A product appears in many sales order line items.
     */
    public function penjualanDetails(): HasMany
    {
        return $this->hasMany(PenjualanDetail::class, 'produk_id');
    }

    /**
     * A product has a full audit trail of stock movements.
     */
    public function logStoks(): HasMany
    {
        return $this->hasMany(LogStok::class, 'produk_id');
    }

    // ─── Helper Methods ─────────────────────────────────────────────────────

    /**
     * Returns the correct selling price based on pelanggan status_pelanggan.
     * Used by the POS backend price validation to prevent client-side manipulation.
     *
     * @param string $statusPelanggan  Umum|Member|Rekan|Motoris
     * @return float
     */
    public function getHargaByStatus(string $statusPelanggan): float
    {
        return match ($statusPelanggan) {
            'Member'  => (float) $this->harga_jual_member,
            'Rekan'   => (float) $this->harga_jual_rekan,
            'Motoris' => (float) $this->harga_jual_motoris,
            default   => (float) $this->harga_jual_umum,  // 'Umum' and any unknown
        };
    }

    /**
     * Check whether the current stock is at or below the minimum threshold.
     * Used to determine if the table-danger CSS class should be applied.
     */
    public function isStokRendah(): bool
    {
        return $this->stok <= $this->stok_minimum;
    }
}
