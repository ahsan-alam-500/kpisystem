<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShiftComplianceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_compliance_id','compliance_task_id','done','note'
    ];

    protected $casts = ['done' => 'boolean'];

    public function shift()
    {
        return $this->belongsTo(ShiftCompliance::class, 'shift_compliance_id');
    }

    public function task()
    {
        return $this->belongsTo(ComplianceTask::class, 'compliance_task_id');
    }
}
