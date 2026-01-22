<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\HcisEmployee; 

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id', 
        'img_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function employee(): HasOne
    {
        return $this->hasOne(HcisEmployee::class, 'employee_id', 'employee_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }

    public function isManager()
    {
        return HcisEmployee::where('manager_l1_id', $this->employee_id)
            ->orWhere('manager_l2_id', $this->employee_id)
            ->exists();
    }

    public function can($abilities, $arguments = []): bool
    {
        $roles = $this->roles()->with('permissions')->get();

        foreach ($roles as $role) {
            if ($role->permissions->contains('name', $abilities)) {
                return true;
            }
        }

        return false;
    }
}