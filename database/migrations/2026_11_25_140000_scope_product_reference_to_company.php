<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_reference_unique');
            $table->unique(
                ['company_id', 'reference'],
                'products_company_reference_unique'
            );
        });
    }

    public function down(): void
    {
        $hasDuplicateReferences = DB::table('products')
            ->select('reference')
            ->groupBy('reference')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicateReferences) {
            throw new RuntimeException(
                'Cannot restore global product reference uniqueness while duplicate references exist.'
            );
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_company_reference_unique');
            $table->unique('reference', 'products_reference_unique');
        });
    }
};
