<?php

namespace Tests\Unit\Akuntansi;

use App\Models\AkunAkuntansi;
use App\Models\JurnalDetail;
use App\Models\JurnalUmum;
use App\Models\Role;
use App\Models\Toko;
use App\Models\User;
use App\Services\Akuntansi\AkuntansiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AkuntansiServiceTest extends TestCase
{
    use RefreshDatabase;

    private function actingStore(): Toko
    {
        $toko = Toko::create(['nama_toko' => 'Toko Test', 'alamat' => 'Alamat', 'no_telepon' => '0812']);
        $role = Role::create(['nama_role' => 'Owner']);
        $user = User::create(['toko_id' => $toko->id, 'role_id' => $role->id, 'username' => 'owner' . $toko->id, 'nama_lengkap' => 'Owner Test', 'password' => 'secret', 'is_active' => true]);
        $this->actingAs($user);
        return $toko;
    }

    public function test_default_accounts_are_created_once(): void
    {
        $toko = $this->actingStore();
        $service = app(AkuntansiService::class);
        $service->ensureDefaultAccounts($toko->id);
        $service->ensureDefaultAccounts($toko->id);

        $this->assertSame(11, AkunAkuntansi::withoutGlobalScopes()->where('toko_id', $toko->id)->count());
        $this->assertSame(1, AkunAkuntansi::withoutGlobalScopes()->where('toko_id', $toko->id)->where('kode_akun', '4001')->count());
    }

    public function test_save_journal_persists_balanced_details(): void
    {
        $toko = $this->actingStore();
        $accounts = app(AkuntansiService::class)->ensureDefaultAccounts($toko->id);
        $service = app(AkuntansiService::class);
        $journal = $service->saveJournal(['tanggal' => '2026-08-20', 'keterangan' => 'Penjualan test', 'details' => [['akun_id' => $accounts->firstWhere('kode_akun', '1001')->id, 'debit' => 100000, 'kredit' => 0], ['akun_id' => $accounts->firstWhere('kode_akun', '4001')->id, 'debit' => 0, 'kredit' => 100000]]], $toko->id, auth()->user()->getKey());

        $this->assertDatabaseHas('jurnal_umum', ['id' => $journal->id, 'toko_id' => $toko->id]);
        $this->assertSame(2, JurnalDetail::where('jurnal_id', $journal->id)->count());
    }

    public function test_ledger_calculates_running_balance_for_debit_account(): void
    {
        $toko = $this->actingStore();
        $service = app(AkuntansiService::class);
        $account = $service->ensureDefaultAccounts($toko->id)->firstWhere('kode_akun', '1001');
        foreach ([[100000, 0], [0, 25000], [50000, 0]] as $index => [$debit, $credit]) {
            $journal = JurnalUmum::create(['toko_id' => $toko->id, 'nomor_jurnal' => 'TEST-' . $index, 'tanggal' => '2026-08-' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT), 'keterangan' => 'Mutasi', 'ref_type' => 'Test', 'user_id' => auth()->user()->getKey()]);
            JurnalDetail::create(['jurnal_id' => $journal->id, 'akun_id' => $account->id, 'debit' => $debit, 'kredit' => $credit]);
        }
        $data = $service->ledger($toko->id, $account->id, '2026-08-01', '2026-08-31');

        $this->assertSame([100000.0, 75000.0, 125000.0], $data['mutasi']->pluck('saldo_berjalan')->map(fn ($value) => (float) $value)->all());
    }

    public function test_profit_and_loss_returns_zero_when_there_are_no_entries(): void
    {
        $toko = $this->actingStore();
        $data = app(AkuntansiService::class)->profitAndLoss($toko->id, '2026-08-01', '2026-08-31');

        $this->assertSame(0, $data['totalPendapatan']);
        $this->assertSame(0, $data['totalBeban']);
        $this->assertSame(0, $data['labaBersih']);
    }
}
