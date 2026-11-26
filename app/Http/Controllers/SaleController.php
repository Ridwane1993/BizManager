<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    /**
     * Afficher la liste des ventes.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => [
                'nullable',
                Rule::in(['pending', 'completed', 'cancelled']),
            ],
            'start_date' => [
                'nullable',
                'date_format:Y-m-d',
                'required_with:end_date',
                'before_or_equal:end_date',
            ],
            'end_date' => [
                'nullable',
                'date_format:Y-m-d',
                'required_with:start_date',
                'after_or_equal:start_date',
            ],
        ]);

        $query = Sale::with('client');
        $search = trim($validated['search'] ?? '');

        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('sale_number', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($clientQuery) use ($search): void {
                        $clientQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (isset($validated['start_date'])) {
            $query->whereDate('sale_date', '>=', $validated['start_date']);
        }

        if (isset($validated['end_date'])) {
            $query->whereDate('sale_date', '<=', $validated['end_date']);
        }

        $sales = $query
            ->latest('sale_date')
            ->paginate(15)
            ->withQueryString();
        $filters = [
            'search' => $search,
            'status' => $validated['status'] ?? '',
            'start_date' => $validated['start_date'] ?? '',
            'end_date' => $validated['end_date'] ?? '',
        ];

        return view(
            'sales.index',
            compact('sales', 'filters')
        );
    }

    /**
     * Afficher le formulaire de création.
     */
    public function create()
    {
        $clients = Client::orderBy('name')->get();

        $products = Product::orderBy('name')->get();

        return view(
            'sales.create',
            compact('clients', 'products')
        );
    }

    /**
     * Enregistrer une nouvelle vente.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => [
                'required',
                Rule::exists('clients', 'id')
                    ->where('company_id', $request->user()->company_id),
            ],

            'sale_date' => [
                'required',
                'date',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'status' => [
                'sometimes',
                Rule::in(['pending', 'completed']),
            ],

            'products' => [
                'required',
                'array',
                'min:1',
            ],

            'products.*.product_id' => [
                'required',
                Rule::exists('products', 'id')
                    ->where('company_id', $request->user()->company_id),
                'distinct',
            ],

            'products.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        $isFinal = ($validated['status'] ?? 'completed') === 'completed';

        DB::transaction(function () use ($validated, $isFinal) {

            // ==================================================
            // 1. GÉNÉRER LE NUMÉRO DE VENTE
            // ==================================================

            do {
                $saleNumber =
                    'V-'
                    .now()->format('Ymd')
                    .'-'
                    .Str::upper(Str::random(6));

            } while (
                Sale::where(
                    'sale_number',
                    $saleNumber
                )->exists()
            );

            // ==================================================
            // 2. CRÉER LA VENTE
            // ==================================================

            $sale = Sale::create([
                'client_id' => $validated['client_id'],
                'sale_number' => $saleNumber,
                'sale_date' => $validated['sale_date'],
                'total_amount' => 0,
                'status' => $isFinal ? 'completed' : 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            // ==================================================
            // 3. RÉCUPÉRER ET VERROUILLER LES PRODUITS
            // ==================================================

            $productIds = collect($validated['products'])
                ->pluck('product_id')
                ->map(fn ($id) => (int) $id)
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

            $totalAmount = 0;

            // ==================================================
            // 4. TRAITER LES PRODUITS
            // ==================================================

            foreach ($validated['products'] as $item) {

                $productId = (int) $item['product_id'];

                $product = $products->get($productId);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'products' => 'Le produit sélectionné est introuvable.',
                    ]);
                }

                $quantity = (int) $item['quantity'];

                // ==================================================
                // 4.1 VÉRIFIER LE STOCK
                // ==================================================

                if (
                    $isFinal
                    && $product->stock < $quantity
                ) {
                    throw ValidationException::withMessages([
                        'products' => 'Stock insuffisant pour le produit : '
                            .$product->name
                            .'. Stock disponible : '
                            .$product->stock
                            .'. Quantité demandée : '
                            .$quantity
                            .'.',
                    ]);
                }

                // ==================================================
                // 4.2 PRIX DE VENTE
                // ==================================================

                $unitPrice = round(
                    (float) $product->selling_price,
                    2
                );

                $subtotal = round(
                    $unitPrice * $quantity,
                    2
                );

                $totalAmount += $subtotal;

                // ==================================================
                // 4.3 CRÉER LA LIGNE DE VENTE
                // ==================================================

                $sale->items()->create([
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                // ==================================================
                // 4.4 DIMINUER LE STOCK ET ENREGISTRER LE MOUVEMENT
                //     (uniquement pour une vente validée)
                // ==================================================

                if ($isFinal) {

                    $product->decrement(
                        'stock',
                        $quantity
                    );

                    StockMovement::create([
                        'product_id' => $productId,
                        'type' => 'out',
                        'quantity' => $quantity,
                        'description' => 'Vente '
                            .$sale->sale_number,
                        'source_type' => Sale::class,
                        'source_id' => $sale->id,
                    ]);
                }
            }

            // ==================================================
            // 5. METTRE À JOUR LE TOTAL
            // ==================================================

            $sale->update([
                'total_amount' => round(
                    $totalAmount,
                    2
                ),
            ]);
        });

        return redirect()
            ->route('sales.index')
            ->with(
                'success',
                $isFinal
                    ? 'Vente enregistrée avec succès.'
                    : 'Brouillon de vente enregistré. Validez la vente pour décrémenter le stock.'
            );
    }

    /**
     * Afficher une vente.
     */
    public function show(Sale $sale)
    {
        $sale->load([
            'client',
            'items.product',
        ]);

        return view(
            'sales.show',
            compact('sale')
        );
    }

    /**
     * Afficher le formulaire de modification.
     */
    public function edit(Sale $sale)
    {
        $sale->load([
            'client',
            'items.product',
        ]);

        $clients = Client::orderBy('name')->get();

        $products = Product::orderBy('name')->get();

        return view(
            'sales.edit',
            compact(
                'sale',
                'clients',
                'products'
            )
        );
    }

    /**
     * Modifier une vente.
     */
    public function update(
        Request $request,
        Sale $sale
    ) {
        $validated = $request->validate([
            'client_id' => [
                'required',
                Rule::exists('clients', 'id')
                    ->where('company_id', $request->user()->company_id),
            ],

            'sale_date' => [
                'required',
                'date',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'products' => [
                'required',
                'array',
                'min:1',
            ],

            'products.*.product_id' => [
                'required',
                Rule::exists('products', 'id')
                    ->where('company_id', $request->user()->company_id),
                'distinct',
            ],

            'products.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        $appliesToStock = $sale->status === 'completed';

        DB::transaction(function () use (
            $sale,
            $validated,
            $appliesToStock
        ) {

            // ==================================================
            // 1. VERROUILLER LA VENTE
            // ==================================================

            $sale = Sale::whereKey($sale->id)
                ->lockForUpdate()
                ->firstOrFail();

            $sale->load('items');

            // ==================================================
            // 2. CONSERVER LES ANCIENNES DONNÉES
            // ==================================================

            /*
            |--------------------------------------------------------------------------
            | On conserve les anciennes lignes afin de :
            |
            | 1. calculer les différences de stock ;
            | 2. conserver les anciens prix de vente.
            |--------------------------------------------------------------------------
            */

            $oldItems = $sale->items
                ->keyBy('product_id');

            $oldQuantities = $sale->items
                ->groupBy('product_id')
                ->map(
                    fn ($items) => $items->sum('quantity')
                );

            /*
            |--------------------------------------------------------------------------
            | Une vente completed a déjà retiré du stock.
            |
            | Les autres statuts n'ont normalement aucun effet
            | sur le stock.
            |--------------------------------------------------------------------------
            */

            if ($sale->status !== 'completed') {
                $oldQuantities = collect();
            }

            // ==================================================
            // 3. NOUVELLES QUANTITÉS
            // ==================================================

            $newQuantities = collect(
                $validated['products']
            )->mapWithKeys(function ($item) {

                return [
                    (int) $item['product_id'] => (int) $item['quantity'],
                ];
            });

            /*
            |--------------------------------------------------------------------------
            | Un brouillon ou une vente annulée n'a aucun effet sur le stock :
            | les lignes sont modifiables sans créer de mouvement.
            |--------------------------------------------------------------------------
            */

            $stockNewQuantities = $appliesToStock
                ? $newQuantities
                : collect();

            // ==================================================
            // 4. PRODUITS CONCERNÉS
            // ==================================================

            $affectedProductIds = collect(
                $oldQuantities->keys()
            )
                ->merge(
                    $newQuantities->keys()
                )
                ->map(
                    fn ($id) => (int) $id
                )
                ->unique()
                ->sort()
                ->values();

            // ==================================================
            // 5. VERROUILLER LES PRODUITS
            // ==================================================

            $products = Product::whereIn(
                'id',
                $affectedProductIds
            )
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // ==================================================
            // 6. CALCULER LES VARIATIONS DE STOCK
            // ==================================================

            $stockChanges = [];

            foreach ($affectedProductIds as $productId) {

                $oldQuantity = (int) (
                    $oldQuantities[$productId] ?? 0
                );

                $newQuantity = (int) (
                    $stockNewQuantities[$productId] ?? 0
                );

                /*
                |--------------------------------------------------------------------------
                | Impact réel sur le stock :
                |
                | ancien = 5
                | nouveau = 8
                |
                | 5 - 8 = -3
                | → retirer 3 du stock
                |
                | ancien = 8
                | nouveau = 5
                |
                | 8 - 5 = +3
                | → remettre 3 en stock
                |--------------------------------------------------------------------------
                */

                $stockDifference =
                    $oldQuantity - $newQuantity;

                $product = $products->get($productId);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'products' => 'Un des produits concernés est introuvable.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Vérifier le stock uniquement lorsqu'il faut
                | retirer des unités supplémentaires.
                |--------------------------------------------------------------------------
                */

                if (
                    $stockDifference < 0
                    && $product->stock < abs($stockDifference)
                ) {
                    throw ValidationException::withMessages([
                        'products' => 'Stock insuffisant pour le produit : '
                            .$product->name
                            .'. Stock disponible : '
                            .$product->stock
                            .'. Quantité supplémentaire nécessaire : '
                            .abs($stockDifference)
                            .'.',
                    ]);
                }

                $stockChanges[$productId] =
                    $stockDifference;
            }

            // ==================================================
            // 7. SUPPRIMER LES ANCIENNES LIGNES
            // ==================================================

            $sale->items()->delete();

            // ==================================================
            // 8. RECRÉER LES NOUVELLES LIGNES
            // ==================================================

            $totalAmount = 0;

            foreach ($validated['products'] as $item) {

                $productId =
                    (int) $item['product_id'];

                $product =
                    $products->get($productId);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'products' => 'Le produit sélectionné est introuvable.',
                    ]);
                }

                $quantity =
                    (int) $item['quantity'];

                /*
                |--------------------------------------------------------------------------
                | CONSERVER LE PRIX HISTORIQUE
                |--------------------------------------------------------------------------
                |
                | Si le produit était déjà présent dans la vente,
                | on conserve le prix enregistré dans sale_items.
                |
                | Si le produit est nouveau dans la vente,
                | on utilise le prix actuel du produit.
                |--------------------------------------------------------------------------
                */

                if ($oldItems->has($productId)) {

                    $unitPrice = round(
                        (float) $oldItems[$productId]->unit_price,
                        2
                    );

                } else {

                    $unitPrice = round(
                        (float) $product->selling_price,
                        2
                    );
                }

                $subtotal = round(
                    $unitPrice * $quantity,
                    2
                );

                $totalAmount += $subtotal;

                $sale->items()->create([
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);
            }

            // ==================================================
            // 9. APPLIQUER LES VARIATIONS DE STOCK
            // ==================================================

            foreach (
                $stockChanges as $productId => $difference
            ) {

                if ($difference === 0) {
                    continue;
                }

                $product =
                    $products->get($productId);

                /*
                |--------------------------------------------------------------------------
                | Différence positive
                | → retour de stock
                |--------------------------------------------------------------------------
                */

                if ($difference > 0) {

                    $product->increment(
                        'stock',
                        $difference
                    );

                    StockMovement::create([
                        'product_id' => $productId,
                        'type' => 'return',
                        'quantity' => $difference,
                        'description' => 'Réduction de la vente '
                            .$sale->sale_number,
                        'source_type' => Sale::class,
                        'source_id' => $sale->id,
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Différence négative
                    | → retrait supplémentaire
                    |--------------------------------------------------------------------------
                    */

                } else {

                    $quantityOut =
                        abs($difference);

                    $product->decrement(
                        'stock',
                        $quantityOut
                    );

                    StockMovement::create([
                        'product_id' => $productId,
                        'type' => 'out',
                        'quantity' => $quantityOut,
                        'description' => 'Augmentation de la vente '
                            .$sale->sale_number,
                        'source_type' => Sale::class,
                        'source_id' => $sale->id,
                    ]);
                }
            }

            // ==================================================
            // 10. METTRE À JOUR LA VENTE
            // ==================================================

            $sale->update([
                'client_id' => $validated['client_id'],
                'sale_date' => $validated['sale_date'],
                'total_amount' => round(
                    $totalAmount,
                    2
                ),
                'status' => $sale->status,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()
            ->route('sales.index')
            ->with(
                'success',
                $appliesToStock
                    ? 'Vente modifiée avec succès et stock mis à jour.'
                    : 'Brouillon modifié avec succès. Le stock sera mis à jour lors de la validation.'
            );
    }

    /**
     * Valider un brouillon : le stock est décrémenté à ce moment.
     */
    public function complete(Sale $sale)
    {
        $result = DB::transaction(function () use ($sale): array {
            $sale = Sale::whereKey($sale->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($sale->status !== 'pending') {
                return [false, 'Seule une vente en brouillon peut être validée.'];
            }

            $sale->load('items');

            $quantities = $sale->items
                ->groupBy('product_id')
                ->map(fn ($items) => $items->sum('quantity'));

            $productIds = $quantities->keys()
                ->map(fn ($id) => (int) $id)
                ->sort()
                ->values();

            $products = Product::whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($quantities as $productId => $quantity) {
                $product = $products->get((int) $productId);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'sale' => 'Un produit de cette vente est introuvable.',
                    ]);
                }

                if ($product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'sale' => 'Stock insuffisant pour le produit : '
                            .$product->name
                            .'. Stock disponible : '
                            .$product->stock
                            .'.',
                    ]);
                }
            }

            foreach ($quantities as $productId => $quantity) {
                $products->get((int) $productId)->decrement('stock', $quantity);

                StockMovement::create([
                    'product_id' => (int) $productId,
                    'type' => 'out',
                    'quantity' => $quantity,
                    'description' => 'Validation de la vente '
                        .$sale->sale_number,
                    'source_type' => Sale::class,
                    'source_id' => $sale->id,
                ]);
            }

            $sale->update(['status' => 'completed']);

            return [true, 'Vente validée avec succès. Le stock a été mis à jour.'];
        });

        return redirect()
            ->route('sales.show', $sale)
            ->with($result[0] ? 'success' : 'error', $result[1]);
    }

    /**
     * Annuler une vente : le stock est restauré si elle était validée.
     */
    public function cancel(Sale $sale)
    {
        $result = DB::transaction(function () use ($sale): array {
            $sale = Sale::whereKey($sale->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($sale->status === 'cancelled') {
                return [false, 'Cette vente est déjà annulée.'];
            }

            if (
                $sale->status === 'completed'
                && $sale->invoice()->exists()
            ) {
                return [false, 'Impossible d\'annuler une vente déjà facturée.'];
            }

            if ($sale->status === 'completed') {
                $sale->load('items');

                $quantities = $sale->items
                    ->groupBy('product_id')
                    ->map(fn ($items) => $items->sum('quantity'));

                $productIds = $quantities->keys()
                    ->map(fn ($id) => (int) $id)
                    ->sort()
                    ->values();

                $products = Product::whereIn('id', $productIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($quantities as $productId => $quantity) {
                    $product = $products->get((int) $productId);

                    if (! $product) {
                        throw ValidationException::withMessages([
                            'sale' => 'Un produit de cette vente est introuvable.',
                        ]);
                    }

                    $product->increment('stock', $quantity);

                    StockMovement::create([
                        'product_id' => (int) $productId,
                        'type' => 'return',
                        'quantity' => $quantity,
                        'description' => 'Annulation de la vente '
                            .$sale->sale_number,
                        'source_type' => Sale::class,
                        'source_id' => $sale->id,
                    ]);
                }
            }

            $sale->update(['status' => 'cancelled']);

            return [true, 'Vente annulée avec succès.'];
        });

        return redirect()
            ->route('sales.show', $sale)
            ->with($result[0] ? 'success' : 'error', $result[1]);
    }

    /**
     * Supprimer une vente.
     */
    public function destroy(Sale $sale)
    {
        $deleted = DB::transaction(function () use ($sale) {

            // ==================================================
            // 1. VERROUILLER LA VENTE
            // ==================================================

            $sale = Sale::whereKey($sale->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($sale->invoice()->exists()) {
                return false;
            }

            $sale->load('items');

            // ==================================================
            // 2. RÉCUPÉRER LES PRODUITS
            // ==================================================

            $productQuantities = $sale->items
                ->groupBy('product_id')
                ->map(
                    fn ($items) => $items->sum('quantity')
                );

            $productIds = $productQuantities
                ->keys()
                ->map(
                    fn ($id) => (int) $id
                )
                ->sort()
                ->values();

            // ==================================================
            // 3. VERROUILLER LES PRODUITS
            // ==================================================

            $products = Product::whereIn(
                'id',
                $productIds
            )
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // ==================================================
            // 4. RESTAURER LE STOCK SI LA VENTE ÉTAIT COMPLETED
            // ==================================================

            if ($sale->status === 'completed') {

                foreach (
                    $productQuantities as $productId => $quantity
                ) {

                    $product = $products->get(
                        (int) $productId
                    );

                    if (! $product) {
                        throw ValidationException::withMessages([
                            'sale' => 'Un produit de cette vente est introuvable.',
                        ]);
                    }

                    // Restaurer le stock
                    $product->increment(
                        'stock',
                        $quantity
                    );

                    // Enregistrer le retour
                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => 'return',
                        'quantity' => $quantity,
                        'description' => 'Annulation de la vente '
                            .$sale->sale_number,
                        'source_type' => Sale::class,
                        'source_id' => $sale->id,
                    ]);
                }
            }

            // ==================================================
            // 5. SUPPRIMER LES LIGNES
            // ==================================================

            $sale->items()->delete();

            // ==================================================
            // 6. SUPPRIMER LA VENTE
            // ==================================================

            $sale->delete();

            return true;
        });

        if (! $deleted) {
            return redirect()
                ->route('sales.show', $sale)
                ->with(
                    'error',
                    'Impossible de supprimer une vente déjà facturée.'
                );
        }

        return redirect()
            ->route('sales.index')
            ->with(
                'success',
                $sale->status === 'completed'
                    ? 'Vente supprimée avec succès et stock restauré.'
                    : 'Brouillon supprimé avec succès.'
            );
    }
}
