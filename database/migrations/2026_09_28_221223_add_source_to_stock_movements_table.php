<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajouter la source du mouvement.
     */
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('source_type')
                ->nullable()
                ->after('description');

            $table->unsignedBigInteger('source_id')
                ->nullable()
                ->after('source_type');

            $table->index([
                'source_type',
                'source_id',
            ]);
        });
    }

    /**
     * Supprimer la source du mouvement.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex([
                'stock_movements_source_type_source_id_index',
            ]);

            $table->dropColumn([
                'source_type',
                'source_id',
            ]);
        });
    }
};
