<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables dont les données doivent appartenir à une entreprise.
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
     * Ajouter company_id et rattacher les données existantes.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Créer ou retrouver l'entreprise principale
        |--------------------------------------------------------------------------
        */

        $company = DB::table('companies')
            ->where('slug', 'entreprise-principale')
            ->first();

        if ($company) {
            $companyId = $company->id;
        } else {
            $companyId = DB::table('companies')->insertGetId([
                'name' => 'Entreprise principale',
                'slug' => 'entreprise-principale',
                'email' => null,
                'phone' => null,
                'address' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Ajouter company_id à chaque table
        |--------------------------------------------------------------------------
        |
        | Le champ est temporairement nullable pour permettre le
        | rattachement des anciennes données avant de rendre
        | l'entreprise obligatoire.
        |
        */

        foreach ($this->tenantTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('company_id')
                    ->nullable()
                    ->constrained('companies')
                    ->restrictOnDelete();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Rattacher toutes les données existantes
        |--------------------------------------------------------------------------
        */

        foreach ($this->tenantTables as $tableName) {
            DB::table($tableName)
                ->whereNull('company_id')
                ->update([
                    'company_id' => $companyId,
                ]);
        }
    }

    /**
     * Retirer les colonnes company_id en cas de rollback.
     *
     * La ligne Entreprise principale est conservée afin d'éviter
     * de supprimer des données d'entreprise potentiellement utilisées.
     */
    public function down(): void
    {
        foreach (array_reverse($this->tenantTables) as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            });
        }
    }
};
