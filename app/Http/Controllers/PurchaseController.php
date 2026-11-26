<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    /**
     * Afficher la liste des achats.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => [
                'nullable',
                Rule::in(['pending', 'received', 'cancelled']),
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

        $query = Purchase::with('supplier');
        $search = trim($validated['search'] ?? '');

        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('purchase_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($supplierQuery) use ($search): void {
                        $supplierQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (isset($validated['start_date'])) {
            $query->whereDate('purchase_date', '>=', $validated['start_date']);
        }

        if (isset($validated['end_date'])) {
            $query->whereDate('purchase_date', '<=', $validated['end_date']);
        }

        $purchases = $query
            ->latest('purchase_date')
            ->paginate(15)
            ->withQueryString();
        $filters = [
            'search' => $search,
            'status' => $validated['status'] ?? '',
            'start_date' => $validated['start_date'] ?? '',
            'end_date' => $validated['end_date'] ?? '',
        ];

        return view('purchases.index', compact('purchases', 'filters'));
    }

    /**
     * Formulaire de création.
     */
    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('purchases.create', compact('suppliers', 'products'));
    }

    /**
     * Enregistrer un achat.
     */
    public function store(StorePurchaseRequest $request)
    {
        $validated = $request->validated();

        $isFinal = ($validated['status'] ?? 'received') === 'received';

        DB::transaction(function () use ($validated, $isFinal) {

            // ==================================================
            // 1. GÉNÉRER LE NUMÉRO DE L'ACHAT
            // ==================================================

            do {
                $purchaseNumber =
                    'A-'
                    .now()->format('Ymd')
                    .'-'
                    .Str::upper(Str::random(6));

            } while (
                Purchase::where(
                    'purchase_number',
                    $purchaseNumber
                )->exists()
            );

            // ==================================================
            // 2. CRÉER L'ACHAT
            // ==================================================

            $purchase = Purchase::create([
                'supplier_id' => $validated['supplier_id'],
                'purchase_number' => $purchaseNumber,
                'purchase_date' => $validated['purchase_date'],
                'total_amount' => 0,
                'status' => $isFinal ? 'received' : 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            // ==================================================
            // 3. VERROUILLER TOUS LES PRODUITS
            //    DANS UN ORDRE DÉTERMINÉ
            // ==================================================

            $productIds = collect($validated['products'])
                ->pluck('product_id')
                ->map(fn ($id) => (int) $id)
                ->sort()
                ->values();

            $products = Product::whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $totalAmount = 0;
            $affectedProductIds = [];

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

                $unitPrice = round(
                    (float) $item['unit_price'],
                    2
                );

                $subtotal = round(
                    $quantity * $unitPrice,
                    2
                );

                $totalAmount += $subtotal;

                $affectedProductIds[] = $productId;

                // ==================================================
                // 4.1 CRÉER LA LIGNE D'ACHAT
                // ==================================================

                $purchase->items()->create([
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                // ==================================================
                // 4.2 AUGMENTER LE STOCK ET ENREGISTRER LE MOUVEMENT
                //     (uniquement pour un achat réceptionné)
                // ==================================================

                if ($isFinal) {

                    $product->increment(
                        'stock',
                        $quantity
                    );

                    StockMovement::create([
                        'product_id' => $productId,
                        'type' => 'in',
                        'quantity' => $quantity,

                        'description' => 'Achat '
                            .$purchase->purchase_number,

                        'source_type' => Purchase::class,
                        'source_id' => $purchase->id,
                    ]);
                }
            }

            // ==================================================
            // 5. METTRE À JOUR LE TOTAL
            // ==================================================

            $purchase->update([
                'total_amount' => round($totalAmount, 2),
            ]);

            // ==================================================
            // 6. SYNCHRONISER LE DERNIER PRIX D'ACHAT
            // ==================================================

            if ($isFinal) {
                foreach (array_unique($affectedProductIds) as $productId) {
                    $this->syncPurchasePrice($productId);
                }
            }
        });

        return redirect()
            ->route('purchases.index')
            ->with(
                'success',
                $isFinal
                    ? 'Achat enregistré avec succès et stock mis à jour.'
                    : 'Brouillon d\'achat enregistré. Réceptionnez l\'achat pour mettre le stock à jour.'
            );
    }

    /**
     * Afficher un achat.
     */
    public function show(Purchase $purchase)
    {
        $purchase->load([
            'supplier',
            'items.product',
        ]);

        return view(
            'purchases.show',
            compact('purchase')
        );
    }

    /**
     * Formulaire de modification.
     */
    public function edit(Purchase $purchase)
    {
        $purchase->load('items');

        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view(
            'purchases.edit',
            compact(
                'purchase',
                'suppliers',
                'products'
            )
        );
    }

    /**
     * Modifier un achat.
     */
    public function update(
        UpdatePurchaseRequest $request,
        Purchase $purchase
    ) {
        $validated = $request->validated();

        $appliesToStock = $purchase->status === 'received';

        DB::transaction(function () use (
            $purchase,
            $validated,
            $appliesToStock
        ) {

            // ==================================================
            // 1. VERROUILLER L'ACHAT
            // ==================================================

            $purchase = Purchase::whereKey($purchase->id)
                ->lockForUpdate()
                ->firstOrFail();

            $purchase->load('items');

            // ==================================================
            // 2. QUANTITÉS DE L'ANCIEN ACHAT
            // ==================================================

            $oldQuantities = $purchase->items
                ->groupBy('product_id')
                ->map(
                    fn ($items) => $items->sum('quantity')
                );

            // ==================================================
            // 3. QUANTITÉS DU NOUVEL ACHAT
            // ==================================================

            $newQuantities = collect($validated['products'])
                ->mapWithKeys(function ($item) {

                    return [
                        (int) $item['product_id'] => (int) $item['quantity'],
                    ];
                });

            // ==================================================
            // 4. PRODUITS CONCERNÉS
            // ==================================================

            $affectedProductIds = collect(
                $oldQuantities->keys()
            )
                ->merge(
                    $newQuantities->keys()
                )
                ->map(fn ($id) => (int) $id)
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

                $oldQuantity =
                    $appliesToStock
                        ? (int) ($oldQuantities[$productId] ?? 0)
                        : 0;

                $newQuantity =
                    $appliesToStock
                        ? (int) ($newQuantities[$productId] ?? 0)
                        : 0;

                $stockDifference =
                    $newQuantity - $oldQuantity;

                $product = $products->get($productId);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'products' => 'Un des produits concernés est introuvable.',
                    ]);
                }

                // ==================================================
                // Vérifier qu'un stock ne deviendra pas négatif
                // ==================================================

                if (
                    $stockDifference < 0
                    && $product->stock < abs($stockDifference)
                ) {

                    throw ValidationException::withMessages([
                        'products' => "Impossible de modifier l'achat "
                            .$purchase->purchase_number
                            .'. Le stock du produit '
                            .$product->name
                            .' est insuffisant pour effectuer '
                            .'cette modification.',
                    ]);
                }

                $stockChanges[$productId] =
                    $stockDifference;
            }

            // ==================================================
            // 7. SUPPRIMER LES ANCIENNES LIGNES
            // ==================================================

            $purchase->items()->delete();

            // ==================================================
            // 8. CRÉER LES NOUVELLES LIGNES
            // ==================================================

            $totalAmount = 0;

            foreach ($validated['products'] as $item) {

                $productId = (int) $item['product_id'];

                $quantity = (int) $item['quantity'];

                $unitPrice = round(
                    (float) $item['unit_price'],
                    2
                );

                $subtotal = round(
                    $quantity * $unitPrice,
                    2
                );

                $totalAmount += $subtotal;

                $purchase->items()->create([
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);
            }

            // ==================================================
            // 9. APPLIQUER LES VARIATIONS DE STOCK
            // ==================================================

            foreach ($stockChanges as $productId => $difference) {

                if ($difference === 0) {
                    continue;
                }

                $product = $products->get($productId);

                if ($difference > 0) {

                    $product->increment(
                        'stock',
                        $difference
                    );

                    StockMovement::create([
                        'product_id' => $productId,
                        'type' => 'in',
                        'quantity' => $difference,

                        'description' => 'Modification de l\'achat '
                            .$purchase->purchase_number,

                        'source_type' => Purchase::class,
                        'source_id' => $purchase->id,
                    ]);
                } else {

                    $quantityOut = abs($difference);

                    $product->decrement(
                        'stock',
                        $quantityOut
                    );

                    StockMovement::create([
                        'product_id' => $productId,
                        'type' => 'out',
                        'quantity' => $quantityOut,

                        'description' => 'Modification de l\'achat '
                            .$purchase->purchase_number,

                        'source_type' => Purchase::class,
                        'source_id' => $purchase->id,
                    ]);
                }
            }

            // ==================================================
            // 10. METTRE À JOUR L'ACHAT
            // ==================================================

            $purchase->update([
                'supplier_id' => $validated['supplier_id'],
                'purchase_date' => $validated['purchase_date'],
                'total_amount' => round($totalAmount, 2),
                'status' => $purchase->status,
                'notes' => $validated['notes'] ?? null,
            ]);

            // ==================================================
            // 11. SYNCHRONISER LE DERNIER PRIX D'ACHAT
            // ==================================================

            if ($appliesToStock) {
                foreach ($affectedProductIds as $productId) {
                    $this->syncPurchasePrice($productId);
                }
            }
        });

        return redirect()
            ->route('purchases.index')
            ->with(
                'success',
                $appliesToStock
                    ? 'Achat modifié avec succès et stock mis à jour.'
                    : 'Brouillon modifié avec succès. Le stock sera mis à jour lors de la réception.'
            );
    }

    /**
     * Réceptionner un brouillon : le stock est augmenté à ce moment.
     */
    public function receive(Purchase $purchase)
    {
        $result = DB::transaction(function () use ($purchase): array {
            $purchase = Purchase::whereKey($purchase->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($purchase->status !== 'pending') {
                return [false, 'Seul un achat en brouillon peut être réceptionné.'];
            }

            $purchase->load('items');

            $quantities = $purchase->items
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
                if (! $products->get((int) $productId)) {
                    throw ValidationException::withMessages([
                        'purchase' => 'Un produit de cet achat est introuvable.',
                    ]);
                }
            }

            foreach ($quantities as $productId => $quantity) {
                $products->get((int) $productId)->increment('stock', $quantity);

                StockMovement::create([
                    'product_id' => (int) $productId,
                    'type' => 'in',
                    'quantity' => $quantity,
                    'description' => 'Réception de l\'achat '
                        .$purchase->purchase_number,
                    'source_type' => Purchase::class,
                    'source_id' => $purchase->id,
                ]);
            }

            $purchase->update(['status' => 'received']);

            foreach ($productIds as $productId) {
                $this->syncPurchasePrice($productId);
            }

            return [true, 'Achat réceptionné avec succès. Le stock a été mis à jour.'];
        });

        return redirect()
            ->route('purchases.show', $purchase)
            ->with($result[0] ? 'success' : 'error', $result[1]);
    }

    /**
     * Annuler un achat : le stock est décrémenté s'il était réceptionné.
     */
    public function cancel(Purchase $purchase)
    {
        $result = DB::transaction(function () use ($purchase): array {
            $purchase = Purchase::whereKey($purchase->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($purchase->status === 'cancelled') {
                return [false, 'Cet achat est déjà annulé.'];
            }

            $productIds = collect();

            if ($purchase->status === 'received') {
                $purchase->load('items');

                $quantities = $purchase->items
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
                            'purchase' => 'Un produit de cet achat est introuvable.',
                        ]);
                    }

                    if ($product->stock < $quantity) {
                        throw ValidationException::withMessages([
                            'purchase' => 'Stock insuffisant pour annuler l\'achat '
                                .$purchase->purchase_number
                                .' : le produit '
                                .$product->name
                                .' n\'a que '
                                .$product->stock
                                .' unité(s) en stock.',
                        ]);
                    }
                }

                foreach ($quantities as $productId => $quantity) {
                    $products->get((int) $productId)->decrement('stock', $quantity);

                    StockMovement::create([
                        'product_id' => (int) $productId,
                        'type' => 'out',
                        'quantity' => $quantity,
                        'description' => 'Annulation de l\'achat '
                            .$purchase->purchase_number,
                        'source_type' => Purchase::class,
                        'source_id' => $purchase->id,
                    ]);
                }
            }

            $purchase->update(['status' => 'cancelled']);

            foreach ($productIds as $productId) {
                $this->syncPurchasePrice($productId);
            }

            return [true, 'Achat annulé avec succès.'];
        });

        return redirect()
            ->route('purchases.show', $purchase)
            ->with($result[0] ? 'success' : 'error', $result[1]);
    }

    /**
     * Supprimer un achat.
     */
    public function destroy(Purchase $purchase)
    {
        DB::transaction(function () use ($purchase) {

            // ==================================================
            // 1. VERROUILLER L'ACHAT
            // ==================================================

            $purchase = Purchase::whereKey($purchase->id)
                ->lockForUpdate()
                ->firstOrFail();

            $purchase->load('items');

            // ==================================================
            // 2. RÉCUPÉRER LES QUANTITÉS PAR PRODUIT
            // ==================================================

            $productQuantities = $purchase->items
                ->groupBy('product_id')
                ->map(
                    fn ($items) => $items->sum('quantity')
                );

            // ==================================================
            // 3. VERROUILLER LES PRODUITS
            // ==================================================

            $productIds = $productQuantities
                ->keys()
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

            // ==================================================
            // 4. VÉRIFIER LE STOCK AVANT DE MODIFIER
            // ==================================================

            if ($purchase->status === 'received') {

                foreach ($productQuantities as $productId => $quantity) {

                    $product = $products->get(
                        (int) $productId
                    );

                    if (! $product) {

                        throw ValidationException::withMessages([
                            'purchase' => 'Un produit de cet achat est introuvable.',
                        ]);
                    }

                    if ($product->stock < $quantity) {

                        throw ValidationException::withMessages([
                            'purchase' => "Impossible de supprimer l'achat "
                                .$purchase->purchase_number
                                .'. Le stock du produit '
                                .$product->name
                                .' est insuffisant pour annuler '
                                .'cet achat.',
                        ]);
                    }
                }

                // ==================================================
                // 5. RETIRER L'EFFET DE L'ACHAT
                // ==================================================

                foreach ($productQuantities as $productId => $quantity) {

                    $product = $products->get(
                        (int) $productId
                    );

                    $product->decrement(
                        'stock',
                        $quantity
                    );

                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => 'out',
                        'quantity' => $quantity,

                        'description' => 'Annulation de l\'achat '
                            .$purchase->purchase_number,

                        'source_type' => Purchase::class,
                        'source_id' => $purchase->id,
                    ]);
                }
            }

            // ==================================================
            // 6. SUPPRIMER LES LIGNES
            // ==================================================

            $purchase->items()->delete();

            // ==================================================
            // 7. SUPPRIMER L'ACHAT
            // ==================================================

            $purchase->delete();

            // ==================================================
            // 8. RECALCULER LE DERNIER PRIX D'ACHAT
            // ==================================================

            foreach ($productIds as $productId) {

                $this->syncPurchasePrice(
                    $productId
                );
            }
        });

        return redirect()
            ->route('purchases.index')
            ->with(
                'success',
                'Achat supprimé avec succès et stock mis à jour.'
            );
    }

    /**
     * Synchroniser le prix d'achat actuel du produit.
     *
     * Le prix d'achat est basé sur le dernier achat reçu
     * pour ce produit.
     */
    private function syncPurchasePrice(int $productId): void
    {
        $lastPurchasePrice = PurchaseItem::query()
            ->join(
                'purchases',
                'purchases.id',
                '=',
                'purchase_items.purchase_id'
            )
            ->where(
                'purchase_items.product_id',
                $productId
            )
            ->where(
                'purchases.status',
                'received'
            )
            ->orderByDesc('purchases.purchase_date')
            ->orderByDesc('purchases.id')
            ->value('purchase_items.unit_price');

        if ($lastPurchasePrice !== null) {

            Product::whereKey($productId)->update([
                'purchase_price' => $lastPurchasePrice,
            ]);

            return;
        }

        // Aucun achat reçu restant pour ce produit.
        Product::whereKey($productId)->update([
            'purchase_price' => 0,
        ]);
    }
}
