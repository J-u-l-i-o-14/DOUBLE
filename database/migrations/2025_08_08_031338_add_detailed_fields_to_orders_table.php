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
            $table->string('doctor_name')->nullable()->after('prescription_number');
            $table->string('patient_id_image')->nullable()->after('prescription_images');
            $table->string('medical_certificate')->nullable()->after('patient_id_image');
            $table->string('payment_reference')->nullable()->after('payment_method');
            $table->string('transaction_id')->nullable()->after('payment_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'doctor_name',
                'patient_id_image', 
                'medical_certificate',
                'payment_reference',
                'transaction_id'
            ]);
        });
    }
};
