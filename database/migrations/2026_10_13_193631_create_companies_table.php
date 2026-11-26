<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Créer la table des entreprises.
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            // Informations principales
            $table->string('name');
            $table->string('slug')->unique();

            // Coordonnées facultatives
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->text('address')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Supprimer la table en cas de rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
