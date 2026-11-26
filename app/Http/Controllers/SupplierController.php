<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    /**
     * Afficher la liste des fournisseurs.
     */
    public function index(Request $request)
    {
        $query = Supplier::query()
            ->withCount('purchases')
            ->latest();

        // ==================================================
        // RECHERCHE
        // ==================================================

        if ($request->filled('search')) {
            $search = trim($request->search);

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }
        }

        // ==================================================
        // PAGINATION
        // ==================================================

        $suppliers = $query
            ->paginate(15)
            ->withQueryString();

        return view(
            'suppliers.index',
            compact('suppliers')
        );
    }

    /**
     * Afficher le formulaire de création.
     */
    public function create()
    {
        return view('suppliers.create');
    }

    /**
     * Enregistrer un fournisseur.
     */
    public function store(SaveSupplierRequest $request)
    {
        $validated = $request->validated();

        Supplier::create($validated);

        return redirect()
            ->route('suppliers.index')
            ->with(
                'success',
                'Fournisseur créé avec succès.'
            );
    }

    /**
     * Afficher les détails d'un fournisseur.
     */
    public function show(Supplier $supplier)
    {
        $supplier->loadCount('purchases');

        // ==================================================
        // HISTORIQUE DES ACHATS
        // ==================================================

        $purchases = $supplier->purchases()
            ->latest('purchase_date')
            ->paginate(10);

        return view(
            'suppliers.show',
            compact(
                'supplier',
                'purchases'
            )
        );
    }

    /**
     * Afficher le formulaire de modification.
     */
    public function edit(Supplier $supplier)
    {
        return view(
            'suppliers.edit',
            compact('supplier')
        );
    }

    /**
     * Mettre à jour un fournisseur.
     */
    public function update(
        SaveSupplierRequest $request,
        Supplier $supplier
    ) {
        $validated = $request->validated();

        $supplier->update($validated);

        return redirect()
            ->route('suppliers.index')
            ->with(
                'success',
                'Fournisseur modifié avec succès.'
            );
    }

    /**
     * Supprimer un fournisseur.
     */
    public function destroy(Supplier $supplier)
    {
        return DB::transaction(function () use ($supplier) {

            // ==================================================
            // VERROUILLER LE FOURNISSEUR
            // ==================================================

            $supplier = Supplier::whereKey($supplier->id)
                ->lockForUpdate()
                ->firstOrFail();

            // ==================================================
            // EMPÊCHER LA SUPPRESSION S'IL EXISTE DES ACHATS
            // ==================================================

            if ($supplier->purchases()->exists()) {
                return redirect()
                    ->route('suppliers.index')
                    ->with(
                        'error',
                        'Impossible de supprimer ce fournisseur car il possède des achats.'
                    );
            }

            // ==================================================
            // SUPPRIMER LE FOURNISSEUR
            // ==================================================

            $supplier->delete();

            return redirect()
                ->route('suppliers.index')
                ->with(
                    'success',
                    'Fournisseur supprimé avec succès.'
                );
        });
    }
}
