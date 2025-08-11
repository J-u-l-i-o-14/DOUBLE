<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'center_id',
        'phone',
        'address',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relations
    public function center()
    {
        return $this->belongsTo(Center::class);
    }

    public function donor()
    {
        return $this->hasOne(Donor::class);
    }

    public function reservationRequests()
    {
        return $this->hasMany(ReservationRequest::class);
    }

    public function reservationAudits()
    {
        return $this->hasMany(ReservationAudit::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'verified_by');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Relation avec les donations via le modèle Donor
    public function donations()
    {
        return $this->hasManyThrough(DonationHistory::class, Donor::class, 'user_id', 'donor_id');
    }

    // Scopes
    public function scopeDonors($query)
    {
        return $query->where('role', 'donor');
    }

    public function scopePatients($query)
    {
        return $query->where('role', 'patient');
    }

    public function scopeManagers($query)
    {
        return $query->where('role', 'manager');
    }

    public function scopeClients($query)
    {
        return $query->where('role', 'client');
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    // Accessors & Mutators
    public function getIsDonorAttribute()
    {
        return $this->role === 'donor';
    }

    public function getIsPatientAttribute()
    {
        return $this->role === 'patient';
    }

    public function getIsManagerAttribute()
    {
        return $this->role === 'manager';
    }

    public function getIsClientAttribute()
    {
        return $this->role === 'client';
    }

    public function getIsAdminAttribute()
    {
        return $this->role === 'admin';
    }

    public function getHasDashboardAttribute()
    {
        return in_array($this->role, ['admin', 'manager', 'client']);
    }

    public function getCanManageCenterAttribute()
    {
        return in_array($this->role, ['admin', 'manager']);
    }
}
