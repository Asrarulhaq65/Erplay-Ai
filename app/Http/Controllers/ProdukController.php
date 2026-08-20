<?php

namespace App\Http\Controllers;

use App\Models\KategoriProduk;
use App\Models\KelompokProduk;
use App\Models\Produk;
use App\Http\Requests\StoreProdukRequest;
use App\Http\Requests\UpdateProdukRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ProdukController
 *
 * Full CRUD for the product master.
 * Key features:
 *   - Index: Low-stock visual warning (row-stok-rendah CSS class)
 *   - Create/Edit: Chained dropdown Kelompok → Kategori via AJAX
 *   - 4 separate price tier fields (Umum, Member, Rekan, Motoris)
 *   - Tenant isolation: all queries auto-filtered by Global Scope
 *   - Destroy guard: prevents deletion if product has any transactions
 */
class ProdukController extends Controller
{
    /**
     * Display a paginated, searchable product list with kelompok & kategori filters.
     * Visual warning: rows with stok <= stok_minimum get the
     * 'row-stok-rendah' CSS class applied in the view.
     */
    public function index(Request $request)
    {
        $namaKategoriFilter = $request->input('nama_kategori');
        $namaKelompokFilter = $request->input('nama_kelompok');
        $search             = $request->input('search');
        $perPage            = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $query = \App\Models\Produk::with(['kategori.kelompok']);

        // Filter by kelompok name via nested relationship
        if (!empty($namaKelompokFilter)) {
            $query->whereHas('kategori.kelompok', function ($q) use ($namaKelompokFilter) {
                $q->where('nama_kelompok', $namaKelompokFilter);
            });
        }
        // Filter by category name via relationship
        if (!empty($namaKategoriFilter)) {
            $query->whereHas('kategori', function ($q) use ($namaKategoriFilter) {
                $q->where('nama_kategori', $namaKategoriFilter);
            });
        }
        // Search by nama_produk OR barcode
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_produk', 'like', '%' . $search . '%')
                  ->orWhere('barcode', 'like', '%' . $search . '%');
            });
        }

        // Clone for live calculations (unfiltered-by-pagination averages)
        $clonedForCalc = clone $query;
        $avgUmum = $clonedForCalc->where('harga_jual_umum', '>', 0)->avg('harga_jual_umum') ?? 0;

        $clonedForCalc = clone $query;
        $avgMember = $clonedForCalc->where('harga_jual_member', '>', 0)->avg('harga_jual_member') ?? 0;

        $clonedForCalc = clone $query;
        $avgRekan = $clonedForCalc->where('harga_jual_rekan', '>', 0)->avg('harga_jual_rekan') ?? 0;

        $clonedForCalc = clone $query;
        $avgMotoris = $clonedForCalc->where('harga_jual_motoris', '>', 0)->avg('harga_jual_motoris') ?? 0;

        $produk       = $query->orderBy('nama_produk')->paginate($perPage)->withQueryString();
        $categories   = \App\Models\KategoriProduk::orderBy('nama_kategori')->get();
        $kelompokList = \App\Models\KelompokProduk::orderBy('nama_kelompok')->get();

        return view('pages.master.produk.index', compact(
            'produk', 'categories', 'kelompokList',
            'avgUmum', 'avgMember', 'avgRekan', 'avgMotoris',
            'namaKategoriFilter', 'namaKelompokFilter', 'search', 'perPage'
        ));
    }

    /**
     * AJAX endpoint: return filtered product rows as JSON.
     * Called by the category dropdown and search box via fetch().
     * Returns: { rows: "<tr>...</tr>", avgUmum, avgMember, avgRekan, avgMotoris }
     */
    public function filter(Request $request)
    {
        $namaKategoriFilter = $request->input('nama_kategori');  // filter by name
        $search             = $request->input('search');

        $query = \App\Models\Produk::with(['kategori.kelompok'])->query();

        // Filter by category name via relationship
        if (!empty($namaKategoriFilter)) {
            $query->whereHas('kategori', function ($q) use ($namaKategoriFilter) {
                $q->where('nama_kategori', $namaKategoriFilter);
            });
        }
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_produk', 'like', '%' . $search . '%')
                  ->orWhere('barcode',   'like', '%' . $search . '%');
            });
        }

        // AVG calculations on filtered set
        $cloned     = clone $query;
        $avgUmum    = $cloned->where('harga_jual_umum',    '>', 0)->avg('harga_jual_umum')    ?? 0;
        $cloned     = clone $query;
        $avgMember  = $cloned->where('harga_jual_member',  '>', 0)->avg('harga_jual_member')  ?? 0;
        $cloned     = clone $query;
        $avgRekan   = $cloned->where('harga_jual_rekan',   '>', 0)->avg('harga_jual_rekan')   ?? 0;
        $cloned     = clone $query;
        $avgMotoris = $cloned->where('harga_jual_motoris', '>', 0)->avg('harga_jual_motoris') ?? 0;

        $produk = $query->get();

        // Build HTML rows to inject into the table
        $rows = '';
        if ($produk->isEmpty()) {
            $rows = '<tr><td colspan="8" class="text-center text-muted py-4">'
                  . '<i class="bi bi-box-seam d-block mb-1" style="font-size:20px;"></i>'
                  . 'Tidak ada data produk.</td></tr>';
        } else {
            foreach ($produk as $index => $prod) {
                $isLowStock   = $prod->stok <= $prod->stok_minimum;
                $rowClass     = $isLowStock ? 'row-stok-rendah' : '';
                $namaKategori = $prod->kategori->nama_kategori        ?? '-';
                $namaKelompok = $prod->kategori->kelompok->nama_kelompok ?? '-';
                $lowBadge     = $isLowStock
                    ? '<span class="badge bg-danger ms-1" style="font-size:9px;" title="Stok Minimum: '
                      . $prod->stok_minimum . '">Stok Rendah</span>'
                    : '';
                $stokClass    = $isLowStock ? 'text-danger' : 'text-success';
                $editUrl      = route('master.produk.edit', $prod->id);
                $deleteUrl    = route('master.produk.destroy', $prod->id);
                $csrfToken    = csrf_token();

                $rows .= <<<HTML
<tr class="hover-product {$rowClass}" style="cursor:pointer;"
    data-name="{$prod->nama_produk}"
    data-umum="{$prod->harga_jual_umum}"
    data-member="{$prod->harga_jual_member}"
    data-rekan="{$prod->harga_jual_rekan}"
    data-motoris="{$prod->harga_jual_motoris}">
    <td class="text-center text-muted align-middle">{$prod->id}</td>
    <td class="font-monospace align-middle" style="font-size:11px;">{$prod->barcode}</td>
    <td class="fw-medium align-middle">{$prod->nama_produk} {$lowBadge}</td>
    <td class="align-middle"><span style="font-size:11px;">{$namaKategori}
        <span class="text-muted">({$namaKelompok})</span></span></td>
    <td class="text-end text-muted align-middle" style="font-size:11px;">Rp {$this->fmt($prod->harga_modal)}</td>
    <td class="text-end fw-semibold align-middle" style="font-size:12px;color:var(--pb-text);">Rp {$this->fmt($prod->harga_jual_umum)}</td>
    <td class="text-center fw-bold align-middle {$stokClass}">{$prod->stok} <span style="font-size:10px;font-weight:normal;">{$prod->satuan}</span></td>
    <td class="text-center align-middle">
        <a href="{$editUrl}" class="btn btn-sm btn-outline-primary py-0 px-2 border-0" title="Edit"><i class="bi bi-pencil"></i></a>
        <form action="{$deleteUrl}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus produk ini?');">
            <input type="hidden" name="_token" value="{$csrfToken}">
            <input type="hidden" name="_method" value="DELETE">
            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2 border-0" title="Hapus"><i class="bi bi-trash"></i></button>
        </form>
    </td>
</tr>
HTML;
            }
        }

        return response()->json([
            'rows'        => $rows,
            'avgUmum'     => round($avgUmum,    2),
            'avgMember'   => round($avgMember,  2),
            'avgRekan'    => round($avgRekan,   2),
            'avgMotoris'  => round($avgMotoris, 2),
            'total'       => $produk->count(),
        ]);
    }

    /** Format angka sebagai string ribuan tanpa simbol mata uang */
    private function fmt(float|string $val): string
    {
        return number_format((float) $val, 0, ',', '.');
    }

    /**
     * Show the product creation form.
     * Passes all kelompok for the parent dropdown.
     * Kategori starts empty — populated via AJAX when kelompok is selected.
     */
    public function create(): View
    {
        $kelompoks = KelompokProduk::orderBy('nama_kelompok')->get();
        $kategoris = collect(); // Empty — populated via AJAX chained dropdown
        $produk    = null;      // Null signals "create mode" to the shared form view

        return view('pages.master.produk.form', compact('kelompoks', 'kategoris', 'produk'));
    }

    /**
     * Store a newly created product.
     * Barcode uniqueness is scoped to the current toko (multi-tenant safe).
     */
    public function store(StoreProdukRequest $request): RedirectResponse
    {
        $this->authorize('create', Produk::class);
        $validated = $request->validated();

        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')->store('produk', 'public');
        }

        Produk::create($validated);

        return redirect()
            ->route('master.produk.index')
            ->with('success', "Produk \"{$validated['nama_produk']}\" berhasil ditambahkan.");
    }

    /**
     * Show the product edit form.
     * Pre-loads the categories for the product's current kelompok
     * so the chained dropdown renders correctly without an AJAX call on mount.
     */
    public function edit(Produk $produk): View
    {
        $kelompoks          = KelompokProduk::orderBy('nama_kelompok')->get();
        $selectedKelompokId = $produk->kategori?->kelompok_id;

        // Pre-load the categories for the selected kelompok
        $kategoris = $selectedKelompokId
            ? KategoriProduk::where('kelompok_id', $selectedKelompokId)
                ->orderBy('nama_kategori')
                ->get()
            : collect();

        return view('pages.master.produk.form', compact('produk', 'kelompoks', 'kategoris', 'selectedKelompokId'));
    }

    /**
     * Update an existing product.
     * Model binding applies the Global Scope — only own-tenant products are accessible.
     */
    public function update(UpdateProdukRequest $request, Produk $produk): RedirectResponse
    {
        $this->authorize('update', $produk);
        $validated = $request->validated();

        if ($request->hasFile('gambar')) {
            if ($produk->gambar && \Illuminate\Support\Facades\Storage::disk('public')->exists($produk->gambar)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($produk->gambar);
            }
            $validated['gambar'] = $request->file('gambar')->store('produk', 'public');
        }

        $produk->update($validated);

        return redirect()
            ->route('master.produk.index')
            ->with('success', "Produk \"{$validated['nama_produk']}\" berhasil diperbarui.");
    }

    /**
     * Delete a product.
     *
     * Guards:
     *   - Cannot delete if the product has been used in any penjualan (sales).
     *   - Cannot delete if the product has been used in any pembelian (purchases).
     *   Both checks prevent orphaned transaction line items and broken reports.
     */
    public function destroy(Produk $produk): RedirectResponse
    {
        $this->authorize('delete', $produk);
        if ($produk->penjualanDetails()->exists()) {
            return redirect()
                ->route('master.produk.index')
                ->with('error', "Produk \"{$produk->nama_produk}\" tidak dapat dihapus karena sudah tercatat dalam transaksi penjualan.");
        }

        if ($produk->pembelianDetails()->exists()) {
            return redirect()
                ->route('master.produk.index')
                ->with('error', "Produk \"{$produk->nama_produk}\" tidak dapat dihapus karena sudah tercatat dalam transaksi pembelian.");
        }

        $nama = $produk->nama_produk;
        if ($produk->gambar && \Illuminate\Support\Facades\Storage::disk('public')->exists($produk->gambar)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($produk->gambar);
        }
        $produk->delete();

        return redirect()
            ->route('master.produk.index')
            ->with('success', "Produk \"{$nama}\" berhasil dihapus.");
    }

    // ─── Export Methods ──────────────────────────────────────────────────

    /**
     * Export produk ke file CSV.
     * Mendukung filter: search (nama/barcode) dan nama_kategori.
     * Menggunakan StreamedResponse agar tidak memuat semua data ke memori sekaligus.
     *
     * Route: GET /master/produk/export-csv
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $search             = $request->input('search');
        $namaKategoriFilter = $request->input('nama_kategori');
        $namaKelompokFilter = $request->input('nama_kelompok');

        $query = Produk::with(['kategori.kelompok']);

        if (!empty($namaKelompokFilter)) {
            $query->whereHas('kategori.kelompok', fn ($q) => $q->where('nama_kelompok', $namaKelompokFilter));
        }
        if (!empty($namaKategoriFilter)) {
            $query->whereHas('kategori', fn ($q) => $q->where('nama_kategori', $namaKategoriFilter));
        }
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_produk', 'like', "%{$search}%")
                  ->orWhere('barcode',   'like', "%{$search}%");
            });
        }

        $produk   = $query->orderBy('nama_produk')->get();
        $namaToko = auth()->user()->toko->nama_toko ?? 'toko';
        $fileName = 'produk_' . str_replace(' ', '_', strtolower($namaToko)) . '_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->streamDownload(function () use ($produk) {
            // BOM UTF-8 agar Excel langsung baca encoding Indonesia dengan benar
            echo "\xEF\xBB\xBF";

            $handle = fopen('php://output', 'w');

            // ── Header Kolom ──────────────────────────────────────────────────
            fputcsv($handle, [
                'No',
                'Barcode',
                'Nama Produk',
                'Kelompok',
                'Kategori',
                'Satuan',
                'Harga Modal',
                'Harga Jual Umum',
                'Harga Jual Member',
                'Harga Jual Rekan',
                'Harga Jual Motoris',
                'Stok',
                'Stok Minimum',
                'Status Stok',
            ], ';');

            // ── Data Rows ─────────────────────────────────────────────────────
            foreach ($produk as $index => $prod) {
                fputcsv($handle, [
                    $index + 1,
                    $prod->barcode,
                    $prod->nama_produk,
                    $prod->kategori?->kelompok?->nama_kelompok ?? '-',
                    $prod->kategori?->nama_kategori ?? '-',
                    $prod->satuan,
                    (float) $prod->harga_modal,
                    (float) $prod->harga_jual_umum,
                    (float) $prod->harga_jual_member,
                    (float) $prod->harga_jual_rekan,
                    (float) $prod->harga_jual_motoris,
                    $prod->stok,
                    $prod->stok_minimum,
                    $prod->isStokRendah() ? 'Stok Rendah' : 'Normal',
                ], ';');
            }

            fclose($handle);
        }, $fileName, $headers);
    }

    /**
     * Download template CSV kosong untuk panduan import produk secara massal.
     * Template berisi: header kolom + 2 baris contoh data.
     *
     * Route: GET /master/produk/download-template
     */
    public function downloadTemplate(): StreamedResponse
    {
        $fileName = 'template_import_produk.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->streamDownload(function () {
            echo "\xEF\xBB\xBF"; // BOM UTF-8

            $handle = fopen('php://output', 'w');

            // ── Header ──────────────────────────────────────────────────────
            fputcsv($handle, [
                'barcode',
                'nama_produk',
                'nama_kelompok',
                'nama_kategori',
                'satuan',
                'harga_modal',
                'harga_jual_umum',
                'harga_jual_member',
                'harga_jual_rekan',
                'harga_jual_motoris',
                'stok',
                'stok_minimum',
            ], ';');

            // ── Baris Keterangan (komentar panduan untuk user) ──────────────
            fputcsv($handle, [
                '# Wajib diisi (maks 50 karakter)',
                '# Wajib diisi (maks 150 karakter)',
                '# Opsional — dibuat otomatis jika belum ada (contoh: Sembako)',
                '# Opsional — dibuat otomatis jika belum ada (contoh: Mie Instan)',
                '# Opsional — default: Pcs (contoh: Pcs, Pack, Dus, Bks)',
                '# Opsional — default: 0 (contoh: 3000)',
                '# Opsional — default: 0',
                '# Opsional — default: 0',
                '# Opsional — default: 0',
                '# Opsional — default: 0',
                '# Opsional — default: 0',
                '# Opsional — default: 5',
            ], ';');

            // ── Contoh Baris 1 ───────────────────────────────────────────────
            fputcsv($handle, [
                '89910001',
                'Buku Tulis Sidu 38 Lembar',
                'Alat Tulis',
                'Buku',
                'Pcs',
                '3000',
                '4000',
                '3800',
                '3600',
                '3400',
                '50',
                '10',
            ], ';');

            // ── Contoh Baris 2 ───────────────────────────────────────────────
            fputcsv($handle, [
                '89920002',
                'Indomie Goreng Spesial',
                'Sembako',
                'Mie Instan',
                'Bks',
                '2500',
                '3500',
                '3300',
                '3100',
                '2900',
                '120',
                '20',
            ], ';');

            fclose($handle);
        }, $fileName, $headers);
    }

    /**
     * Tampilkan halaman panduan export & template produk.
     *
     * Route: GET /master/produk/panduan-export
     */
    public function panduanExport(): View
    {
        $totalProduk    = Produk::count();
        $stokRendah     = Produk::whereColumn('stok', '<=', 'stok_minimum')->count();
        $categories     = KategoriProduk::with('kelompok')->orderBy('nama_kategori')->get();

        return view('pages.master.produk.export-guide', compact('totalProduk', 'stokRendah', 'categories'));
    }

    /**
     * Tampilkan halaman import produk via CSV.
     * Menyertakan daftar kategori yang tersedia, panduan, dan form upload.
     *
     * Route: GET /master/produk/import
     */
    public function showImport(): View
    {
        $categories = KategoriProduk::with('kelompok')->orderBy('nama_kategori')->get();
        return view('pages.master.produk.import-guide', compact('categories'));
    }

    /**
     * Proses import produk dari file CSV dengan logika fleksibel.
     *
     * Fitur baru (versi lenient):
     *   - Deteksi kolom otomatis berdasarkan header (urutan kolom bebas)
     *   - nama_kelompok & nama_kategori auto-create jika belum ada
     *   - Validasi longgar: hanya barcode & nama_produk yang wajib
     *   - Kolom kosong pakai nilai default, tidak dianggap error
     *   - Baris dengan field wajib kosong → dilewati dengan info, baris lain tetap diproses
     *   - Angka negatif di-clamp ke 0
     *
     * Route: POST /master/produk/import
     */
    public function importCsv(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ], [
            'csv_file.required' => 'File CSV wajib dipilih.',
            'csv_file.mimes'    => 'File harus bertipe CSV (.csv).',
            'csv_file.max'      => 'Ukuran file maksimal 5 MB.',
        ]);

        $file   = $request->file('csv_file');
        $tokoId = auth()->user()->toko_id;

        // ── Parse CSV content ────────────────────────────────────────────
        $content = file_get_contents($file->getRealPath());

        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }

        $lines = preg_split('/\r\n|\r|\n/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $lines = array_values(array_filter($lines, fn ($l) => trim($l) !== ''));

        $infoMsgs       = [];
        $inserted       = 0;
        $updated        = 0;
        $skipped        = 0;
        $newKelompokCount = 0;
        $newKategoriCount = 0;

        if (count($lines) < 1) {
            return redirect()->back()
                ->with('error', 'File CSV kosong.')
                ->withInput();
        }

        // ── Header aliases untuk deteksi kolom fleksibel ────────────────
        $aliases = [
            'barcode'            => ['barcode', 'kode', 'code'],
            'nama_produk'        => ['nama_produk', 'nama', 'produk', 'name'],
            'nama_kelompok'      => ['nama_kelompok', 'kelompok', 'group'],
            'nama_kategori'      => ['nama_kategori', 'kategori', 'category'],
            'satuan'             => ['satuan', 'unit'],
            'harga_modal'        => ['harga_modal', 'modal', 'cost'],
            'harga_jual_umum'    => ['harga_jual_umum', 'umum', 'harga_jual', 'jual'],
            'harga_jual_member'  => ['harga_jual_member', 'member'],
            'harga_jual_rekan'   => ['harga_jual_rekan', 'rekan'],
            'harga_jual_motoris' => ['harga_jual_motoris', 'motoris'],
            'stok'               => ['stok', 'stock', 'qty'],
            'stok_minimum'       => ['stok_minimum', 'minimum', 'min', 'stok_min'],
        ];

        // ── Deteksi baris header & buat column map ───────────────────
        $colMap    = null;
        $dataStart = 0;

        foreach ($lines as $idx => $rawLine) {
            $rawLine = trim($rawLine);
            if (empty($rawLine) || str_starts_with($rawLine, '#')) {
                continue;
            }
            $cols  = str_getcsv($rawLine, ';', '"', '\\');
            $first = strtolower(trim($cols[0] ?? ''));

            if (in_array($first, $aliases['barcode'], true)) {
                $colMap = [];
                foreach ($cols as $i => $colName) {
                    $colName = strtolower(trim($colName));
                    foreach ($aliases as $field => $names) {
                        if (in_array($colName, $names, true) && !isset($colMap[$field])) {
                            $colMap[$field] = $i;
                            break;
                        }
                    }
                }
                $dataStart = $idx + 1;
            }
            break;
        }

        // Positional fallback bila tidak ada header
        if ($colMap === null) {
            $colMap = [
                'barcode'            => 0,
                'nama_produk'        => 1,
                'nama_kelompok'      => 2,
                'nama_kategori'      => 3,
                'satuan'             => 4,
                'harga_modal'        => 5,
                'harga_jual_umum'    => 6,
                'harga_jual_member'  => 7,
                'harga_jual_rekan'   => 8,
                'harga_jual_motoris' => 9,
                'stok'               => 10,
                'stok_minimum'       => 11,
            ];
        }

        // Helper untuk ambil nilai kolom berdasarkan colMap
        $getCol = function (array $cols, string $field, string $default = '') use ($colMap): string {
            $idx = $colMap[$field] ?? null;
            if ($idx === null || !isset($cols[$idx])) {
                return $default;
            }
            return trim($cols[$idx]);
        };

        // ── Pre-load kelompok & kategori ke dalam cache map ────────────
        $kelompokMap = [];
        foreach (KelompokProduk::withoutGlobalScopes()->where('toko_id', $tokoId)->get() as $k) {
            $kelompokMap[strtolower($k->nama_kelompok)] = $k->id;
        }

        $kategoriByKelompok = []; // "lower_kelompok::lower_kategori" => id
        $kategoriByName     = []; // "lower_kategori" => id (fallback bila kelompok kosong)
        foreach (KategoriProduk::withoutGlobalScopes()->where('toko_id', $tokoId)->with('kelompok')->get() as $kat) {
            $kel = strtolower($kat->kelompok?->nama_kelompok ?? '');
            $nam = strtolower($kat->nama_kategori);
            $kategoriByKelompok["{$kel}::{$nam}"] = $kat->id;
            $kategoriByName[$nam] = $kat->id;
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            foreach ($lines as $lineNumber => $rawLine) {
                $actualLineNumber = $lineNumber + 1;
                $rawLine = trim($rawLine, "\r\n\t ");

                if (empty($rawLine)) continue;
                if (str_starts_with($rawLine, '#')) { $skipped++; continue; }
                if ($lineNumber < $dataStart) continue;

                $cols = str_getcsv($rawLine, ';', '"', '\\');

                // ── Extract values (semua punya default) ──────────────
                $barcode      = $getCol($cols, 'barcode');
                $namaProduk   = $getCol($cols, 'nama_produk');
                $namaKelompok = $getCol($cols, 'nama_kelompok');
                $namaKategori = $getCol($cols, 'nama_kategori');
                $satuan       = $getCol($cols, 'satuan', 'Pcs');
                $hargaModal   = $this->parseNumber($getCol($cols, 'harga_modal', '0'));
                $hargaUmum    = $this->parseNumber($getCol($cols, 'harga_jual_umum', '0'));
                $hargaMember  = $this->parseNumber($getCol($cols, 'harga_jual_member', '0'));
                $hargaRekan   = $this->parseNumber($getCol($cols, 'harga_jual_rekan', '0'));
                $hargaMotoris = $this->parseNumber($getCol($cols, 'harga_jual_motoris', '0'));
                $stok         = (int) $this->parseNumber($getCol($cols, 'stok', '0'));
                $stokMinimum  = (int) $this->parseNumber($getCol($cols, 'stok_minimum', '5'));

                // Skip baris komentar yang lolos
                if (str_starts_with($barcode, '#')) { $skipped++; continue; }

                // ── Validasi longgar: hanya barcode & nama_produk wajib ──
                if (empty($barcode)) {
                    $infoMsgs[] = "Baris {$actualLineNumber}: Barcode kosong — dilewati.";
                    continue;
                }
                if (empty($namaProduk)) {
                    $infoMsgs[] = "Baris {$actualLineNumber} ({$barcode}): Nama produk kosong — dilewati.";
                    continue;
                }
                if (strlen($barcode) > 50) {
                    $infoMsgs[] = "Baris {$actualLineNumber} ({$barcode}): Barcode melebihi 50 karakter — dilewati.";
                    continue;
                }
                if (strlen($namaProduk) > 150) {
                    $infoMsgs[] = "Baris {$actualLineNumber} ({$barcode}): Nama produk melebihi 150 karakter — dilewati.";
                    continue;
                }

                // Clamp angka negatif ke 0
                $hargaModal   = max(0, $hargaModal);
                $hargaUmum    = max(0, $hargaUmum);
                $hargaMember  = max(0, $hargaMember);
                $hargaRekan   = max(0, $hargaRekan);
                $hargaMotoris = max(0, $hargaMotoris);
                $stok         = max(0, $stok);
                $stokMinimum  = max(0, $stokMinimum);

                // ── Resolve kelompok (auto-create) ────────────────────
                $namaKelompok = $namaKelompok ?: 'Lainnya';
                $kelompokKey  = strtolower($namaKelompok);

                if (!isset($kelompokMap[$kelompokKey])) {
                    $newKelompok = KelompokProduk::withoutGlobalScopes()->create([
                        'toko_id'       => $tokoId,
                        'nama_kelompok' => $namaKelompok,
                    ]);
                    $kelompokMap[$kelompokKey] = $newKelompok->id;
                    $newKelompokCount++;
                }
                $kelompokId = $kelompokMap[$kelompokKey];

                // ── Resolve kategori (auto-create) ────────────────────
                $namaKategori = $namaKategori ?: $namaKelompok ?: 'Lainnya';
                $kategoriKey  = strtolower($namaKelompok . '::' . $namaKategori);

                if (isset($kategoriByKelompok[$kategoriKey])) {
                    $kategoriId = $kategoriByKelompok[$kategoriKey];
                } elseif (!empty($namaKelompok) && $namaKelompok === 'Lainnya' && isset($kategoriByName[strtolower($namaKategori)])) {
                    // Kelompok kosong & kategori sudah ada di kelompok manapun → pakai yang ada
                    $kategoriId = $kategoriByName[strtolower($namaKategori)];
                } else {
                    // Buat kategori baru
                    $newKategori = KategoriProduk::withoutGlobalScopes()->create([
                        'toko_id'      => $tokoId,
                        'kelompok_id' => $kelompokId,
                        'nama_kategori' => $namaKategori,
                    ]);
                    $kategoriId = $newKategori->id;
                    $kategoriByKelompok[$kategoriKey] = $kategoriId;
                    $kategoriByName[strtolower($namaKategori)] = $kategoriId;
                    $newKategoriCount++;
                }

                // ── Upsert produk ──────────────────────────────────────
                $existing = Produk::withoutGlobalScopes()
                    ->where('toko_id', $tokoId)
                    ->where('barcode', $barcode)
                    ->first();

                $data = [
                    'kategori_id'        => $kategoriId,
                    'nama_produk'        => $namaProduk,
                    'satuan'             => $satuan ?: 'Pcs',
                    'harga_modal'        => $hargaModal,
                    'harga_jual_umum'    => $hargaUmum,
                    'harga_jual_member'  => $hargaMember,
                    'harga_jual_rekan'   => $hargaRekan,
                    'harga_jual_motoris' => $hargaMotoris,
                    'stok'               => $stok,
                    'stok_minimum'       => $stokMinimum,
                ];

                if ($existing) {
                    $existing->update($data);
                    $updated++;
                } else {
                    Produk::withoutGlobalScopes()->create(array_merge($data, [
                        'toko_id' => $tokoId,
                        'barcode' => $barcode,
                    ]));
                    $inserted++;
                }
            }

            // ── Tidak ada data tersimpan → rollback ──────────────────────
            if (($inserted + $updated) === 0) {
                \Illuminate\Support\Facades\DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Import gagal — tidak ada data valid yang bisa disimpan.')
                    ->with('import_info', $infoMsgs)
                    ->withInput();
            }

            \Illuminate\Support\Facades\DB::commit();

            // ── Build ringkasan ───────────────────────────────────────────
            $parts = [];
            if ($inserted > 0)         $parts[] = "{$inserted} produk baru";
            if ($updated > 0)          $parts[] = "{$updated} produk diperbarui";
            if ($newKelompokCount > 0) $parts[] = "{$newKelompokCount} kelompok baru dibuat";
            if ($newKategoriCount > 0) $parts[] = "{$newKategoriCount} kategori baru dibuat";
            if ($skipped > 0)          $parts[] = "{$skipped} baris komentar dilewati";

            $msg = 'Import berhasil: ' . implode(', ', $parts) . '.';

            if (!empty($infoMsgs)) {
                $msg .= ' ' . count($infoMsgs) . ' baris dilewati karena data wajib kosong.';
            }

            return redirect()->route('master.produk.index')
                ->with('success', $msg)
                ->with('import_info', $infoMsgs);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('ProdukController@importCsv error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan sistem saat import: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Parse string angka dari CSV: hapus titik ribuan, ganti koma desimal.
     * Contoh: "5.000" → 5000, "3,5" → 3.5, "2500" → 2500
     */
    private function parseNumber(string $value): float
    {
        $value = trim($value);
        // Hapus titik ribuan (jika diikuti 3 digit)
        $value = preg_replace('/\.(\d{3})/', '$1', $value);
        // Ganti koma desimal jadi titik
        $value = str_replace(',', '.', $value);
        return is_numeric($value) ? (float) $value : 0;
    }

}
