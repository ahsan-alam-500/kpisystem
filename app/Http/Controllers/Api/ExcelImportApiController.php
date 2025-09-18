<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\KpiImport;
use App\Imports\ComplianceImport;

class ExcelImportApiController extends Controller
{
    public function importKpi(Request $request)
    {
        $request->validate(['file'=>'required|file|mimes:xlsx,xls,xlsm,csv']);
        Excel::import(new KpiImport, $request->file('file'));
        return response()->json(['message'=>'KPI imported successfully']);
    }

    public function importCompliance(Request $request)
    {
        $request->validate(['file'=>'required|file|mimes:xlsx,xls,xlsm,csv']);
        Excel::import(new ComplianceImport, $request->file('file'));
        return response()->json(['message'=>'Compliance imported successfully']);
    }
}
