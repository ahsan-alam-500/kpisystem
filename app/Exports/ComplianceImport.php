<?php

namespace App\Imports;

use App\Models\Manager;
use App\Models\PubNumber;
use App\Models\ComplianceScore;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ComplianceImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public function headingRow(): int
    {
        return 1;
    }

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                if (!$row || $row->filter()->isEmpty()) continue;

                $pubNumber   = $this->val($row, ['pub number', 'pub_number', 'pub', 'pub no', 'pubno']);
                $managerName = $this->val($row, ['manager', 'manager name', 'name']);
                $managerMail = $this->val($row, ['email', 'manager email', 'manager_email']);
                $score       = $this->val($row, ['score', 'compliance', 'compliance %', 'compliance_percent']);
                $weekStart   = $this->val($row, ['week start', 'week_start', 'week']);

                if (blank($pubNumber) || (blank($managerName) && blank($managerMail))) {
                    continue;
                }

                $pub = PubNumber::firstOrCreate(
                    ['pub_number' => trim($pubNumber)],
                    ['title' => null]
                );

                $manager = Manager::firstOrCreate(
                    ['email' => $managerMail ?: null, 'name' => $managerName ?: 'Unknown'],
                    ['name' => $managerName ?: ($managerMail ?? 'Unknown')]
                );

                $scoreVal = is_numeric($score) ? (int) $score : null;
                $weekDate = $this->parseDate($weekStart);

                ComplianceScore::create([
                    'manager_id' => $manager->id,
                    'pub_id'     => $pub->id,
                    'score'      => $scoreVal ?? 0,
                    'week_start' => $weekDate?->toDateString(),
                ]);
            }
        });
    }

    private function val($row, array $keys, $default = null)
    {
        foreach ($keys as $k) {
            $key = Str::slug($k, '_');
            if (isset($row[$key])) return $row[$key];
            if (isset($row[$k])) return $row[$k];
            foreach ($row as $col => $v) {
                if (Str::lower(Str::slug($col, '_')) === Str::lower($key)) {
                    return $v;
                }
            }
        }
        return $default;
    }

    private function parseDate($value): ?Carbon
    {
        if (blank($value)) return null;

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float)$value));
            } catch (\Throwable $e) {
            }
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
