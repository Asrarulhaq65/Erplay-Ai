<?php

namespace App\Policies;

use App\Models\AkunAkuntansi;
use App\Models\User;

class AkunAkuntansiPolicy
{
    public function viewAny(User $user): bool { return $user->toko_id !== null; }
    public function view(User $user, AkunAkuntansi $akun): bool { return (int) $user->toko_id === (int) $akun->toko_id; }
    public function create(User $user): bool { return $user->toko_id !== null; }
    public function update(User $user, AkunAkuntansi $akun): bool { return $this->view($user, $akun); }
    public function delete(User $user, AkunAkuntansi $akun): bool { return $this->view($user, $akun); }
}
