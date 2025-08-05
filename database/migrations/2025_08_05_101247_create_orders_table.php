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
            $table->string('prescription_number'); // Numéro d'ordonnance
            $table->string('blood_type');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2)->default(5000.00); // Prix par poche
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['pending', 'confirmed', 'ready', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('order_date')->useCurrent();
            $table->timestamp('delivery_date')->nullable();
            $table->timestamps();
            
            // Index pour optimiser les recherches
            $table->index(['user_id', 'status']);
            $table->index(['center_id', 'status']);
            $table->index('prescription_number');
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
