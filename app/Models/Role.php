<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: Role
 *
 * Defines application-level roles shared across all tenants.
 * Roles: Super Admin, Owner, Gudang, Kasir.
 *
 * This model is intentionally global — no toko_id, no Global Scope.
 * Role definitions are system-wide, not per-tenant.
 *
 * @property int    $id
 * @property string $nama_role
 */
class Role extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'roles';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nama_role',
    ];

    // ─── Relationships ──────────────────────────────────────────────────────

    /**
     * A role is assigned to many users.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }
}
