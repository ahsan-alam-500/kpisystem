<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = 'tasks';

    protected $fillable = [
        'title',
        'description',
        'completed',
        'assigned_to',
        'due_date',
    ];

    public function manager()
    {
        return $this->belongsTo(Manager::class, 'assigned_to');
    }

}
