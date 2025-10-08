<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ComplianceTask extends Model
{
    use HasFactory;

    protected $fillable = ['name','is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function items()
    {
        return $this->hasMany(ShiftComplianceItem::class);
    }

    public function scopeActive($q){ return $q->where('is_active', true); }
}
