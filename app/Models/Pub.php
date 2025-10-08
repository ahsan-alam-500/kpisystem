<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pub extends Model
{
    use HasFactory;

    protected $fillable = ['pub_number','name','manager_id'];

    // Relations
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function kpis()
    {
        return $this->hasMany(KpiRecord::class);
    }

    public function shifts()
    {
        return $this->hasMany(ShiftCompliance::class);
    }

    // Scopes
    public function scopeNumber($q, $pubNo)
    {
        return $q->where('pub_number', $pubNo);
    }
}
