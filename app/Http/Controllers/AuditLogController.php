<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $modul  = $request->input('modul');

        $query = AuditLog::with('user')->orderBy('id', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('aktivitas', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($qu) use ($search) {
                      $qu->where('nama_lengkap', 'like', "%{$search}%")
                         ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }

        if ($modul) {
            $query->where('modul', $modul);
        }

        $logs = $query->paginate(20);
        $modules = AuditLog::select('modul')->distinct()->pluck('modul');

        return view('pages.pengaturan.audit-log.index', compact('logs', 'search', 'modul', 'modules'));
    }
}
