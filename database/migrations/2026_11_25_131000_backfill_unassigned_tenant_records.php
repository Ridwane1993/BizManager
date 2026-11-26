<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
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

    public function up(): void
    {
        $unassignedCounts = [];

        foreach ($this->tenantTables as $tableName) {
            $count = DB::table($tableName)
                ->whereNull('company_id')
                ->count();

            if ($count > 0) {
                $unassignedCounts[$tableName] = $count;
            }
        }

        if ($unassignedCounts === []) {
            return;
        }

        $companies = DB::table('companies')->orderBy('id')->get(['id']);

        if ($companies->count() !== 1) {
            throw new RuntimeException(
                'Des données sans entreprise ont été trouvées alors que plusieurs entreprises existent. '
                .'Rattachez explicitement ces données avant de poursuivre les migrations.'
            );
        }

        $companyId = $companies->first()->id;

        DB::transaction(function () use ($unassignedCounts, $companyId): void {
            foreach (array_keys($unassignedCounts) as $tableName) {
                DB::table($tableName)
                    ->whereNull('company_id')
                    ->update(['company_id' => $companyId]);
            }
        });
    }

    public function down(): void
    {
        // Existing records keep their company assignment when this migration is rolled back.
    }
};
