<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes, HasFactory;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'avatar', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────
    public function customer() { return $this->hasOne(Customer::class); }
    public function staffProfile() { return $this->hasOne(Staff::class); }

    // ── Role helpers ───────────────────────────────────────────
    public function isSuperAdmin(): bool { return $this->role === 'super_admin'; }
    public function isAdmin(): bool      { return in_array($this->role, ['super_admin', 'admin']); }
    public function isStaff(): bool      { return $this->role === 'staff'; }
    public function isCustomer(): bool   { return $this->role === 'customer'; }
    public function hasRole(string $role): bool { return $this->role === $role; }

    public function getDashboardRoute(): string
    {
        return match ($this->role) {
            'super_admin', 'admin' => '/admin/dashboard',
            'staff'                => '/staff/dashboard',
            'customer'             => '/customer/dashboard',
            default                => '/',
        };
    }
}
