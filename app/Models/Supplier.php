<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: Supplier
 *
 * Supplier/vendor master data scoped per tenant (toko_id).
 * nama_kontak stores the PIC or sales representative's name.
 *
 * Global Scope: Automatically filters all queries by the authenticated
 * user's toko_id to enforce multi-tenant data isolation.
 *
 * @property int         $id
 * @property int         $toko_id
 * @property string      $nama_supplier
 * @property string|null $nama_kontak
 * @property string      $no_telepon
 * @property string|null $alamat
 */
class Supplier extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'supplier';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'toko_id',
        'nama_supplier',
        'nama_kontak',
        'no_telepon',
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
                $builder->where('supplier.toko_id', auth()->user()->toko_id);
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
     * A supplier belongs to one toko (tenant).
     */
    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    /**
     * A supplier has many purchase orders.
     */
    public function pembelians(): HasMany
    {
        return $this->hasMany(Pembelian::class, 'supplier_id');
    }
}
