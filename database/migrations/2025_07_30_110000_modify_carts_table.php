<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('carts', function (Blueprint $table) {
            // Vérifier si les colonnes existent avant de les ajouter
            if (!Schema::hasColumn('carts', 'blood_type')) {
                $table->string('blood_type')->after('center_id');
            }
            if (!Schema::hasColumn('carts', 'quantity')) {
                $table->integer('quantity')->after('blood_type');
            }

            // Ajout des contraintes de clé étrangère si elles n'existent pas
            if (!Schema::hasColumn('carts', 'user_id')) {
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('carts', 'center_id')) {
                $table->foreignId('center_id')->constrained();
            }
        });
    }

    public function down()
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn(['blood_type', 'quantity']);
            $table->dropForeign(['user_id', 'center_id']);
        });
    }
};
