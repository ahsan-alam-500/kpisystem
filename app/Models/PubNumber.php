<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PubNumber extends Model
{
    protected $table = 'pub_numbers';

    protected $fillable = [
        'pub_number',
        'name',
        'description',
    ];

    public function kpis()
    {
        return $this->hasMany(Kpi::class);
    }

    public function complianceScores()
    {
        return $this->hasMany(ComplianceScore::class);
    }
}
