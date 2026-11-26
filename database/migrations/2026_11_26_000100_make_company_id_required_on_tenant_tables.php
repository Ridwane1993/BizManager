<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables dont chaque enregistrement doit appartenir à une entreprise.
     */
    private array $tenantTables = [
        'users',
        'categories',
        'products',
        'clients',
        'sales',
        'sale_items',
        'expenses',
        'suppliers',
        'purchases',
        'purchase_items',
        'stock_movements',
    ];

    /**
     * Rendre company_id obligatoire après vérification des données existantes.
     */
    public function up(): void
    {
        $unassigned = [];

        foreach ($this->tenantTables as $tableName) {
            $count = DB::table($tableName)
                ->whereNull('company_id')
                ->count();

            if ($count > 0) {
                $unassigned[$tableName] = $count;
            }
        }

        if ($unassigned !== []) {
            $details = [];

            foreach ($unassigned as $tableName => $count) {
                $details[] = sprintf('%s (%d)', $tableName, $count);
            }

            throw new RuntimeException(
                'Impossible de rendre company_id obligatoire : des enregistrements '
                .'sont encore sans entreprise : '.implode(', ', $details).'. '
                .'Rattachez-les explicitement avant de relancer la migration.'
            );
        }

        foreach ($this->tenantTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropForeign(['company_id']);
                $table->unsignedBigInteger('company_id')
                    ->nullable(false)
                    ->change();
                $table->foreign('company_id')
                    ->references('id')
                    ->on('companies')
                    ->restrictOnDelete();
            });
        }
    }

    /**
     * Restaurer des colonnes nullable (les données restent rattachées).
     */
    public function down(): void
    {
        foreach (array_reverse($this->tenantTables) as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropForeign(['company_id']);
                $table->unsignedBigInteger('company_id')
                    ->nullable()
                    ->change();
                $table->foreign('company_id')
                    ->references('id')
                    ->on('companies')
                    ->restrictOnDelete();
            });
        }
    }
};
