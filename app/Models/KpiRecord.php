<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KpiRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'pub_id','period_id','kpi_category_id','value','meta'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'meta'  => 'array',
    ];

    public function pub(){ return $this->belongsTo(Pub::class); }
    public function period(){ return $this->belongsTo(Period::class); }
    public function category(){ return $this->belongsTo(KpiCategory::class,'kpi_category_id'); }

    // Helpers
    public function getDisplayNameAttribute()
    {
        return ($this->category->name ?? 'KPI')." — P{$this->period_id}";
    }
}
