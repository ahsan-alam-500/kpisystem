<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShiftCompliance extends Model
{
    use HasFactory;

    protected $fillable = ['pub_id','week_id','user_id','score','summary'];

    protected $casts = [
        'score'   => 'integer',
        'summary' => 'array',
    ];

    public function pub(){ return $this->belongsTo(Pub::class); }
    public function week(){ return $this->belongsTo(Week::class); }
    public function user(){ return $this->belongsTo(User::class); }

    public function items()
    {
        return $this->hasMany(ShiftComplianceItem::class);
    }

    // computed: completion %
    public function computeScore(): int
    {
        $total = $this->items()->count();
        if (!$total) return 0;
        $done = $this->items()->where('done', true)->count();
        return (int) round($done * 100 / $total);
    }
}
