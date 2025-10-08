<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\{Pub, Period, KpiCategory, KpiRecord};

class KpiImportController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt,xlsx']);

        $rows = Excel::toCollection(null, $request->file('file'))->first();

        if (!$rows || $rows->count() < 2) {
            return response()->json(['message' => 'No data rows found'], 422);
        }

        // Expected columns (flexible header names):
        // pub_number | period | kpi_code | value
        $header = collect($rows->first())->map(fn($v) => strtolower(trim((string)$v)));
        $map = [
            'pub_number' => $header->search('pub_number'),
            'period'     => $header->search('period'),
            'kpi_code'   => $header->search('kpi_code'),
            'value'      => $header->search('value'),
        ];

        if (in_array(false, $map, true)) {
            return response()->json(['message' => 'Expected headers: pub_number, period, kpi_code, value'], 422);
        }

        $created = 0; $updated = 0;

        foreach ($rows->skip(1) as $r) {
            $pubNo = trim((string)($r[$map['pub_number']] ?? ''));
            $periodNo = (int) ($r[$map['period']] ?? 0);
            $kpiCode = trim((string)($r[$map['kpi_code']] ?? ''));
            $value = is_numeric($r[$map['value']] ?? null) ? (float)$r[$map['value']] : null;

            if (!$pubNo || !$periodNo || !$kpiCode) continue;

            $pub = Pub::firstOrCreate(['pub_number' => $pubNo], ['name' => "Pub $pubNo"]);
            $period = Period::firstOrCreate(['id' => $periodNo], ['year' => now()->year]);
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
    }
}
