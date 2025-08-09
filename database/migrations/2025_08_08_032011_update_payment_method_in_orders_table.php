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
            // Modifier la colonne payment_method pour inclure plus de valeurs
            $table->enum('payment_method', ['tmoney', 'flooz', 'carte_bancaire', 'mobile_money', 'especes', 'cheque', 'virement'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Revenir à l'ancienne définition
            $table->enum('payment_method', ['tmoney', 'flooz', 'carte_bancaire'])->nullable()->change();
        });
    }
};
