<?php

namespace App\Policies;

use App\Models\Produk;
use App\Models\User;

class ProdukPolicy
{
    public function viewAny(User $user): bool { return $user->toko_id !== null; }
    public function view(User $user, Produk $produk): bool { return (int) $user->toko_id === (int) $produk->toko_id; }
    public function create(User $user): bool { return $user->toko_id !== null; }
    public function update(User $user, Produk $produk): bool { return $this->view($user, $produk); }
    public function delete(User $user, Produk $produk): bool { return $this->view($user, $produk); }
}
