<?php

use App\Imports\RowsImport;
use App\Models\KpiRecord;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Api\PubController;
use App\Http\Controllers\Api\KpiImportController;
use App\Http\Controllers\Api\ShiftComplianceController;
use App\Http\Controllers\Api\CompareController;
use App\Http\Controllers\Api\TimeframeController;
use App\Models\KpiCategory;
use App\Models\Period;
use App\Models\Pub;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

//clear all cache
Route::get('cc', function() {
    Artisan::call('cache:clear');
    Artisan::call('optimize');
    return "Cache is cleared";
});

// Auth
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('register', [AuthController::class, 'register']);
Route::post('refresh', [AuthController::class, 'refresh']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');

// Public meta (optional)
Route::get('/health', fn() => response()->json(['ok' => true]));

// Protected
Route::middleware('auth:api')->group(function () {

    // current user
    Route::get('/me', [ProfileController::class, 'me']);

    // pubs
    Route::get('/pubs', [PubController::class, 'index']);                 // list + filters
    Route::post('/pubs', [PubController::class, 'store'])->middleware('role:Admin|Manager');
    Route::get('/pubs/{pub}', [PubController::class, 'show']);
    Route::put('/pubs/{pub}', [PubController::class, 'update'])->middleware('role:Admin|Manager');
    Route::delete('/pubs/{pub}', [PubController::class, 'destroy'])->middleware('role:Admin');

    // KPI import (csv/xlsx)
    // Route::post('/kpi/import', [KpiImportController::class, 'store']);

    Route::post('/kpi/import', function(Request $request){
         Log::info("\n\n"."rows paisi dhukse");
        //  $hello = $request->validate(['file' => 'required|file|mimes:csv,xlsx,xls']);

        return response()->json($request->all());

        // try {
        //     // ✅ Excel থেকে কালেকশন নেয়ার সঠিক উপায় (null দেবেন না)
        //     $import = new RowsImport();
        //     Excel::import($import, $request->file('file'));

        //     $rows = $import->rows->first(); // প্রথম শিট
        //     Log::info($rows."\n\n"."rows paisi");
        //     if (!$rows || $rows->count() < 2) {
        //         return response()->json(['message' => 'No data rows found'], 422);
        //     }

        //     // Expected columns: pub_number | period | kpi_code | value
        //     $header = collect($rows->first())->map(fn($v) => strtolower(trim((string) $v)));
        //     $map = [
        //         'pub_number' => $header->search('pub_number'),
        //         'period'     => $header->search('period'),
        //         'kpi_code'   => $header->search('kpi_code'),
        //         'value'      => $header->search('value'),
        //     ];

        //     Log::info($map."\n\n"."map paisi");

        //     if (in_array(false, $map, true)) {
        //         return response()->json([
        //             'message' => 'Expected headers: pub_number, period, kpi_code, value'
        //         ], 422);
        //     }

        //     $created = 0; $updated = 0;

        //     foreach ($rows->skip(1) as $r) {
        //         $pubNo    = trim((string)($r[$map['pub_number']] ?? ''));
        //         $periodNo = (int) ($r[$map['period']] ?? 0);
        //         $kpiCode  = trim((string)($r[$map['kpi_code']] ?? ''));
        //         $vRaw     = $r[$map['value']] ?? null;
        //         $value    = is_numeric($vRaw) ? (float) $vRaw : null;

        //         if (!$pubNo || !$periodNo || !$kpiCode) continue;

        //         $pub = Pub::firstOrCreate(['pub_number' => $pubNo], ['name' => "Pub $pubNo"]);

        //         // ✅ id mass-assign না করে সেফলি Period তৈরি/খোঁজা
        //         $period = Period::find($periodNo);
        //         if (!$period) {
        //             $period = new Period();
        //             $period->id   = $periodNo;       // যদি তোমার লজিকে periodNo-ই PK হয়
        //             $period->year = now()->year;
        //             $period->save();
        //         }

        //         $cat = KpiCategory::firstOrCreate(['code' => $kpiCode], ['name' => $kpiCode]);

        //         $rec = KpiRecord::updateOrCreate(
        //             ['pub_id' => $pub->id, 'period_id' => $period->id, 'kpi_category_id' => $cat->id],
        //             ['value' => $value]
        //         );

        //         $rec->wasRecentlyCreated ? $created++ : $updated++;
        //     }

        //     return response()->json([
        //         'message' => 'KPI import complete',
        //         'created' => $created,
        //         'updated' => $updated
        //     ]);

        // } catch (Throwable $e) {
        //     // ✅ Error লগ হবে
        //     \Log::error('KPI import failed', [
        //         'message' => $e->getMessage(),
        //         'file'    => $e->getFile(),
        //         'line'    => $e->getLine(),
        //     ]);

        //     // ✅ Local এ ডিটেইল দেখাবে, প্রোড এ জেনেরিক মেসেজ
        //     return response()->json([
        //         'message' => 'Import failed',
        //         'error'   => app()->environment('local') ? $e->getMessage() : 'Server Error'
        //     ], 500);
        // }
    });


    // shift compliance (pub-week)
    Route::get('/shift', [ShiftComplianceController::class, 'index']); // query by pub_id, week_id
    Route::post('/shift', [ShiftComplianceController::class, 'upsert']); // create/update checklist
    Route::get('/shift/{id}', [ShiftComplianceController::class, 'show']); // single shift compliance

    // compare two weeks of a pub
    Route::get('/compare', [CompareController::class, 'compare']); // ?pub_id=&week_a=&week_b=

    // time frames helper (periods & weeks)
    Route::get('/periods', [TimeframeController::class, 'periods']);
    Route::get('/periods/{period}/weeks', [TimeframeController::class, 'weeks']);
});
