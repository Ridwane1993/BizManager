<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
     * Afficher la liste des catégories.
     */
    public function index()
    {
        $categories = Category::latest()->get();

        return view(
            'categories.index',
            compact('categories')
        );
    }

    /**
     * Formulaire de création.
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Enregistrer une nouvelle catégorie.
     */
    public function store(StoreCategoryRequest $request)
    {
        $validated = $request->validated();

        Category::create($validated);

        return redirect()
            ->route('categories.index')
            ->with(
                'success',
                'Catégorie créée avec succès.'
            );
    }

    /**
     * Cette méthode n'est actuellement pas utilisée
     * car la route resource utilise ->except(['show']).
     */
    public function show(Category $category)
    {
        abort(404);
    }

    /**
     * Formulaire de modification.
     */
    public function edit(Category $category)
    {
        return view(
            'categories.edit',
            compact('category')
        );
    }

    /**
     * Modifier une catégorie.
     */
    public function update(
        UpdateCategoryRequest $request,
        Category $category
    ) {
        $validated = $request->validated();

        $category->update($validated);

        return redirect()
            ->route('categories.index')
            ->with(
                'success',
                'Catégorie modifiée avec succès.'
            );
    }

    /**
     * Supprimer une catégorie.
     */
    public function destroy(Category $category)
    {
        return DB::transaction(function () use ($category) {

            /*
            |--------------------------------------------------------------------------
            | Verrouiller la catégorie
            |--------------------------------------------------------------------------
            */
            $category = Category::whereKey($category->id)
                ->lockForUpdate()
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | Vérifier si la catégorie contient encore des produits
            |--------------------------------------------------------------------------
            */
            if ($category->products()->exists()) {
                return redirect()
                    ->route('categories.index')
                    ->with(
                        'error',
                        'Impossible de supprimer cette catégorie car elle contient encore des produits.'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | Supprimer la catégorie
            |--------------------------------------------------------------------------
            */
            $category->delete();

            return redirect()
                ->route('categories.index')
                ->with(
                    'success',
                    'Catégorie supprimée avec succès.'
                );
        });
    }
}
