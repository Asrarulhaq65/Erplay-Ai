<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use SplFileObject;

/**
 * PelangganController
 *
 * Full CRUD for the Customer master.
 * Key features:
 *   - Auto-generates kode_pelanggan (PLG-0001, PLG-0002, …)
 *     using withoutGlobalScope to get the correct sequence number
 *     scoped to the current toko only.
 *   - Status pelanggan (Umum / Member / Rekan / Motoris) drives
 *     which price tier column is used in POS and PenjualanController.
 *   - Destroy guard: blocks deletion if the customer has any sales records.
 *   - All queries are tenant-filtered by the model's Global Scope.
 */
class PelangganController extends Controller
{
    /**
     * Display a paginated, searchable customer list.
     */
    public function index(Request $request): View
    {
        $q = $request->get('q', '');

        $pelanggans = Pelanggan::when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nama_pelanggan', 'like', "%{$q}%")
                        ->orWhere('kode_pelanggan', 'like', "%{$q}%")
                        ->orWhere('no_telepon', 'like', "%{$q}%");
                });
            })
            ->orderBy('nama_pelanggan')
            ->paginate(20)
            ->withQueryString();

        return view('pages.master.pelanggan.index', compact('pelanggans', 'q'));
    }

    /** Show the Excel-compatible CSV import form. */
    public function importForm(): View
    {
        return view('pages.master.pelanggan.import', [
            'tiers' => ['Umum', 'Member', 'Rekan', 'Motoris'],
        ]);
    }

    /** Import customers from a CSV exported by Excel/Google Sheets. */
    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:10240'],
            'default_tier' => ['required', Rule::in(['Umum', 'Member', 'Rekan', 'Motoris'])],
        ], [
            'customer_file.required' => 'File pelanggan wajib dipilih.',
            'customer_file.mimes' => 'File harus berupa CSV, TXT, atau Excel XLSX.',
            'default_tier.in' => 'Tier pelanggan tidak valid.',
        ]);

        $headers = null;
        $imported = 0;
        $skipped = 0;
        $errors = [];
        $rowNumber = 0;

        foreach ($this->readImportRows($request->file('customer_file')) as $row) {
            $rowNumber++;
            if (!is_array($row) || count(array_filter($row, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            if ($headers === null) {
                $headers = array_map(fn ($header) => $this->normalizeImportHeader($header), $row);
                continue;
            }

            $data = [];
            foreach ($headers as $index => $header) {
                if ($header !== '') {
                    $data[$header] = trim((string) ($row[$index] ?? ''));
                }
            }

            $name = $data['nama_pelanggan'] ?? $data['nama'] ?? '';
            $phone = $data['no_telepon'] ?? $data['telepon'] ?? $data['no_hp'] ?? $data['hp'] ?? '';
            $tier = $this->normalizeCustomerTier($data['status_pelanggan'] ?? $data['jenis_pelanggan'] ?? $data['tier'] ?? '')
                ?: $validated['default_tier'];

            if ($name === '' || $phone === '') {
                $skipped++;
                $errors[] = "Baris {$rowNumber}: nama dan nomor telepon wajib diisi.";
                continue;
            }

            Pelanggan::create([
                'kode_pelanggan' => $this->generateKodePelanggan(),
                'nama_pelanggan' => mb_substr($name, 0, 100),
                'no_telepon' => mb_substr($phone, 0, 20),
                'status_pelanggan' => $tier,
                'alamat' => ($data['alamat'] ?? '') !== '' ? mb_substr($data['alamat'], 0, 500) : null,
            ]);
            $imported++;
        }

        if ($headers === null) {
            return back()->with('error', 'File CSV tidak memiliki header.');
        }

        $message = "Import selesai: {$imported} pelanggan berhasil ditambahkan";
        if ($skipped > 0) {
            $message .= ", {$skipped} baris dilewati.";
        }

        return redirect()->route('master.pelanggan.index')
            ->with('success', $message)
            ->with('import_errors', $errors);
    }

    private function detectCsvDelimiter(string $path): string
    {
        $line = (string) fgets(fopen($path, 'rb'));
        $counts = [',' => substr_count($line, ','), ';' => substr_count($line, ';'), "\t" => substr_count($line, "\t")];
        arsort($counts);

        return (string) array_key_first($counts);
    }

    private function readImportRows(mixed $uploadedFile): iterable
    {
        $path = $uploadedFile->getRealPath();
        if (strtolower($uploadedFile->getClientOriginalExtension()) !== 'xlsx') {
            $file = new SplFileObject($path);
            $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
            $file->setCsvControl($this->detectCsvDelimiter($path));
            yield from $file;
            return;
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('File Excel tidak dapat dibaca.');
        }

        $sharedStrings = [];
        if (($xml = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
            $document = simplexml_load_string($xml);
            $document->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            foreach ($document->xpath('//x:si') as $item) {
                $sharedStrings[] = implode('', array_map('strval', $item->xpath('.//x:t')));
            }
        }

        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if ($sheet === false) {
            throw new \RuntimeException('Sheet pertama pada file Excel tidak ditemukan.');
        }

        $document = simplexml_load_string($sheet);
        $document->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        foreach ($document->xpath('//x:sheetData/x:row') as $xmlRow) {
            $row = [];
            foreach ($xmlRow->c as $cell) {
                $reference = (string) $cell['r'];
                preg_match('/^[A-Z]+/', $reference, $match);
                $column = $this->excelColumnNumber($match[0] ?? 'A');
                $value = (string) ($cell->v ?? '');
                if ((string) $cell['t'] === 's') {
                    $value = $sharedStrings[(int) $value] ?? '';
                } elseif ((string) $cell['t'] === 'inlineStr') {
                    $value = implode('', array_map('strval', $cell->xpath('.//x:t')));
                }
                $row[$column] = $value;
            }
            if ($row !== []) {
                ksort($row);
                yield array_values($row);
            }
        }
    }

    private function excelColumnNumber(string $letters): int
    {
        $number = 0;
        foreach (str_split($letters) as $letter) {
            $number = ($number * 26) + ord($letter) - 64;
        }

        return $number - 1;
    }

    private function normalizeImportHeader(mixed $header): string
    {
        $header = strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $header)));
        $header = preg_replace('/[^a-z0-9]+/', '_', $header);

        return trim($header, '_');
    }

    private function normalizeCustomerTier(string $tier): ?string
    {
        $tier = strtolower(trim($tier));
        $aliases = [
            'umum' => 'Umum', 'general' => 'Umum', 'public' => 'Umum',
            'member' => 'Member', 'anggota' => 'Member',
            'rekan' => 'Rekan', 'mitra' => 'Rekan', 'reseller' => 'Rekan',
            'motoris' => 'Motoris', 'sales' => 'Motoris', 'grosir' => 'Motoris',
        ];

        return $aliases[$tier] ?? null;
    }

    /**
     * Store a newly created customer.
     *
     * kode_pelanggan is auto-generated here; it is NOT accepted from the form.
     * The format PLG-XXXX is scoped per toko (different shops have independent sequences).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_pelanggan'   => ['required', 'string', 'max:100'],
            'no_telepon'       => ['required', 'string', 'max:20'],
            'alamat'           => ['nullable', 'string', 'max:500'],
            'status_pelanggan' => ['required', Rule::in(['Umum', 'Member', 'Rekan', 'Motoris'])],
        ], [
            'nama_pelanggan.required'   => 'Nama pelanggan wajib diisi.',
            'no_telepon.required'       => 'No. telepon wajib diisi.',
            'status_pelanggan.required' => 'Status pelanggan wajib dipilih.',
            'status_pelanggan.in'       => 'Status pelanggan tidak valid.',
        ]);

        $validated['kode_pelanggan'] = $this->generateKodePelanggan();

        Pelanggan::create($validated);

        return redirect()
            ->route('master.pelanggan.index')
            ->with('success', "Pelanggan \"{$validated['nama_pelanggan']}\" ({$validated['kode_pelanggan']}) berhasil ditambahkan.");
    }

    /**
     * Update an existing customer.
     * kode_pelanggan is immutable after creation — not included in update.
     * status_pelanggan CAN be changed (e.g., upgrading Umum → Member).
     */
    public function update(Request $request, Pelanggan $pelanggan): RedirectResponse
    {
        $validated = $request->validate([
            'nama_pelanggan'   => ['required', 'string', 'max:100'],
            'no_telepon'       => ['required', 'string', 'max:20'],
            'alamat'           => ['nullable', 'string', 'max:500'],
            'status_pelanggan' => ['required', Rule::in(['Umum', 'Member', 'Rekan', 'Motoris'])],
        ], [
            'nama_pelanggan.required'   => 'Nama pelanggan wajib diisi.',
            'no_telepon.required'       => 'No. telepon wajib diisi.',
            'status_pelanggan.required' => 'Status pelanggan wajib dipilih.',
            'status_pelanggan.in'       => 'Status pelanggan tidak valid.',
        ]);

        $pelanggan->update($validated);

        return redirect()
            ->route('master.pelanggan.index')
            ->with('success', "Data pelanggan \"{$validated['nama_pelanggan']}\" berhasil diperbarui.");
    }

    /**
     * Delete a customer.
     *
     * Guard: Cannot delete if the customer has any sales transactions.
     * This preserves the referential integrity of penjualan records
     * and historical sales reports.
     */
    public function destroy(Pelanggan $pelanggan): RedirectResponse
    {
        if ($pelanggan->penjualans()->exists()) {
            return redirect()
                ->route('master.pelanggan.index')
                ->with('error', "Pelanggan \"{$pelanggan->nama_pelanggan}\" tidak dapat dihapus karena sudah memiliki riwayat transaksi penjualan.");
        }

        $nama = $pelanggan->nama_pelanggan;
        $pelanggan->delete();

        return redirect()
            ->route('master.pelanggan.index')
            ->with('success', "Pelanggan \"{$nama}\" berhasil dihapus.");
    }

    // ─── Private Helpers ────────────────────────────────────────────────

    /**
     * Generate the next available kode_pelanggan for the current toko.
     *
     * Format: PLG-XXXX (e.g., PLG-0001, PLG-0042)
     *
     * Uses withoutGlobalScope('tenant') so we can inspect ALL pelanggan
     * rows for this specific toko regardless of any additional scope filters,
     * then manually scope to the current toko_id. This ensures the sequence
     * counter is per-toko and does not increment across tenants.
     *
     * @return string
     */
    private function generateKodePelanggan(): string
    {
        $tokoId = auth()->user()->toko_id;

        // Get the highest existing code for this toko
        $lastKode = Pelanggan::withoutGlobalScope('tenant')
            ->where('toko_id', $tokoId)
            ->where('kode_pelanggan', 'like', 'PLG-%')
            ->orderByDesc('id')
            ->value('kode_pelanggan');

        // Extract the numeric part and increment
        $nextNumber = $lastKode
            ? ((int) substr($lastKode, 4)) + 1
            : 1;

        return 'PLG-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
