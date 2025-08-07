<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('center_id')->constrained()->onDelete('cascade');
            
            // Informations de base
            $table->string('prescription_number'); // Numéro d'ordonnance
            $table->string('phone_number');
            $table->json('prescription_image')->nullable(); // Images d'ordonnance (JSON)
            $table->json('prescription_images')->nullable(); // Compatibilité multiple images
            $table->text('notes')->nullable();
            
            // Informations produit
            $table->string('blood_type');
            $table->unsignedBigInteger('blood_type_id')->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2)->default(5000.00); // Prix par poche
            
            // Informations financières
            $table->decimal('total_amount', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable();
            $table->decimal('discount_amount', 10, 2)->nullable();
            $table->decimal('deposit_amount', 10, 2)->nullable(); // Acompte
            $table->decimal('remaining_amount', 10, 2)->nullable(); // Solde restant
            
            // Paiement
            $table->enum('payment_method', ['tmoney', 'flooz', 'carte_bancaire'])->nullable();
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'failed', 'refunded'])->default('pending');
            
            // Statuts
            $table->enum('status', ['pending', 'confirmed', 'processing', 'ready', 'completed', 'cancelled', 'expired'])->default('pending');
            
            // Sprint 4 - Gestion des documents
            $table->enum('document_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('validated_by')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->text('validation_notes')->nullable();
            
            // Dates
            $table->timestamp('order_date')->useCurrent();
            $table->timestamp('delivery_date')->nullable();
            $table->timestamps();
            
            // Clés étrangères
            $table->foreign('validated_by')->references('id')->on('users')->onDelete('set null');
            
            // Index pour optimiser les recherches
            $table->index(['user_id', 'status']);
            $table->index(['center_id', 'status']);
            $table->index('prescription_number');
            $table->index('document_status');
            $table->index(['status', 'document_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
