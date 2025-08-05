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
        Schema::table('orders', function (Blueprint $table) {
            // Informations de contact
            $table->string('phone_number')->nullable()->after('prescription_number');
            
            // Image d'ordonnance
            $table->string('prescription_image')->nullable()->after('phone_number');
            
            // Moyen de paiement
            $table->enum('payment_method', ['tmoney', 'flooz', 'carte_bancaire'])->nullable()->after('total_amount');
            
            // Prix réduit de moitié
            $table->decimal('original_price', 10, 2)->nullable()->after('payment_method');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('original_price');
            
            // Statut de paiement
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'failed', 'refunded'])->default('pending')->after('discount_amount');
            
            // Modifier le prix total pour refléter le prix réduit
            $table->decimal('total_amount', 10, 2)->comment('Prix réduit de 50%')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'phone_number',
                'prescription_image',
                'payment_method',
                'original_price',
                'discount_amount',
                'payment_status'
            ]);
        });
    }
};
