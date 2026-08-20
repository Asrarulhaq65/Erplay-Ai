<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAkunAkuntansiRequest;
use App\Http\Requests\StoreJurnalRequest;
use App\Models\AkunAkuntansi;
use App\Services\Akuntansi\AkuntansiService;
use Illuminate\Http\Request;

class AkuntansiController extends Controller
{
    public function __construct(private readonly AkuntansiService $service) {}

    public function accountsIndex()
    {
        $this->authorize('viewAny', AkunAkuntansi::class);
        return view('pages.akuntansi.accounts', ['accounts' => $this->service->accounts(auth()->user()->toko_id)]);
    }

    public function accountsStore(StoreAkunAkuntansiRequest $request)
    {
        $this->authorize('create', AkunAkuntansi::class);
        $validated = $request->validated();
        $this->service->createAccount($validated, auth()->user()->toko_id);
        return redirect()->back()->with('success', 'Akun akuntansi baru berhasil ditambahkan.');
    }

    public function jurnalIndex(Request $request)
    {
        $this->authorize('viewAny', AkunAkuntansi::class);
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        return view('pages.akuntansi.jurnal', ['jurnals' => $this->service->journals(auth()->user()->toko_id, $startDate, $endDate), 'accounts' => $this->service->accounts(auth()->user()->toko_id), 'startDate' => $startDate, 'endDate' => $endDate]);
    }

    public function jurnalStore(StoreJurnalRequest $request)
    {
        $this->authorize('create', AkunAkuntansi::class);
        $validated = $request->validated();
        $debit = array_sum(array_map('floatval', array_column($validated['details'], 'debit')));
        $credit = array_sum(array_map('floatval', array_column($validated['details'], 'kredit')));
        if (abs($debit - $credit) > 0.01) return redirect()->back()->with('error', 'Total Debit dan Kredit tidak seimbang (Unbalanced Jurnal).');
        $this->service->saveJournal($validated, auth()->user()->toko_id, auth()->user()->getKey());
        return redirect()->back()->with('success', 'Entri Jurnal Umum berhasil disimpan.');
    }

    public function bukuBesar(Request $request)
    {
        $this->authorize('viewAny', AkunAkuntansi::class);
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $data = $this->service->ledger(auth()->user()->toko_id, $request->integer('akun_id') ?: null, $startDate, $endDate);
        return view('pages.akuntansi.buku-besar', [...$data, 'selectedAkun' => $data['selected'], 'startDate' => $startDate, 'endDate' => $endDate]);
    }

    public function labaRugi(Request $request)
    {
        $this->authorize('viewAny', AkunAkuntansi::class);
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $data = $this->service->profitAndLoss(auth()->user()->toko_id, $startDate, $endDate);
        return view('pages.akuntansi.laba-rugi', ['pendapatanAccounts' => $data['pendapatan'], 'totalPendapatan' => $data['totalPendapatan'], 'bebanAccounts' => $data['beban'], 'totalBeban' => $data['totalBeban'], 'labaBersih' => $data['labaBersih'], 'startDate' => $startDate, 'endDate' => $endDate]);
    }
}
