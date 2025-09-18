<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\KpiImport;
use App\Imports\ComplianceImport;
use Illuminate\Support\Facades\Log;

class ExcelImportController extends Controller
{
    public function importKpi(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,xlsm,csv'],
        ]);

        try {
            Excel::import(new KpiImport, $request->file('file'));
            return back()->with('success', 'KPI data imported successfully.');
        } catch (\Throwable $e) {
            Log::error('KPI Import failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'KPI import failed: ' . $e->getMessage());
        }
    }

    public function importCompliance(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,xlsm,csv'],
        ]);

        try {
            Excel::import(new ComplianceImport, $request->file('file'));
            return back()->with('success', 'Compliance data imported successfully.');
        } catch (\Throwable $e) {
            Log::error('Compliance Import failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Compliance import failed: ' . $e->getMessage());
        }
    }
}
