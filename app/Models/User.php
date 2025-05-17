<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
        'role_id', 'designation', 'wing',
        'supervisor_id', 'ip_address', 'latitude', 'longitude', 'device_token'
    ];

    protected $hidden = ['password'];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function filesCreated()
    {
        return $this->hasMany(File::class, 'created_by');
    }

    public function sentMovements()
    {
        return $this->hasMany(FileMovement::class, 'sender_id');
    }

    public function receivedMovements()
    {
        return $this->hasMany(FileMovement::class, 'receiver_id');
    }
    
    public function wing()
    {
        return $this->belongsTo(Wing::class);
    }
}
