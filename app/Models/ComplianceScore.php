<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplianceScore extends Model
{
    protected $table = 'compliance_scores';

    protected $fillable = [
        'manager_id',
        'pub_id',
        'score',
        'week_start',
    ];

    public function manager()
    {
        return $this->belongsTo(Manager::class);
    }

    public function pub()
    {
        return $this->belongsTo(PubNumber::class);
    }

}
