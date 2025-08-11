<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BloodBag extends Model
{
    use HasFactory;

    protected $fillable = [
        'blood_type_id', 'center_id', 'donor_id', 'volume',
        'collected_at', 'expires_at', 'status'
    ];

    protected $casts = [
        'collected_at' => 'datetime',
        'expires_at' => 'date',
        'volume' => 'decimal:2',
    ];

    public function bloodType()
    {
        return $this->belongsTo(BloodType::class);
    }

    public function center()
    {
        return $this->belongsTo(Center::class);
    }

    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }

    public function transfusion()
    {
        return $this->hasOne(Transfusion::class);
    }

    public function donationHistory()
    {
        return $this->hasOne(DonationHistory::class);
    }

    public function reservationBloodBags()
    {
        return $this->hasMany(ReservationBloodBag::class);
    }

    public function reservations()
    {
        return $this->belongsToMany(ReservationRequest::class, 'reservation_blood_bags', 'blood_bag_id', 'reservation_id');
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeReserved($query)
    {
        return $query->where('status', 'reserved');
    }

    public function scopeTransfused($query)
    {
        return $query->where('status', 'transfused');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->where('status', 'available')
                    ->where('expires_at', '<=', Carbon::now()->addDays($days))
                    ->where('expires_at', '>', Carbon::now());
    }

    public function scopeByBloodType($query, $bloodTypeId)
    {
        return $query->where('blood_type_id', $bloodTypeId);
    }

    public function scopeByCenter($query, $centerId)
    {
        return $query->where('center_id', $centerId);
    }

    // Accessors
    public function getIsExpiredAttribute()
    {
        return $this->expires_at->isPast();
    }

    public function getDaysUntilExpirationAttribute()
    {
        return Carbon::now()->diffInDays($this->expires_at, false);
    }

    public function getIsExpiringSoonAttribute()
    {
        return $this->days_until_expiration <= 7 && $this->days_until_expiration > 0;
    }
}