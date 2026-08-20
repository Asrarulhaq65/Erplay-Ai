<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Import/export route facade for the product module.
 *
 * The implementation remains in ProdukController for this compatibility step
 * so existing CSV behavior is not duplicated or altered. The route surface is
 * isolated here and can be extracted into a service in a follow-up change.
 */
class ProdukImportExportController extends ProdukController
{
    public function exportCsv(Request $request): StreamedResponse
    {
        return parent::exportCsv($request);
    }

    public function downloadTemplate(): StreamedResponse
    {
        return parent::downloadTemplate();
    }

    public function panduanExport(): View
    {
        return parent::panduanExport();
    }

    public function showImport(): View
    {
        return parent::showImport();
    }

    public function importCsv(Request $request): RedirectResponse
    {
        return parent::importCsv($request);
    }
}
