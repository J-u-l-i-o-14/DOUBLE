<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Table regions
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // 2. Table centers
        Schema::create('centers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('address');
            $table->foreignId('region_id')->constrained()->onDelete('cascade');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
        });

        // 3. Table blood_types
        Schema::create('blood_types', function (Blueprint $table) {
            $table->id();
            $table->string('group')->unique();
            $table->timestamps();
        });

        // 4. Table users (mise à jour)
        Schema::table('users', function (Blueprint $table) {
            // Supprimer les anciennes colonnes
            $table->dropColumn(['blood_type', 'birth_date', 'gender', 'last_donation_date']);
            
            // Ajouter les nouvelles colonnes
            $table->foreignId('center_id')->nullable()->constrained()->onDelete('set null');
        });

        // 5. Table donors
        Schema::create('donors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('center_id')->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birthdate')->nullable();
            $table->enum('gender', ['male', 'female', 'other']);
            $table->foreignId('blood_type_id')->constrained();
            $table->date('last_donation_date')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable()->unique();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        // 6. Table blood_bags (mise à jour)
        Schema::dropIfExists('blood_bags');
        Schema::create('blood_bags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_type_id')->constrained();
            $table->foreignId('center_id')->constrained();
            $table->foreignId('donor_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('volume', 8, 2); // en ml
            $table->dateTime('collected_at');
            $table->date('expires_at');
            $table->enum('status', ['available', 'reserved', 'transfused', 'expired', 'discarded'])->default('available');
            $table->timestamps();
        });

        // 7. Table stock_thresholds
        Schema::create('stock_thresholds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('center_id')->constrained()->onDelete('cascade');
            $table->foreignId('blood_type_id')->constrained()->onDelete('cascade');
            $table->integer('warning_threshold')->default(5);
            $table->integer('critical_threshold')->default(3);
            $table->unique(['center_id', 'blood_type_id']);
            $table->timestamps();
        });

        // 7bis. Table center_blood_type_inventory
        Schema::create('center_blood_type_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('center_id')->constrained();
            $table->foreignId('blood_type_id')->constrained();
            $table->integer('available_quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->unique(['center_id', 'blood_type_id']);
            $table->timestamps();
        });

        // 8. Table patients (mise à jour)
        Schema::dropIfExists('patients');
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('center_id')->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birthdate')->nullable();
            $table->enum('gender', ['male', 'female', 'other']);
            $table->foreignId('blood_type_id')->nullable()->constrained();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        // 9. Table reservation_requests
        Schema::create('reservation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('center_id')->constrained();
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed', 'expired'])->default('pending');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->text('document_path')->nullable(); // Pour stocker un JSON de plusieurs fichiers
            $table->dateTime('expires_at')->nullable();
            $table->string('client_name')->nullable();
            $table->string('client_email')->nullable();
            $table->string('client_phone', 30)->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->timestamps();
        });

        // 10. Table reservation_items
        Schema::create('reservation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('reservation_requests')->onDelete('cascade');
            $table->foreignId('blood_type_id')->constrained();
            $table->integer('quantity');
            $table->timestamps();
        });

        // 11. Table reservation_blood_bags
        Schema::create('reservation_blood_bags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('reservation_requests')->onDelete('cascade');
            $table->foreignId('blood_bag_id')->constrained();
            $table->timestamps();
        });

        // 12. Table reservation_audits
        Schema::create('reservation_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('reservation_requests');
            $table->foreignId('user_id')->constrained();
            $table->enum('action', ['created', 'confirmed', 'cancelled', 'completed', 'payment']);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 13. Table payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('reservation_requests');
            $table->decimal('amount', 10, 2);
            $table->string('method');
            $table->string('transaction_id')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
        });

        // 14. Table documents
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('reservation_requests');
            $table->string('path');
            $table->boolean('verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();
        });

        // 15. Table appointments (mise à jour)
        Schema::dropIfExists('appointments');
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donor_id')->constrained();
            $table->foreignId('center_id')->constrained();
            $table->dateTime('scheduled_at');
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 16. Table center_schedules
        Schema::create('center_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('center_id')->constrained();
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('max_donors')->default(10);
            $table->json('equipements')->nullable();
            $table->timestamps();
        });

        // 17. Table campaigns (mise à jour)
        Schema::dropIfExists('campaigns');
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('center_id')->constrained();
            $table->foreignId('organizer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('location');
            $table->dateTime('date');
            $table->dateTime('end_date')->nullable();
            $table->timestamps();
        });

        // 18. Table donation_histories
        Schema::create('donation_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donor_id')->constrained();
            $table->foreignId('campaign_id')->nullable()->constrained();
            $table->foreignId('blood_bag_id')->unique()->constrained();
            $table->dateTime('donated_at');
            $table->decimal('volume', 8, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 19. Table transfusions (mise à jour)
        Schema::dropIfExists('transfusions');
        Schema::create('transfusions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_bag_id')->constrained();
            $table->foreignId('patient_id')->constrained();
            $table->dateTime('transfusion_date');
            $table->decimal('volume_used', 8, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 20. Table notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('type');
            $table->text('message');
            $table->boolean('read')->default(false);
            $table->timestamps();
        });

        // 21. Table alerts
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('center_id')->constrained();
            $table->foreignId('blood_type_id')->nullable()->constrained();
            $table->enum('type', ['expiration', 'low_stock', 'critical_stock']);
            $table->text('message');
            $table->boolean('resolved')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('center_blood_type_inventory');
        Schema::dropIfExists('alerts');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('transfusions');
        Schema::dropIfExists('donation_histories');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('center_schedules');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('reservation_audits');
        Schema::dropIfExists('reservation_blood_bags');
        Schema::dropIfExists('reservation_items');
        Schema::dropIfExists('reservation_requests');
        Schema::dropIfExists('patients');
        Schema::dropIfExists('stock_thresholds');
        Schema::dropIfExists('blood_bags');
        Schema::dropIfExists('donors');
        Schema::dropIfExists('centers');
        Schema::dropIfExists('blood_types');
        Schema::dropIfExists('regions');
    }
}; 