<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'center_id',
        'prescription_number',
        'phone_number',
        'prescription_image',
        'blood_type',
        'quantity',
        'unit_price',
        'total_amount',
        'original_price',
        'discount_amount',
        'payment_method',
        'payment_status',
        'status',
        'notes',
        'order_date',
        'delivery_date'
    ];    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'original_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'order_date' => 'datetime',
        'delivery_date' => 'datetime',
        'prescription_image' => 'json', // Cast JSON pour les images multiples
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function center()
    {
        return $this->belongsTo(Center::class);
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

    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
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
    public function getFormattedTotalAttribute()
    {
        return number_format($this->total_amount, 0, ',', ' ') . ' F CFA';
    }

    public function getFormattedOriginalPriceAttribute()
    {
        return number_format($this->original_price ?? 0, 0, ',', ' ') . ' F CFA';
    }

    public function getFormattedDiscountAttribute()
    {
        return number_format($this->discount_amount ?? 0, 0, ',', ' ') . ' F CFA';
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'En attente',
            'confirmed' => 'Confirmée',
            'ready' => 'Prête',
            'completed' => 'Terminée',
            'cancelled' => 'Annulée'
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getPaymentStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'En attente',
            'partial' => 'Acompte payé',
            'paid' => 'Payé intégralement',
            'failed' => 'Échec',
            'refunded' => 'Remboursé'
        ];

        return $labels[$this->payment_status] ?? $this->payment_status;
    }

    public function getPaymentMethodLabelAttribute()
    {
        $labels = [
            'tmoney' => 'T-Money',
            'flooz' => 'Flooz',
            'carte_bancaire' => 'Carte Bancaire'
        ];

        return $labels[$this->payment_method] ?? 'Non spécifié';
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'orange',
            'confirmed' => 'blue',
            'ready' => 'green',
            'completed' => 'gray',
            'cancelled' => 'red'
        ];

        return $colors[$this->status] ?? 'gray';
    }

    public function getPrescriptionImagesArrayAttribute()
    {
        // Si c'est déjà un tableau (JSON), retourner tel quel
        if (is_array($this->prescription_image)) {
            return $this->prescription_image;
        }
        
        // Si c'est une chaîne JSON, décoder
        if (is_string($this->prescription_image) && $this->prescription_image) {
            $decoded = json_decode($this->prescription_image, true);
            return is_array($decoded) ? $decoded : [$this->prescription_image];
        }
        
        // Retourner un tableau vide si pas d'image
        return [];
    }

    public function getPrescriptionImagesUrlsAttribute()
    {
        $images = $this->getPrescriptionImagesArrayAttribute();
        return array_map(function($imagePath) {
            return $imagePath ? asset('storage/' . $imagePath) : null;
        }, $images);
    }

    public function getFirstPrescriptionImageUrlAttribute()
    {
        $images = $this->getPrescriptionImagesArrayAttribute();
        $firstImage = $images[0] ?? null;
        return $firstImage ? asset('storage/' . $firstImage) : null;
    }

    // Mutateurs
    public function setTotalAmountAttribute($value)
    {
        $this->attributes['total_amount'] = $this->quantity * $this->unit_price;
    }
}
