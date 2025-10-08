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
        'role'
    ];

    protected $hidden = ['password','remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // Laravel 10/11 auto-hash
        ];
    }

    /**
     * JWTSubject implementations
     */
    public function getJWTIdentifier()
    {
        return $this->getKey(); // user id
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role
        ];
    }

    public function managedPubs()
    {
        return $this->hasMany(Pub::class, 'manager_id');
    }

    public function shiftCompliances()
    {
        return $this->hasMany(ShiftCompliance::class);
    }

    // Scopes
    public function scopeManagers($q){ return $q->role('Manager'); }
    public function scopeEmployees($q){ return $q->role('Employee'); }
}
