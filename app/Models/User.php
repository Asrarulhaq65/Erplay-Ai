<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: User
 *
 * Application staff accounts scoped per tenant (toko_id).
 * Implements all Laravel authentication contracts to work seamlessly
 * with Laravel's built-in Auth guard.
 *
 * Roles drive module access:
 *   Super Admin → All tenants (no scope restriction)
 *   Owner       → All modules + financial reports
 *   Gudang      → Produk & Pembelian only
 *   Kasir       → POS & Shift reports only
 *
 * is_active = false → user cannot log in (soft toggle; no hard delete).
 *
 * Global Scope: Auto-filters queries by authenticated user's toko_id.
 * Creating Event: Auto-assigns toko_id on new user creation.
 *
 * @property int    $id
 * @property int    $toko_id
 * @property int    $role_id
 * @property string $username
 * @property string $nama_lengkap
 * @property string $password
 * @property bool   $is_active
 */
class User extends Model implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, MustVerifyEmail;

    /**
     * The table associated with the model.
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'toko_id',
        'role_id',
        'username',
        'nama_lengkap',
        'password',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'password'  => 'hashed',
    ];

    /**
     * The authentication field used by Laravel Auth.
     * We use 'username' instead of the default 'email'.
     */
    public function getAuthIdentifierName(): string
    {
        return 'username';
    }

    /**
     * Boot the model.
     * Registers the tenant Global Scope and auto-assigns toko_id on creation.
     */
    protected static function booted(): void
    {
        // ── Global Scope: auto-filter by the authenticated tenant ──────────
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->hasUser()) {
                $builder->where('users.toko_id', auth()->user()->toko_id);
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
     * A user belongs to one toko (tenant).
     */
    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    /**
     * A user belongs to one role.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * A user (kasir/staff) has recorded many purchase orders.
     */
    public function pembelians(): HasMany
    {
        return $this->hasMany(Pembelian::class, 'user_id');
    }

    /**
     * A user (kasir) has processed many sales transactions.
     */
    public function penjualans(): HasMany
    {
        return $this->hasMany(Penjualan::class, 'user_id');
    }

    /**
     * A user has recorded many cash flow entries.
     */
    public function arusKas(): HasMany
    {
        return $this->hasMany(ArusKas::class, 'user_id');
    }

    // ─── Helper Methods ─────────────────────────────────────────────────────

    /**
     * Check if the user has a specific role by name.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role?->nama_role === $roleName;
    }

    /**
     * Check if the user is a Super Admin (cross-tenant access).
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }

    /**
     * Check if the user is an Owner.
     */
    public function isOwner(): bool
    {
        return $this->hasRole('Owner');
    }

    /**
     * Check if the user is a Gudang (warehouse) staff.
     */
    public function isGudang(): bool
    {
        return $this->hasRole('Gudang');
    }

    /**
     * Check if the user is a Kasir (cashier).
     */
    public function isKasir(): bool
    {
        return $this->hasRole('Kasir');
    }
}
