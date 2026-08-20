<?php

namespace Tests\Feature\Akuntansi;

use App\Models\AkunAkuntansi;
use App\Models\Role;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AkuntansiControllerTest extends TestCase
{
    use RefreshDatabase;

    private function userFor(string $suffix): User
    {
        $toko = Toko::create(['nama_toko' => 'Toko ' . $suffix, 'alamat' => 'Alamat', 'no_telepon' => '0812']);
        $role = Role::firstOrCreate(['nama_role' => 'Owner']);
        return User::create(['toko_id' => $toko->id, 'role_id' => $role->id, 'username' => 'owner-' . strtolower($suffix), 'nama_lengkap' => 'Owner ' . $suffix, 'password' => 'secret', 'is_active' => true]);
    }

    public function test_accounts_index_is_available_and_scoped_to_current_tenant(): void
    {
        $first = $this->userFor('One');
        $second = $this->userFor('Two');
        $foreign = AkunAkuntansi::withoutGlobalScopes()->create(['toko_id' => $second->toko_id, 'kode_akun' => '9999', 'nama_akun' => 'RAHASIA TOKO LAIN', 'tipe_akun' => 'Aset', 'saldo_normal' => 'Debit', 'saldo_awal' => 0]);
        $this->actingAs($first);

        $response = $this->get('/akuntansi/accounts');

        $response->assertOk()->assertViewHas('accounts');
        $this->assertFalse($response->viewData('accounts')->contains('id', $foreign->id));
    }

    public function test_accounts_store_redirects_and_persists_current_tenant_account(): void
    {
        $user = $this->userFor('Store');
        $this->actingAs($user);

        $response = $this->post('/akuntansi/accounts', ['kode_akun' => '7001', 'nama_akun' => 'Beban Test', 'tipe_akun' => 'Beban', 'saldo_normal' => 'Debit', 'saldo_awal' => 0]);

        $response->assertRedirect();
        $this->assertDatabaseHas('akun_akuntansi', ['toko_id' => $user->toko_id, 'kode_akun' => '7001']);
    }
}
