<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table    = 'users';
    protected $fillable = ['nom', 'email', 'password', 'telephone', 'role', 'actif'];
    protected $hidden   = ['password', 'remember_token'];
    protected $casts    = [
        'actif'      => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function commandes()
    {
        return $this->hasMany(Commande::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
