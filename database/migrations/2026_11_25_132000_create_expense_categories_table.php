<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                ->constrained('companies')
                ->restrictOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->unique(['company_id', 'name']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('expense_category_id')
                ->nullable()
                ->after('category')
                ->constrained('expense_categories')
                ->nullOnDelete();
        });

        $legacyCategories = DB::table('expenses')
            ->whereNotNull('category')
            ->select('company_id', 'category')
            ->distinct()
            ->get();

        foreach ($legacyCategories as $legacyCategory) {
            $categoryId = DB::table('expense_categories')
                ->where('company_id', $legacyCategory->company_id)
                ->where('name', $legacyCategory->category)
                ->value('id');

            if ($categoryId === null) {
                $categoryId = DB::table('expense_categories')->insertGetId([
                    'company_id' => $legacyCategory->company_id,
                    'name' => $legacyCategory->category,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('expenses')
                ->where('company_id', $legacyCategory->company_id)
                ->where('category', $legacyCategory->category)
                ->update(['expense_category_id' => $categoryId]);
        }
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['expense_category_id']);
            $table->dropColumn('expense_category_id');
        });

        Schema::dropIfExists('expense_categories');
    }
};
