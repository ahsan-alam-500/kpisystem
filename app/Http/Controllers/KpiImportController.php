<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RowsImport;
use App\Models\{Pub, Period, KpiCategory, KpiRecord};

class KpiImportController extends Controller
{
    public function store(Request $request)
    {
        // ✅ txt বাদ দিলাম; xls যুক্ত করলাম
        $request->validate(['file' => 'required|file|mimes:csv,xlsx,xls']);

        try {
            // ✅ Excel থেকে কালেকশন নেয়ার সঠিক উপায় (null দেবেন না)
            $import = new RowsImport();
            Excel::import($import, $request->file('file'));

            $rows = $import->rows->first(); // প্রথম শিট

            if (!$rows || $rows->count() < 2) {
                return response()->json(['message' => 'No data rows found'], 422);
            }

            // Expected columns: pub_number | period | kpi_code | value
            $header = collect($rows->first())->map(fn($v) => strtolower(trim((string) $v)));
            $map = [
                'pub_number' => $header->search('pub_number'),
                'period'     => $header->search('period'),
                'kpi_code'   => $header->search('kpi_code'),
                'value'      => $header->search('value'),
            ];

            if (in_array(false, $map, true)) {
                return response()->json([
                    'message' => 'Expected headers: pub_number, period, kpi_code, value'
                ], 422);
            }

            $created = 0; $updated = 0;

            foreach ($rows->skip(1) as $r) {
                $pubNo    = trim((string)($r[$map['pub_number']] ?? ''));
                $periodNo = (int) ($r[$map['period']] ?? 0);
                $kpiCode  = trim((string)($r[$map['kpi_code']] ?? ''));
                $vRaw     = $r[$map['value']] ?? null;
                $value    = is_numeric($vRaw) ? (float) $vRaw : null;

                if (!$pubNo || !$periodNo || !$kpiCode) continue;

                $pub = Pub::firstOrCreate(['pub_number' => $pubNo], ['name' => "Pub $pubNo"]);

                // ✅ id mass-assign না করে সেফলি Period তৈরি/খোঁজা
                $period = Period::find($periodNo);
                if (!$period) {
                    $period = new Period();
                    $period->id   = $periodNo;       // যদি তোমার লজিকে periodNo-ই PK হয়
                    $period->year = now()->year;
                    $period->save();
                }

                $cat = KpiCategory::firstOrCreate(['code' => $kpiCode], ['name' => $kpiCode]);

                $rec = KpiRecord::updateOrCreate(
                    ['pub_id' => $pub->id, 'period_id' => $period->id, 'kpi_category_id' => $cat->id],
                    ['value' => $value]
                );

                $rec->wasRecentlyCreated ? $created++ : $updated++;
            }

            return response()->json([
                'message' => 'KPI import complete',
                'created' => $created,
                'updated' => $updated
            ]);

        } catch (Throwable $e) {
            // ✅ Error লগ হবে
            Log::error('KPI import failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            // ✅ Local এ ডিটেইল দেখাবে, প্রোড এ জেনেরিক মেসেজ
            return response()->json([
                'message' => 'Import failed',
                'error'   => app()->environment('local') ? $e->getMessage() : 'Server Error'
            ], 500);
        }
    }
}
