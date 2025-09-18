<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manager extends Model
{
    protected $table = 'managers';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function complianceScores()
    {
        return $this->hasMany(ComplianceScore::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
