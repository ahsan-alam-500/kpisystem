<?php

namespace App\Imports;

use App\Models\Kpi;
use App\Models\PubNumber;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class KpiImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public function headingRow(): int
    {
        return 1; // header row index
    }

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                if (!$row || $row->filter()->isEmpty()) continue;

                // Flexible key mapping
                $pubNumber = $this->val($row, ['pub number','pub_number','pub','pub no','pubno']);
                $metric    = $this->val($row, ['kpi name','kpi','metric','name']);
                $value     = $this->val($row, ['value','kpi value','val']);
                $weekStart = $this->val($row, ['week start','week_start','week']);
                $desc      = $this->val($row, ['description','desc','remarks','note']);

                if (blank($pubNumber) || blank($metric)) {
                    continue; // essential fields missing → skip row
                }

                // Find or create Pub
                $pub = PubNumber::firstOrCreate(
                    ['pub_number' => trim($pubNumber)],
                    ['title' => null]
                );

                // Parse value
                $val = is_numeric($value) ? (float)$value : null;

                // Parse date (supports Excel serial dates or string)
                $weekDate = $this->parseDate($weekStart);

                Kpi::create([
                    'pub_id'      => $pub->id,
                    'name'        => trim($metric),
                    'value'       => $val ?? 0,
                    'week_start'  => $weekDate?->toDateString(),
                    'description' => $desc,
                ]);
            }
        });
    }

    private function val($row, array $keys, $default=null)
    {
        foreach ($keys as $k) {
            $key = Str::slug($k, '_');
            // try exact
            if (isset($row[$key])) return $row[$key];
            // try original
            if (isset($row[$k])) return $row[$k];
            // try loose: remove spaces/case
            foreach ($row as $col => $v) {
                if (Str::lower(Str::slug($col,'_')) === Str::lower($key)) {
                    return $v;
                }
            }
        }
        return $default;
    }

    private function parseDate($value): ?Carbon
    {
        if (blank($value)) return null;

        // Excel serial date
        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float)$value));
            } catch (\Throwable $e) {}
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
