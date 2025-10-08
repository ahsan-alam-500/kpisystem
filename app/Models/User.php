<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',     // optional
        'pub_id',    // ✅ link to pubs.id
    ];

    protected $hidden = ['password', 'remember_token'];

    // Laravel 10/11 style casts method (keep only this; remove $casts property)
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed', // auto-hash on set
        ];
    }

    /* ========== JWT ========== */
    public function getJWTIdentifier()
    {
        return $this->getKey(); // user id
    }

    public function getJWTCustomClaims(): array
    {
        // keep token slim; UI can fetch roles via /me
        return [];
    }

    /* ========== RELATIONSHIPS ========== */
    public function pub()                     // ✅ needed for ->load('pub')
    {
        return $this->belongsTo(Pub::class);  // users.pub_id -> pubs.id
    }

    public function managedPubs()
    {
        return $this->hasMany(Pub::class, 'manager_id');
    }

    public function shiftCompliances()
    {
        return $this->hasMany(ShiftCompliance::class);
    }

    /* ========== SCOPES ========== */
    public function scopeManagers($q){ return $q->role('Manager'); }
    public function scopeEmployees($q){ return $q->role('Employee'); }
}
