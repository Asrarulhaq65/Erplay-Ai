<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: Pelanggan
 *
 * Customer master data scoped per tenant (toko_id).
 * The status_pelanggan field drives automatic tier-pricing selection
 * during POS transactions (Umum → harga_jual_umum, etc.).
 *
 * Global Scope: Automatically filters all queries by the authenticated
 * user's toko_id to enforce multi-tenant data isolation.
 *
 * @property int         $id
 * @property int         $toko_id
 * @property string      $kode_pelanggan
 * @property string      $nama_pelanggan
 * @property string      $no_telepon
 * @property string      $status_pelanggan  Umum|Member|Rekan|Motoris
 * @property string|null $alamat
 */
class Pelanggan extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'pelanggan';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'toko_id',
        'kode_pelanggan',
        'nama_pelanggan',
        'no_telepon',
        'status_pelanggan',
        'alamat',
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
                $builder->where('pelanggan.toko_id', auth()->user()->toko_id);
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
     * A customer belongs to one toko (tenant).
     */
    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    /**
     * A customer can have many sales transactions.
     * nullable FK — walk-in "Umum" customers have pelanggan_id = null.
     */
    public function penjualans(): HasMany
    {
        return $this->hasMany(Penjualan::class, 'pelanggan_id');
    }
}
