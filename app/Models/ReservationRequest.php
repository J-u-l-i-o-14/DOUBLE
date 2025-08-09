<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ReservationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'user_id', 'center_id', 'status', 'total_amount', 'paid_amount',
        'document_path', 'expires_at', 'manager_notes', 'updated_by'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function center()
    {
        return $this->belongsTo(Center::class);
    }

    public function items()
    {
        return $this->hasMany(ReservationItem::class, 'request_id');
    }

    public function bloodBags()
    {
        return $this->hasMany(ReservationBloodBag::class, 'reservation_id');
    }

    public function audits()
    {
        return $this->hasMany(ReservationAudit::class, 'reservation_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'reservation_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'reservation_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByCenter($query, $centerId)
    {
        return $query->where('center_id', $centerId);
    }

    // Accessors
    public function getRemainingAmountAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function getIsFullyPaidAttribute()
    {
        return $this->paid_amount >= $this->total_amount;
    }

    public function getIsExpiredAttribute()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getIsExpiringSoonAttribute()
    {
        return $this->expires_at && $this->expires_at->diffInHours(now()) <= 24;
    }

    public function getPaymentPercentageAttribute()
    {
        if ($this->total_amount == 0) return 100;
        return ($this->paid_amount / $this->total_amount) * 100;
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'En attente',
            'confirmed' => 'Confirmée',
            'cancelled' => 'Annulée',
            'completed' => 'Terminée',
            'expired' => 'Expirée'
        ];

        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'info',
            'confirmed' => 'success',
            'cancelled' => 'danger',
            'completed' => 'primary',
            'expired' => 'secondary'
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    public function canBeUpdated()
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }
} 