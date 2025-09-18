<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kpi extends Model
{
    protected $table = 'kpis';

    protected $fillable = [
        'pub_id',
        'name',
        'value',
        'week_start',
        'description',
    ];

    public function pub()
    {
        return $this->belongsTo(PubNumber::class);
    }
}
