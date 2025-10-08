<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Period extends Model
{
    use HasFactory;

    // If you used natural id (1..12) you may want to disable incrementing:
    public $incrementing = true; // keep default if you used bigIncrements
    protected $fillable = ['year'];

    public function weeks()
    {
        return $this->hasMany(Week::class);
    }

    public function kpiRecords()
    {
        return $this->hasMany(KpiRecord::class);
    }

    public function scopeYear($q, $year)
    {
        return $q->where('year', $year);
    }
}
