<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->unique(
                ['company_id', 'name'],
                'categories_company_name_unique'
            );
        });
    }

    public function down(): void
    {
        $hasDuplicateNames = DB::table('categories')
            ->select('name')
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicateNames) {
            throw new RuntimeException(
                'Cannot restore global category name uniqueness while duplicate names exist.'
            );
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_company_name_unique');
        });
    }
};
