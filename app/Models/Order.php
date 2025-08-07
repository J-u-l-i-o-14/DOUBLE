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
        'blood_type',
        'blood_type_id',
        'quantity',
        'prescription_number',
        'phone_number',
        'prescription_image',
        'prescription_images',
        'notes',
        'payment_method',
        'total_amount',
        'deposit_amount',
        'remaining_amount',
        'payment_status',
        'status',
        'unit_price',
        'original_price',
        'discount_amount',
        'order_date',
        // Nouveaux champs Sprint 4
        'document_status',
        'validated_by',
        'validated_at',
        'validation_notes'
    ];    protected $casts = [
        'prescription_images' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'order_date' => 'datetime',
        'validated_at' => 'datetime',
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

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // Scopes pour les nouveaux statuts
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDocumentPending($query)
    {
        return $query->where('document_status', 'pending');
    }

    public function scopeDocumentApproved($query)
    {
        return $query->where('document_status', 'approved');
    }

    public function scopeByPrescriptionNumber($query, $prescriptionNumber)
    {
        return $query->where('prescription_number', $prescriptionNumber);
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
            'processing' => 'En traitement',
            'ready' => 'Prête',
            'completed' => 'Terminée',
            'cancelled' => 'Annulée',
            'expired' => 'Expirée'
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getDocumentStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'En attente de validation',
            'approved' => 'Documents approuvés',
            'rejected' => 'Documents rejetés'
        ];

        return $labels[$this->document_status] ?? $this->document_status;
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

    // Méthodes statiques pour la gestion des ordonnances multiples
    public static function checkPrescriptionStatus($prescriptionNumber)
    {
        $orders = self::where('prescription_number', $prescriptionNumber)->get();
        
        if ($orders->isEmpty()) {
            return ['status' => 'new', 'message' => 'Nouvelle ordonnance'];
        }
        
        // Vérifier s'il y a des commandes en cours (non terminées)
        $pendingOrders = $orders->whereIn('status', ['pending', 'confirmed', 'processing']);
        
        if ($pendingOrders->isNotEmpty()) {
            return [
                'status' => 'in_progress', 
                'message' => 'Ordonnance en cours, nouvelles commandes autorisées',
                'existing_orders' => $pendingOrders->count()
            ];
        }
        
        // Toutes les commandes sont terminées
        return [
            'status' => 'completed', 
            'message' => 'Ordonnance terminée, nouvelle ordonnance requise',
            'completed_orders' => $orders->count()
        ];
    }

    public static function canAddNewOrder($prescriptionNumber)
    {
        $status = self::checkPrescriptionStatus($prescriptionNumber);
        return in_array($status['status'], ['new', 'in_progress']);
    }

    // Méthodes d'instance pour les statuts
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isConfirmed()
    {
        return $this->status === 'confirmed';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isDocumentPending()
    {
        return $this->document_status === 'pending';
    }

    public function isDocumentApproved()
    {
        return $this->document_status === 'approved';
    }

    public function markAsConfirmed($validatedBy = null, $notes = null)
    {
        $this->update([
            'status' => 'confirmed',
            'document_status' => 'approved',
            'validated_by' => $validatedBy,
            'validated_at' => now(),
            'validation_notes' => $notes
        ]);
    }

    public function markAsRejected($validatedBy = null, $notes = null)
    {
        $this->update([
            'status' => 'cancelled',
            'document_status' => 'rejected',
            'validated_by' => $validatedBy,
            'validated_at' => now(),
            'validation_notes' => $notes
        ]);
    }

    public function markAsCompleted()
    {
        $this->update(['status' => 'completed']);
    }
}
