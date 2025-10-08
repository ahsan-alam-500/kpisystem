<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KpiCategory extends Model
{
    use HasFactory;

    protected $fillable = ['code','name'];

    public function records()
    {
        return $this->hasMany(KpiRecord::class);
    }
}
