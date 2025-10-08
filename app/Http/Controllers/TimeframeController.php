<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Period, Week};

class TimeframeController extends Controller
{
    public function periods()
    {
        return response()->json(
            Period::query()->orderBy('id')->get(['id','year'])
        );
    }

    public function weeks(Period $period)
    {
        return response()->json(
            $period->weeks()->orderBy('week_no')->get(['id','period_id','week_no','start_date','end_date'])
        );
    }
}
