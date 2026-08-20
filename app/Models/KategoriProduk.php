<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: KategoriProduk
 *
 * Second-level product category, child of KelompokProduk.
 * Examples: Kelompok "Alat Tulis" → Kategori "Buku", "Pulpen", "Penghapus".
 *
 * Category hierarchy:
 *   KelompokProduk (Level 1) → KategoriProduk (Level 2) → Produk
 *
 * Global Scope: Auto-filters queries by authenticated user's toko_id.
 *
 * @property int    $id
 * @property int    $toko_id
 * @property int    $kelompok_id
 * @property string $nama_kategori
 */
class KategoriProduk extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'kategori_produk';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'toko_id',
        'kelompok_id',
        'nama_kategori',
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
                $builder->where('kategori_produk.toko_id', auth()->user()->toko_id);
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
     * A kategori belongs to one toko (tenant).
     */
    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    /**
     * A kategori belongs to its parent kelompok (Level 1 grouping).
     */
    public function kelompok(): BelongsTo
    {
        return $this->belongsTo(KelompokProduk::class, 'kelompok_id');
    }

    /**
     * A kategori has many products.
     * DELETE RESTRICT: Cannot delete a kategori if products reference it.
     */
    public function produks(): HasMany
    {
        return $this->hasMany(Produk::class, 'kategori_id');
    }
}
