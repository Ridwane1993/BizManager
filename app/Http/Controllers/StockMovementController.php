<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StockMovementController extends Controller
{
    /**
     * Afficher l'historique des mouvements.
     */
    public function index(Request $request)
    {
        $query = StockMovement::with([
            'product',
            'source',
        ])->latest();

        // ==================================================
        // FILTRE PAR PRODUIT
        // ==================================================

        if ($request->filled('product_id')) {
            $query->where(
                'product_id',
                $request->product_id
            );
        }

        // ==================================================
        // FILTRE PAR TYPE
        // ==================================================

        if ($request->filled('type')) {
            $query->where(
                'type',
                $request->type
            );
        }

        // ==================================================
        // FILTRE PAR DATE DE DÉBUT
        // ==================================================

        if ($request->filled('start_date')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->start_date
            );
        }

        // ==================================================
        // FILTRE PAR DATE DE FIN
        // ==================================================

        if ($request->filled('end_date')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->end_date
            );
        }

        // ==================================================
        // STATISTIQUES
        // ==================================================

        $totalMovements = (clone $query)->count();

        $totalEntries = (clone $query)
            ->whereIn(
                'type',
                ['in', 'return']
            )
            ->sum('quantity');

        $totalOutputs = (clone $query)
            ->whereIn(
                'type',
                ['out', 'loss']
            )
            ->sum('quantity');

        $totalAdjustments = (clone $query)
            ->where(
                'type',
                'adjustment'
            )
            ->sum('quantity');

        $netMovement =
            $totalEntries
            - $totalOutputs
            + $totalAdjustments;

        // ==================================================
        // PAGINATION
        // ==================================================

        $movements = $query
            ->paginate(15)
            ->withQueryString();

        // ==================================================
        // LISTE DES PRODUITS
        // ==================================================

        $products = Product::orderBy('name')->get();

        return view(
            'stock-movements.index',
            compact(
                'movements',
                'products',
                'totalMovements',
                'totalEntries',
                'totalOutputs',
                'totalAdjustments',
                'netMovement'
            )
        );
    }

    /**
     * Formulaire de création.
     */
    public function create()
    {
        $products = Product::orderBy('name')->get();

        return view(
            'stock-movements.create',
            compact('products')
        );
    }

    /**
     * Enregistrer un mouvement manuel.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => [
                'required',
                Rule::exists('products', 'id')
                    ->where('company_id', $request->user()->company_id),
            ],

            'type' => [
                'required',
                Rule::in([
                    'in',
                    'out',
                    'adjustment',
                    'loss',
                    'return',
                ]),
            ],

            'quantity' => [
                'required',
                'integer',
                'not_in:0',
            ],

            'description' => [
                'nullable',
                'string',
            ],
        ]);

        $type = $validated['type'];

        $quantity = (int) $validated['quantity'];

        // ==================================================
        // LES TYPES CLASSIQUES DOIVENT ÊTRE POSITIFS
        // ==================================================

        if (
            $type !== 'adjustment'
            && $quantity < 0
        ) {
            throw ValidationException::withMessages([
                'quantity' => 'La quantité doit être positive pour '
                    .'une entrée, sortie, perte ou retour.',
            ]);
        }

        DB::transaction(function () use (
            $validated,
            $type,
            $quantity
        ) {

            // ==================================================
            // VERROUILLER LE PRODUIT
            // ==================================================

            $product = Product::whereKey(
                $validated['product_id']
            )
                ->lockForUpdate()
                ->firstOrFail();

            // ==================================================
            // CALCULER L'IMPACT
            // ==================================================

            $stockChange =
                $this->calculateStockChange(
                    $type,
                    $quantity
                );

            // ==================================================
            // CALCULER LE NOUVEAU STOCK
            // ==================================================

            $newStock =
                $product->stock
                + $stockChange;

            // ==================================================
            // EMPÊCHER UN STOCK NÉGATIF
            // ==================================================

            if ($newStock < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Stock insuffisant. '
                        .'Le stock ne peut pas être négatif.',
                ]);
            }

            // ==================================================
            // METTRE À JOUR LE STOCK
            // ==================================================

            $product->update([
                'stock' => $newStock,
            ]);

            // ==================================================
            // CRÉER LE MOUVEMENT MANUEL
            // ==================================================

            StockMovement::create([
                'product_id' => $product->id,
                'type' => $type,
                'quantity' => $quantity,
                'description' => $validated['description'] ?? null,

                // Mouvement manuel :
                // source_type = null
                // source_id   = null
            ]);
        });

        return redirect()
            ->route('stock-movements.index')
            ->with(
                'success',
                'Mouvement de stock enregistré avec succès.'
            );
    }

    /**
     * Afficher un mouvement.
     */
    public function show(
        StockMovement $stock_movement
    ) {
        $stock_movement->load([
            'product',
            'source',
        ]);

        $movement = $stock_movement;

        return view(
            'stock-movements.show',
            compact('movement')
        );
    }

    /**
     * Formulaire de modification.
     */
    public function edit(
        StockMovement $stock_movement
    ) {
        $stock_movement->load([
            'product',
            'source',
        ]);

        $movement = $stock_movement;

        $products = Product::orderBy('name')->get();

        return view(
            'stock-movements.edit',
            compact(
                'movement',
                'products'
            )
        );
    }

    /**
     * Modifier un mouvement manuel.
     */
    public function update(
        Request $request,
        StockMovement $stock_movement
    ) {
        $validated = $request->validate([
            'product_id' => [
                'required',
                Rule::exists('products', 'id')
                    ->where('company_id', $request->user()->company_id),
            ],

            'type' => [
                'required',
                Rule::in([
                    'in',
                    'out',
                    'adjustment',
                    'loss',
                    'return',
                ]),
            ],

            'quantity' => [
                'required',
                'integer',
                'not_in:0',
            ],

            'description' => [
                'nullable',
                'string',
            ],
        ]);

        $newType = $validated['type'];

        $newQuantity = (int) $validated['quantity'];

        // ==================================================
        // LES TYPES CLASSIQUES DOIVENT ÊTRE POSITIFS
        // ==================================================

        if (
            $newType !== 'adjustment'
            && $newQuantity < 0
        ) {
            throw ValidationException::withMessages([
                'quantity' => 'La quantité doit être positive pour '
                    .'une entrée, sortie, perte ou retour.',
            ]);
        }

        DB::transaction(function () use (
            $validated,
            $stock_movement,
            $newType,
            $newQuantity
        ) {

            // ==================================================
            // VERROUILLER LE MOUVEMENT
            // ==================================================

            $movement = StockMovement::whereKey(
                $stock_movement->getKey()
            )
                ->lockForUpdate()
                ->firstOrFail();

            // ==================================================
            // EMPÊCHER LA MODIFICATION D'UN MOUVEMENT AUTOMATIQUE
            // ==================================================

            if (! $movement->isManual()) {
                abort(
                    403,
                    'Les mouvements automatiques ne peuvent pas être modifiés.'
                );
            }

            // ==================================================
            // IDENTIFIER LES PRODUITS
            // ==================================================

            $oldProductId =
                (int) $movement->product_id;

            $newProductId =
                (int) $validated['product_id'];

            // ==================================================
            // VERROUILLER LES PRODUITS
            // DANS UN ORDRE DÉTERMINÉ
            // ==================================================

            $productIds = collect([
                $oldProductId,
                $newProductId,
            ])
                ->unique()
                ->sort()
                ->values();

            $products = Product::whereIn(
                'id',
                $productIds
            )
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $oldProduct =
                $products->get($oldProductId);

            $newProduct =
                $products->get($newProductId);

            if (
                ! $oldProduct
                || ! $newProduct
            ) {
                throw ValidationException::withMessages([
                    'product_id' => 'Le produit sélectionné est introuvable.',
                ]);
            }

            // ==================================================
            // 1. ANNULER L'ANCIEN IMPACT
            // ==================================================

            $oldStockChange =
                $this->calculateStockChange(
                    $movement->type,
                    (int) $movement->quantity
                );

            $stockAfterReversal =
                $oldProduct->stock
                - $oldStockChange;

            if ($stockAfterReversal < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Impossible de modifier ce mouvement '
                        .'car le stock actuel est incohérent.',
                ]);
            }

            $oldProduct->update([
                'stock' => $stockAfterReversal,
            ]);

            // ==================================================
            // 2. CALCULER LE NOUVEL IMPACT
            // ==================================================

            $newStockChange =
                $this->calculateStockChange(
                    $newType,
                    $newQuantity
                );

            // ==================================================
            // 3. APPLIQUER LE NOUVEAU MOUVEMENT
            // ==================================================

            $targetProduct =
                $oldProductId === $newProductId
                    ? $oldProduct
                    : $newProduct;

            $finalStock =
                $targetProduct->stock
                + $newStockChange;

            if ($finalStock < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Stock insuffisant. '
                        .'Le stock ne peut pas être négatif.',
                ]);
            }

            $targetProduct->update([
                'stock' => $finalStock,
            ]);

            // ==================================================
            // 4. METTRE À JOUR LE MOUVEMENT
            // ==================================================

            $movement->update([
                'product_id' => $newProductId,
                'type' => $newType,
                'quantity' => $newQuantity,
                'description' => $validated['description'] ?? null,
            ]);
        });

        return redirect()
            ->route(
                'stock-movements.show',
                $stock_movement
            )
            ->with(
                'success',
                'Mouvement de stock modifié avec succès.'
            );
    }

    /**
     * Supprimer un mouvement manuel.
     */
    public function destroy(
        StockMovement $stock_movement
    ) {
        DB::transaction(function () use (
            $stock_movement
        ) {

            // ==================================================
            // VERROUILLER LE MOUVEMENT
            // ==================================================

            $movement = StockMovement::whereKey(
                $stock_movement->getKey()
            )
                ->lockForUpdate()
                ->firstOrFail();

            // ==================================================
            // EMPÊCHER LA SUPPRESSION D'UN MOUVEMENT AUTOMATIQUE
            // ==================================================

            if (! $movement->isManual()) {
                abort(
                    403,
                    'Les mouvements automatiques ne peuvent pas être supprimés.'
                );
            }

            // ==================================================
            // VERROUILLER LE PRODUIT
            // ==================================================

            $product = Product::whereKey(
                $movement->product_id
            )
                ->lockForUpdate()
                ->firstOrFail();

            // ==================================================
            // CALCULER L'IMPACT À ANNULER
            // ==================================================

            $stockChange =
                $this->calculateStockChange(
                    $movement->type,
                    (int) $movement->quantity
                );

            $newStock =
                $product->stock
                - $stockChange;

            // ==================================================
            // EMPÊCHER LE STOCK NÉGATIF
            // ==================================================

            if ($newStock < 0) {
                throw ValidationException::withMessages([
                    'stock' => 'Impossible de supprimer ce mouvement '
                        .'car cela rendrait le stock négatif.',
                ]);
            }

            // ==================================================
            // RESTAURER LE STOCK
            // ==================================================

            $product->update([
                'stock' => $newStock,
            ]);

            // ==================================================
            // SUPPRIMER LE MOUVEMENT
            // ==================================================

            $movement->delete();
        });

        return redirect()
            ->route('stock-movements.index')
            ->with(
                'success',
                'Mouvement de stock supprimé avec succès.'
            );
    }

    /**
     * Calculer l'impact d'un mouvement.
     *
     * Entrée      => + quantité
     * Retour      => + quantité
     * Sortie      => - quantité
     * Perte       => - quantité
     * Ajustement  => quantité telle quelle
     */
    private function calculateStockChange(
        string $type,
        int $quantity
    ): int {
        return match ($type) {

            'in',
            'return' => abs($quantity),

            'out',
            'loss' => -abs($quantity),

            'adjustment' => $quantity,

            default => 0,
        };
    }
}
