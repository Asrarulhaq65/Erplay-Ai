<?php

namespace App\Services\Akuntansi;

use App\Models\AkunAkuntansi;
use App\Models\AuditLog;
use App\Models\JurnalDetail;
use App\Models\JurnalUmum;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AkuntansiService
{
    public function ensureDefaultAccounts(int $tokoId): Collection
    {
        $existing = AkunAkuntansi::withoutGlobalScopes()->where('toko_id', $tokoId)->get();
        if ($existing->isNotEmpty()) return $existing;

        $defaults = [
            ['kode_akun' => '1001', 'nama_akun' => 'Kas Toko', 'tipe_akun' => 'Aset', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '1002', 'nama_akun' => 'Bank / QRIS', 'tipe_akun' => 'Aset', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '1100', 'nama_akun' => 'Persediaan Barang Dagang', 'tipe_akun' => 'Aset', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '1200', 'nama_akun' => 'Piutang Usaha', 'tipe_akun' => 'Aset', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '2001', 'nama_akun' => 'Hutang Usaha', 'tipe_akun' => 'Kewajiban', 'saldo_normal' => 'Kredit'],
            ['kode_akun' => '3001', 'nama_akun' => 'Modal Pemilik', 'tipe_akun' => 'Ekuitas', 'saldo_normal' => 'Kredit'],
            ['kode_akun' => '3002', 'nama_akun' => 'Laba Ditahan', 'tipe_akun' => 'Ekuitas', 'saldo_normal' => 'Kredit'],
            ['kode_akun' => '4001', 'nama_akun' => 'Pendapatan Penjualan', 'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'Kredit'],
            ['kode_akun' => '5001', 'nama_akun' => 'Harga Pokok Penjualan (HPP)', 'tipe_akun' => 'Beban', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '6001', 'nama_akun' => 'Beban Operasional & Toko', 'tipe_akun' => 'Beban', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '6002', 'nama_akun' => 'Beban Gaji Karyawan', 'tipe_akun' => 'Beban', 'saldo_normal' => 'Debit'],
        ];

        foreach ($defaults as $account) AkunAkuntansi::withoutGlobalScopes()->create([...$account, 'toko_id' => $tokoId]);
        return AkunAkuntansi::withoutGlobalScopes()->where('toko_id', $tokoId)->get();
    }

    public function accounts(int $tokoId): Collection
    {
        $this->ensureDefaultAccounts($tokoId);
        return AkunAkuntansi::withoutGlobalScopes()->where('toko_id', $tokoId)->orderBy('kode_akun')->get();
    }

    public function createAccount(array $data, int $tokoId): AkunAkuntansi
    {
        $account = AkunAkuntansi::withoutGlobalScopes()->create([...$data, 'toko_id' => $tokoId, 'saldo_awal' => $data['saldo_awal'] ?? 0]);
        AuditLog::log("Menambahkan Akun Akuntansi: {$account->kode_akun} - {$account->nama_akun}", 'Akuntansi');
        return $account;
    }

    public function saveJournal(array $data, int $tokoId, ?int $userId): JurnalUmum
    {
        $totalDebit = array_sum(array_map('floatval', array_column($data['details'], 'debit')));
        $nomorJurnal = 'JU-' . date('Ymd-His');
        return DB::transaction(function () use ($data, $tokoId, $userId, $totalDebit, $nomorJurnal) {
            $journal = JurnalUmum::withoutGlobalScopes()->create(['toko_id' => $tokoId, 'nomor_jurnal' => $nomorJurnal, 'tanggal' => $data['tanggal'], 'keterangan' => $data['keterangan'], 'ref_type' => 'Manual', 'user_id' => $userId]);
            foreach ($data['details'] as $detail) JurnalDetail::create(['jurnal_id' => $journal->id, 'akun_id' => $detail['akun_id'], 'debit' => $detail['debit'], 'kredit' => $detail['kredit'], 'memo' => $detail['memo'] ?? null]);
            AuditLog::log("Membuat Jurnal Umum Manual: {$nomorJurnal} (Rp " . number_format($totalDebit, 0, ',', '.') . ")", 'Akuntansi');
            return $journal->load('details');
        });
    }

    public function journals(int $tokoId, string $startDate, string $endDate)
    {
        return JurnalUmum::withoutGlobalScopes()->with(['details.akun', 'user'])->where('toko_id', $tokoId)->whereBetween('tanggal', [$startDate, $endDate])->orderByDesc('tanggal')->orderByDesc('id')->paginate(15);
    }

    public function ledger(int $tokoId, ?int $accountId, string $startDate, string $endDate): array
    {
        $accounts = $this->accounts($tokoId);
        $selected = $accountId ? $accounts->firstWhere('id', $accountId) : $accounts->first();
        $mutasi = collect();
        if ($selected) {
            $mutasi = JurnalDetail::where('akun_id', $selected->id)->whereHas('jurnal', fn ($q) => $q->withoutGlobalScopes()->where('toko_id', $tokoId)->whereBetween('tanggal', [$startDate, $endDate]))->with('jurnal')->get()->sortBy(fn ($detail) => $detail->jurnal->tanggal . '-' . $detail->jurnal->id)->values();
            $balance = (float) $selected->saldo_awal;
            foreach ($mutasi as $detail) {
                $balance += $selected->saldo_normal === 'Debit' ? (float) $detail->debit - (float) $detail->kredit : (float) $detail->kredit - (float) $detail->debit;
                $detail->saldo_berjalan = $balance;
            }
        }
        return compact('accounts', 'selected', 'mutasi');
    }

    public function profitAndLoss(int $tokoId, string $startDate, string $endDate): array
    {
        $base = fn ($type) => AkunAkuntansi::withoutGlobalScopes()->where('toko_id', $tokoId)->where('tipe_akun', $type)->get();
        $pendapatan = $base('Pendapatan');
        $beban = $base('Beban');
        $sum = function (Collection $accounts, string $column) use ($tokoId, $startDate, $endDate) {
            $total = 0;
            foreach ($accounts as $account) {
                $amount = JurnalDetail::where('akun_id', $account->id)->whereHas('jurnal', fn ($q) => $q->withoutGlobalScopes()->where('toko_id', $tokoId)->whereBetween('tanggal', [$startDate, $endDate]))->sum($column);
                $account->total = $amount;
                $total += $amount;
            }
            return [$accounts, $total];
        };
        [$pendapatan, $totalPendapatan] = $sum($pendapatan, 'kredit');
        [$beban, $totalBeban] = $sum($beban, 'debit');
        return compact('pendapatan', 'totalPendapatan', 'beban', 'totalBeban') + ['labaBersih' => $totalPendapatan - $totalBeban];
    }
}
