<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Modifier la contrainte de category_id.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {

            // Supprimer l'ancienne contrainte CASCADE
            $table->dropForeign(['category_id']);

            // Créer une nouvelle contrainte RESTRICT
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->restrictOnDelete();
        });
    }

    /**
     * Annuler la modification.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {

            // Supprimer la contrainte RESTRICT
            $table->dropForeign(['category_id']);

            // Restaurer la contrainte CASCADE
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->cascadeOnDelete();
        });
    }
};
