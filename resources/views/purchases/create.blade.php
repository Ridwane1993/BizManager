@extends('layouts.app')

@section('title', 'Nouvel achat')

@section('content')

@php
$oldProducts = old('products', [
[
'product_id' => '',
'quantity' => 1,
'unit_price' => '',
],
]);

$productRowsData = array_values($oldProducts);

@endphp

<div class="container-fluid py-4">

{{-- ==================================================
    EN-TÊTE
================================================== --}}

<div class="mb-4">

    <h1 class="h3 mb-1">
        Nouvel achat
    </h1>

    <p class="text-muted mb-0">
        Enregistrez un achat et ajoutez automatiquement les produits au stock.
    </p>

</div>

{{-- ==================================================
    ERREURS
================================================== --}}

@if ($errors->any())

    <div
        class="alert alert-danger alert-dismissible fade show"
        role="alert"
    >

        <div class="d-flex">

            <i class="bi bi-exclamation-triangle me-2 mt-1"></i>

            <div>

                <strong>
                    Certaines informations sont incorrectes.
                </strong>

                <ul class="mb-0 mt-2">

                    @foreach ($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        </div>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
            aria-label="Fermer"
        ></button>

    </div>

@endif

{{-- ==================================================
    FORMULAIRE
================================================== --}}

<form
    method="POST"
    action="{{ route('purchases.store') }}"
>

    @csrf

    {{-- ==================================================
        INFORMATIONS GÉNÉRALES
    ================================================== --}}

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white">

            <h5 class="mb-0">
                Informations de l'achat
            </h5>

        </div>

        <div class="card-body">

            <div class="row g-3">

                {{-- Fournisseur --}}
                <div class="col-md-6">

                    <label
                        for="supplier_id"
                        class="form-label"
                    >
                        Fournisseur *
                    </label>

                    <select
                        name="supplier_id"
                        id="supplier_id"
                        class="form-select @error('supplier_id') is-invalid @enderror"
                        required
                    >

                        <option value="">
                            Sélectionner un fournisseur
                        </option>

                        @foreach ($suppliers as $supplier)

                            <option
                                value="{{ $supplier->id }}"
                                {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}
                            >
                                {{ $supplier->name }}
                            </option>

                        @endforeach

                    </select>

                    @error('supplier_id')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>

                {{-- Date --}}
                <div class="col-md-6">

                    <label
                        for="purchase_date"
                        class="form-label"
                    >
                        Date d'achat *
                    </label>

                    <input
                        type="date"
                        name="purchase_date"
                        id="purchase_date"
                        class="form-control @error('purchase_date') is-invalid @enderror"
                        value="{{ old('purchase_date', now()->format('Y-m-d')) }}"
                        required
                    >

                    @error('purchase_date')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>

                {{-- Notes --}}
                <div class="col-12">

                    <label
                        for="notes"
                        class="form-label"
                    >
                        Notes
                    </label>

                    <textarea
                        name="notes"
                        id="notes"
                        rows="3"
                        class="form-control @error('notes') is-invalid @enderror"
                        placeholder="Informations complémentaires..."
                    >{{ old('notes') }}</textarea>

                    @error('notes')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>

            </div>

        </div>

    </div>

    {{-- ==================================================
        PRODUITS
    ================================================== --}}

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white d-flex justify-content-between align-items-center">

            <h5 class="mb-0">
                Produits achetés
            </h5>

            <button
                type="button"
                id="addProduct"
                class="btn btn-sm btn-primary"
            >

                <i class="bi bi-plus-lg me-1"></i>

                Ajouter un produit

            </button>

        </div>

        <div class="card-body">

            <div id="productRows">

                @foreach ($productRowsData as $index => $row)

                    <div class="product-row border rounded p-3 mb-3">

                        <div class="row g-3 align-items-end">

                            {{-- Produit --}}
                            <div class="col-md-5">

                                <label class="form-label">
                                    Produit
                                </label>

                                <select
                                    name="products[{{ $index }}][product_id]"
                                    class="form-select product-select @error("products.$index.product_id") is-invalid @enderror"
                                    required
                                >

                                    <option value="">
                                        Sélectionner un produit
                                    </option>

                                    @foreach ($products as $product)

                                        <option
                                            value="{{ $product->id }}"
                                            data-purchase-price="{{ $product->purchase_price }}"
                                            {{ ($row['product_id'] ?? '') == $product->id ? 'selected' : '' }}
                                        >

                                            {{ $product->name }}
                                            — {{ $product->reference }}

                                        </option>

                                    @endforeach

                                </select>

                                @error("products.$index.product_id")

                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>

                            {{-- Quantité --}}
                            <div class="col-md-2">

                                <label class="form-label">
                                    Quantité
                                </label>

                                <input
                                    type="number"
                                    name="products[{{ $index }}][quantity]"
                                    class="form-control quantity-input @error("products.$index.quantity") is-invalid @enderror"
                                    min="1"
                                    value="{{ $row['quantity'] ?? 1 }}"
                                    required
                                >

                                @error("products.$index.quantity")

                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>

                            {{-- Prix --}}
                            <div class="col-md-3">

                                <label class="form-label">
                                    Prix d'achat
                                </label>

                                <input
                                    type="number"
                                    name="products[{{ $index }}][unit_price]"
                                    class="form-control price-input @error("products.$index.unit_price") is-invalid @enderror"
                                    min="0"
                                    step="0.01"
                                    value="{{ $row['unit_price'] ?? '' }}"
                                    required
                                >

                                @error("products.$index.unit_price")

                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>

                            {{-- Sous-total --}}
                            <div class="col-md-2">

                                <div class="d-flex gap-2">

                                    <input
                                        type="text"
                                        class="form-control subtotal"
                                        value="0 FCFA"
                                        readonly
                                    >

                                    @if ($index > 0)

                                        <button
                                            type="button"
                                            class="btn btn-outline-danger remove-product"
                                            title="Supprimer"
                                        >

                                            <i class="bi bi-trash"></i>

                                        </button>

                                    @endif

                                </div>

                            </div>

                        </div>

                    </div>

                @endforeach

            </div>

            {{-- ==================================================
                TOTAL
            ================================================== --}}

            <div class="d-flex justify-content-end mt-4">

                <div class="text-end">

                    <small class="text-muted d-block">
                        Total de l'achat
                    </small>

                    <h3
                        id="totalAmount"
                        class="mb-0"
                    >
                        0 FCFA
                    </h3>

                </div>

            </div>

        </div>

        {{-- ==================================================
            ACTIONS
        ================================================== --}}

        <div class="card-footer bg-white d-flex justify-content-end gap-2">

            <a
                href="{{ route('purchases.index') }}"
                class="btn btn-outline-secondary"
            >

                <i class="bi bi-arrow-left me-1"></i>

                Annuler

            </a>

            <button
                type="submit"
                class="btn btn-success"
            >

                <i class="bi bi-check-lg me-1"></i>

                Enregistrer l'achat

            </button>

        </div>

    </div>

</form>
</div>

{{-- ==================================================
JAVASCRIPT
================================================== --}}

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Important :
    | On commence après le nombre réel de lignes déjà présentes.
    | Cela évite les doublons d'index après une erreur de validation.
    |--------------------------------------------------------------------------
    */
    let productIndex = {{ count($productRowsData) }};

    const productRows =
        document.getElementById('productRows');

    const addProductButton =
        document.getElementById('addProduct');

    const totalAmount =
        document.getElementById('totalAmount');

    // ==================================================
    // FORMAT FCFA
    // ==================================================

    function formatFCFA(value) {
        return Number(value).toLocaleString('fr-FR') + ' FCFA';
    }

    // ==================================================
    // CALCULER LE TOTAL
    // ==================================================

    function calculateTotal() {

        let total = 0;

        document
            .querySelectorAll('.product-row')
            .forEach(function (row) {

                const quantityInput =
                    row.querySelector('.quantity-input');

                const priceInput =
                    row.querySelector('.price-input');

                const subtotalInput =
                    row.querySelector('.subtotal');

                if (
                    !quantityInput ||
                    !priceInput ||
                    !subtotalInput
                ) {
                    return;
                }

                const quantity =
                    parseFloat(quantityInput.value) || 0;

                const price =
                    parseFloat(priceInput.value) || 0;

                const subtotal =
                    quantity * price;

                subtotalInput.value =
                    formatFCFA(subtotal);

                total += subtotal;

            });

        totalAmount.textContent =
            formatFCFA(total);
    }

    // ==================================================
    // CHANGEMENT DE PRODUIT
    // ==================================================

    productRows.addEventListener('change', function (event) {

        if (
            !event.target.classList.contains('product-select')
        ) {
            return;
        }

        const row =
            event.target.closest('.product-row');

        const selectedOption =
            event.target.options[
                event.target.selectedIndex
            ];

        const purchasePrice =
            selectedOption.dataset.purchasePrice;

        if (
            purchasePrice !== undefined &&
            row
        ) {

            const priceInput =
                row.querySelector('.price-input');

            if (priceInput) {
                priceInput.value = purchasePrice;
            }
        }

        calculateTotal();
    });

    // ==================================================
    // QUANTITÉ / PRIX
    // ==================================================

    productRows.addEventListener('input', function (event) {

        if (
            event.target.classList.contains('quantity-input') ||
            event.target.classList.contains('price-input')
        ) {
            calculateTotal();
        }
    });

    // ==================================================
    // AJOUTER UN PRODUIT
    // ==================================================

    addProductButton.addEventListener('click', function () {

        const row =
            document.createElement('div');

        row.className =
            'product-row border rounded p-3 mb-3';

        row.innerHTML = `

            <div class="row g-3 align-items-end">

                <div class="col-md-5">

                    <label class="form-label">
                        Produit
                    </label>

                    <select
                        name="products[${productIndex}][product_id]"
                        class="form-select product-select"
                        required
                    >

                        <option value="">
                            Sélectionner un produit
                        </option>

                        @foreach ($products as $product)

                            <option
                                value="{{ $product->id }}"
                                data-purchase-price="{{ $product->purchase_price }}"
                            >

                                {{ $product->name }}
                                — {{ $product->reference }}

                            </option>

                        @endforeach

                    </select>

                </div>

                <div class="col-md-2">

                    <label class="form-label">
                        Quantité
                    </label>

                    <input
                        type="number"
                        name="products[${productIndex}][quantity]"
                        class="form-control quantity-input"
                        min="1"
                        value="1"
                        required
                    >

                </div>

                <div class="col-md-3">

                    <label class="form-label">
                        Prix d'achat
                    </label>

                    <input
                        type="number"
                        name="products[${productIndex}][unit_price]"
                        class="form-control price-input"
                        min="0"
                        step="0.01"
                        required
                    >

                </div>

                <div class="col-md-2">

                    <div class="d-flex gap-2">

                        <input
                            type="text"
                            class="form-control subtotal"
                            value="0 FCFA"
                            readonly
                        >

                        <button
                            type="button"
                            class="btn btn-outline-danger remove-product"
                            title="Supprimer"
                        >

                            <i class="bi bi-trash"></i>

                        </button>

                    </div>

                </div>

            </div>
        `;

        productRows.appendChild(row);

        productIndex++;

    });

    // ==================================================
    // SUPPRIMER UNE LIGNE
    // ==================================================

    productRows.addEventListener('click', function (event) {

        const button =
            event.target.closest('.remove-product');

        if (!button) {
            return;
        }

        const rows =
            document.querySelectorAll('.product-row');

        /*
        |--------------------------------------------------------------------------
        | Le contrôleur exige au moins un produit.
        |--------------------------------------------------------------------------
        */
        if (rows.length <= 1) {
            return;
        }

        button
            .closest('.product-row')
            .remove();

        calculateTotal();
    });

    // ==================================================
    // CALCUL INITIAL
    // ==================================================

    calculateTotal();

});
</script>

@endsection
