<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Week extends Model
{
    use HasFactory;

    protected $fillable = ['period_id','week_no','start_date','end_date'];
    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function period()
    {
        return $this->belongsTo(Period::class);
    }

    public function shiftCompliances()
    {
        return $this->hasMany(ShiftCompliance::class);
    }

    public function scopeNo($q, $n){ return $q->where('week_no', $n); }
}
