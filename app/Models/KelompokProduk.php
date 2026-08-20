<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: KelompokProduk
 *
 * Top-level product grouping (Department level).
 * Examples: "Alat Tulis", "Sembako", "Elektronik".
 *
 * This is the Level-1 in the two-level product category hierarchy:
 *   KelompokProduk (Level 1) → KategoriProduk (Level 2) → Produk
 *
 * The chained dropdown on the product form uses this relationship
 * to dynamically filter KategoriProduk via AJAX.
 *
 * Global Scope: Auto-filters queries by authenticated user's toko_id.
 *
 * @property int    $id
 * @property int    $toko_id
 * @property string $nama_kelompok
 */
class KelompokProduk extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'kelompok_produk';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'toko_id',
        'nama_kelompok',
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
                $builder->where('kelompok_produk.toko_id', auth()->user()->toko_id);
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
     * A kelompok belongs to one toko (tenant).
     */
    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    /**
     * A kelompok has many categories beneath it (Level 2).
     * Used by the AJAX chained dropdown on product forms.
     */
    public function kategoriProduks(): HasMany
    {
        return $this->hasMany(KategoriProduk::class, 'kelompok_id');
    }
}
