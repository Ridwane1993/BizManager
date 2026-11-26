<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Afficher la liste des produits.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')
                    ->where('company_id', $request->user()->company_id),
            ],
            'low_stock' => ['sometimes', 'boolean'],
        ]);

        $query = Product::with('category');
        $search = trim($validated['search'] ?? '');

        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        if (isset($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        $lowStock = $request->boolean('low_stock');

        if ($lowStock) {
            $query->whereColumn('stock', '<=', 'stock_alert_threshold');
        }

        $products = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();
        $filters = [
            'search' => $search,
            'category_id' => $validated['category_id'] ?? '',
            'low_stock' => $lowStock,
        ];

        return view(
            'products.index',
            compact('products', 'categories', 'filters')
        );
    }

    /**
     * Formulaire de création.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view(
            'products.create',
            compact('categories')
        );
    }

    /**
     * Enregistrer un nouveau produit.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')
                    ->where('company_id', $request->user()->company_id),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'reference' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'reference')
                    ->where('company_id', $request->user()->company_id),
            ],

            'purchase_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'selling_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'stock' => [
                'required',
                'integer',
                'min:0',
            ],

            'stock_alert_threshold' => [
                'required',
                'integer',
                'min:0',
            ],

            'description' => [
                'nullable',
                'string',
            ],
        ]);

        DB::transaction(function () use ($validated) {

            /*
            |--------------------------------------------------------------------------
            | 1. Créer le produit
            |--------------------------------------------------------------------------
            */
            $product = Product::create($validated);

            /*
            |--------------------------------------------------------------------------
            | 2. Enregistrer le stock initial
            |--------------------------------------------------------------------------
            |
            | Le stock initial est généré automatiquement lors de la
            | création du produit.
            |
            | On lui associe donc le produit comme source afin qu'il
            | ne soit pas considéré comme un mouvement manuel.
            |--------------------------------------------------------------------------
            */
            if ($product->stock > 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'in',
                    'quantity' => $product->stock,
                    'description' => 'Stock initial du produit',
                    'source_type' => Product::class,
                    'source_id' => $product->id,
                ]);
            }
        });

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Produit créé avec succès.'
            );
    }

    /**
     * Afficher les détails du produit.
     */
    public function show(Product $product)
    {
        /*
        |--------------------------------------------------------------------------
        | Charger la catégorie
        |--------------------------------------------------------------------------
        */
        $product->load('category');

        /*
        |--------------------------------------------------------------------------
        | Historique des mouvements
        |--------------------------------------------------------------------------
        */
        $stockMovements = $product->stockMovements()
            ->with('source')
            ->latest()
            ->paginate(10);

        /*
        |--------------------------------------------------------------------------
        | Résumé du stock
        |--------------------------------------------------------------------------
        */
        $stockSummary = $product->stockMovements()
            ->selectRaw("
                COALESCE(
                    SUM(
                        CASE
                            WHEN type = 'in'
                            THEN quantity
                            ELSE 0
                        END
                    ),
                    0
                ) AS total_entries
            ")
            ->selectRaw("
                COALESCE(
                    SUM(
                        CASE
                            WHEN type = 'out'
                            THEN quantity
                            ELSE 0
                        END
                    ),
                    0
                ) AS total_outputs
            ")
            ->selectRaw("
                COALESCE(
                    SUM(
                        CASE
                            WHEN type = 'loss'
                            THEN quantity
                            ELSE 0
                        END
                    ),
                    0
                ) AS total_losses
            ")
            ->selectRaw("
                COALESCE(
                    SUM(
                        CASE
                            WHEN type = 'return'
                            THEN quantity
                            ELSE 0
                        END
                    ),
                    0
                ) AS total_returns
            ")
            ->selectRaw("
                COALESCE(
                    SUM(
                        CASE
                            WHEN type = 'adjustment'
                            THEN quantity
                            ELSE 0
                        END
                    ),
                    0
                ) AS total_adjustments
            ")
            ->first();

        return view(
            'products.show',
            compact(
                'product',
                'stockMovements',
                'stockSummary'
            )
        );
    }

    /**
     * Formulaire de modification.
     */
    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();

        return view(
            'products.edit',
            compact(
                'product',
                'categories'
            )
        );
    }

    /**
     * Modifier un produit.
     */
    public function update(
        Request $request,
        Product $product
    ) {
        $validated = $request->validate([
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')
                    ->where('company_id', $request->user()->company_id),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'reference' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'reference')
                    ->where('company_id', $request->user()->company_id)
                    ->ignore($product->id),
            ],

            'purchase_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'selling_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'stock' => [
                'required',
                'integer',
                'min:0',
            ],

            'stock_alert_threshold' => [
                'required',
                'integer',
                'min:0',
            ],

            'description' => [
                'nullable',
                'string',
            ],
        ]);

        DB::transaction(function () use (
            $validated,
            $product
        ) {
            /*
            |--------------------------------------------------------------------------
            | Verrouiller le produit
            |--------------------------------------------------------------------------
            */
            $product = Product::whereKey($product->id)
                ->lockForUpdate()
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | Ancien stock
            |--------------------------------------------------------------------------
            */
            $oldStock = (int) $product->stock;

            /*
            |--------------------------------------------------------------------------
            | Nouveau stock
            |--------------------------------------------------------------------------
            */
            $newStock = (int) $validated['stock'];

            /*
            |--------------------------------------------------------------------------
            | Différence de stock
            |--------------------------------------------------------------------------
            */
            $stockDifference = $newStock - $oldStock;

            /*
            |--------------------------------------------------------------------------
            | Mettre à jour le produit
            |--------------------------------------------------------------------------
            */
            $product->update($validated);

            /*
            |--------------------------------------------------------------------------
            | Enregistrer la correction manuelle du stock
            |--------------------------------------------------------------------------
            |
            | Un adjustment est signé :
            |
            | +3 = ajout de 3 unités
            | -3 = retrait de 3 unités
            |
            | Comme ce mouvement est créé manuellement, aucune source
            | n'est renseignée.
            |--------------------------------------------------------------------------
            */
            if ($stockDifference !== 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'adjustment',
                    'quantity' => $stockDifference,
                    'description' => 'Correction manuelle du stock',
                    'source_type' => null,
                    'source_id' => null,
                ]);
            }
        });

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Produit modifié avec succès.'
            );
    }

    /**
     * Supprimer un produit.
     */
    public function destroy(Product $product)
    {
        return DB::transaction(function () use ($product) {

            /*
            |--------------------------------------------------------------------------
            | Verrouiller le produit
            |--------------------------------------------------------------------------
            |
            | Cela évite qu'une autre opération modifie le produit
            | pendant que nous effectuons les vérifications.
            |--------------------------------------------------------------------------
            */
            $product = Product::whereKey($product->id)
                ->lockForUpdate()
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | Vérifier les mouvements de stock
            |--------------------------------------------------------------------------
            */
            if ($product->stockMovements()->exists()) {
                return redirect()
                    ->route('products.index')
                    ->with(
                        'error',
                        'Impossible de supprimer ce produit car il possède un historique de mouvements de stock.'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | Vérifier les lignes de vente
            |--------------------------------------------------------------------------
            */
            if ($product->saleItems()->exists()) {
                return redirect()
                    ->route('products.index')
                    ->with(
                        'error',
                        'Impossible de supprimer ce produit car il est utilisé dans des ventes.'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | Vérifier les lignes d'achat
            |--------------------------------------------------------------------------
            */
            if ($product->purchaseItems()->exists()) {
                return redirect()
                    ->route('products.index')
                    ->with(
                        'error',
                        'Impossible de supprimer ce produit car il est utilisé dans des achats.'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | Supprimer le produit
            |--------------------------------------------------------------------------
            */
            $product->delete();

            return redirect()
                ->route('products.index')
                ->with(
                    'success',
                    'Produit supprimé avec succès.'
                );
        });
    }
}
