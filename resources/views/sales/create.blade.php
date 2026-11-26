@extends('layouts.app')

@section('title', 'Nouvelle vente')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | Récupérer les anciennes lignes après une erreur de validation
    |--------------------------------------------------------------------------
    */
    $oldProducts = old('products', [
        [
            'product_id' => '',
            'quantity' => 1,
        ]
    ]);

    $productRowsData = array_values($oldProducts);
@endphp

<div class="container-fluid py-4">

    {{-- ==================================================
        EN-TÊTE
    ================================================== --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="h3 mb-1">
                Nouvelle vente
            </h1>

            <p class="text-muted mb-0">
                Enregistrez une nouvelle vente.
            </p>

        </div>

        <a
            href="{{ route('sales.index') }}"
            class="btn btn-outline-secondary"
        >
            <i class="bi bi-arrow-left me-1"></i>
            Retour
        </a>

    </div>

    {{-- ==================================================
        ERREURS DE VALIDATION
    ================================================== --}}
    <x-validation-errors message="Veuillez corriger les erreurs suivantes :" />

    {{-- ==================================================
        FORMULAIRE
    ================================================== --}}
    <form
        action="{{ route('sales.store') }}"
        method="POST"
    >

        @csrf

        {{-- ==================================================
            INFORMATIONS GÉNÉRALES
        ================================================== --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">

                <h5 class="mb-0">

                    <i class="bi bi-receipt me-2"></i>

                    Informations de la vente

                </h5>

            </div>

            <div class="card-body">

                <div class="row g-3">

                    {{-- Client --}}
                    <div class="col-md-6">

                        <label
                            for="client_id"
                            class="form-label fw-semibold"
                        >
                            Client <span class="text-danger">*</span>
                        </label>

                        <select
                            name="client_id"
                            id="client_id"
                            class="form-select @error('client_id') is-invalid @enderror"
                            required
                        >

                            <option value="">
                                Sélectionner un client
                            </option>

                            @foreach ($clients as $client)

                                <option
                                    value="{{ $client->id }}"
                                    {{ old('client_id') == $client->id ? 'selected' : '' }}
                                >
                                    {{ $client->name }}
                                </option>

                            @endforeach

                        </select>

                        @error('client_id')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>

                    {{-- Date --}}
                    <div class="col-md-6">

                        <label
                            for="sale_date"
                            class="form-label fw-semibold"
                        >
                            Date de vente <span class="text-danger">*</span>
                        </label>

                        <input
                            type="date"
                            name="sale_date"
                            id="sale_date"
                            class="form-control @error('sale_date') is-invalid @enderror"
                            value="{{ old('sale_date', now()->format('Y-m-d')) }}"
                            required
                        >

                        @error('sale_date')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>

                    {{-- Statut --}}
                    @if (auth()->user()->isAdmin() || auth()->user()->isManager())

                        <div class="col-md-6">

                            <label
                                for="status"
                                class="form-label fw-semibold"
                            >
                                Statut
                            </label>

                            <select
                                name="status"
                                id="status"
                                class="form-select @error('status') is-invalid @enderror"
                            >
                                <option value="completed" @selected(old('status', 'completed') === 'completed')>
                                    Vente immédiate (stock décrémenté)
                                </option>
                                <option value="pending" @selected(old('status') === 'pending')>
                                    Brouillon / devis (stock inchangé)
                                </option>
                            </select>

                            @error('status')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>

                    @endif

                    {{-- Notes --}}
                    <div class="col-12">

                        <label
                            for="notes"
                            class="form-label fw-semibold"
                        >
                            Notes
                        </label>

                        <textarea
                            name="notes"
                            id="notes"
                            rows="3"
                            class="form-control @error('notes') is-invalid @enderror"
                            placeholder="Remarque concernant cette vente..."
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
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">

                <h5 class="mb-0">

                    <i class="bi bi-box-seam me-2"></i>

                    Produits

                </h5>

            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table
                        class="table align-middle"
                        id="productsTable"
                    >

                        <thead class="table-light">

                            <tr>

                                <th style="min-width: 250px;">
                                    Produit
                                </th>

                                <th>
                                    Stock disponible
                                </th>

                                <th>
                                    Quantité
                                </th>

                                <th>
                                    Prix unitaire
                                </th>

                                <th>
                                    Sous-total
                                </th>

                                <th></th>

                            </tr>

                        </thead>

                        <tbody id="productRows">

                            @foreach ($productRowsData as $index => $row)

                                <tr class="product-row">

                                    {{-- Produit --}}
                                    <td>

                                        <select
                                            name="products[{{ $index }}][product_id]"
                                            class="form-select @error("products.$index.product_id") is-invalid @enderror product-select"
                                            required
                                        >

                                            <option value="">
                                                Sélectionner un produit
                                            </option>

                                            @foreach ($products as $product)

                                                <option
                                                    value="{{ $product->id }}"
                                                    data-price="{{ $product->selling_price }}"
                                                    data-stock="{{ $product->stock }}"
                                                    {{ ($row['product_id'] ?? '') == $product->id ? 'selected' : '' }}
                                                >
                                                    {{ $product->name }}
                                                    — {{ $product->selling_price }} FCFA
                                                </option>

                                            @endforeach

                                        </select>

                                        @error("products.$index.product_id")

                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>

                                        @enderror

                                    </td>

                                    {{-- Stock --}}
                                    <td>

                                        <span class="stock-display text-muted">
                                            —
                                        </span>

                                    </td>

                                    {{-- Quantité --}}
                                    <td style="width: 130px;">

                                        <input
                                            type="number"
                                            name="products[{{ $index }}][quantity]"
                                            class="form-control @error("products.$index.quantity") is-invalid @enderror quantity-input"
                                            min="1"
                                            value="{{ $row['quantity'] ?? 1 }}"
                                            required
                                        >

                                        @error("products.$index.quantity")

                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>

                                        @enderror

                                    </td>

                                    {{-- Prix unitaire --}}
                                    <td style="width: 170px;">

                                        <input
                                            type="text"
                                            class="form-control unit-price"
                                            value="0"
                                            readonly
                                        >

                                    </td>

                                    {{-- Sous-total --}}
                                    <td style="width: 170px;">

                                        <input
                                            type="text"
                                            class="form-control subtotal"
                                            value="0"
                                            data-raw-value="0"
                                            readonly
                                        >

                                    </td>

                                    {{-- Supprimer --}}
                                    <td style="width: 50px;">

                                        <button
                                            type="button"
                                            class="btn btn-outline-danger remove-row"
                                            title="Supprimer"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

                {{-- Ajouter un produit --}}
                <button
                    type="button"
                    class="btn btn-outline-primary"
                    id="addProduct"
                >

                    <i class="bi bi-plus-lg me-1"></i>

                    Ajouter un produit

                </button>

            </div>

        </div>

        {{-- ==================================================
            TOTAL
        ================================================== --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-body">

                <div class="row justify-content-end">

                    <div class="col-md-4">

                        <div class="d-flex justify-content-between align-items-center">

                            <span class="fs-5 fw-semibold">
                                Total :
                            </span>

                            <span class="fs-4 fw-bold text-primary">

                                <span id="totalAmount">
                                    0
                                </span>

                                FCFA

                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- ==================================================
            BOUTONS
        ================================================== --}}
        <div class="d-flex justify-content-end gap-2">

            <a
                href="{{ route('sales.index') }}"
                class="btn btn-outline-secondary"
            >
                Annuler
            </a>

            <button
                type="submit"
                class="btn btn-primary"
            >

                <i class="bi bi-check-lg me-1"></i>

                Enregistrer la vente

            </button>

        </div>

    </form>

</div>

{{-- ==================================================
    JAVASCRIPT
================================================== --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    let rowIndex = {{ count($productRowsData) }};

    const productRows =
        document.getElementById('productRows');

    const addProductButton =
        document.getElementById('addProduct');

    const totalAmount =
        document.getElementById('totalAmount');

    // ==================================================
    // FORMATTER LES NOMBRES
    // ==================================================
    function formatNumber(number) {

        return new Intl.NumberFormat('fr-FR').format(number);

    }

    // ==================================================
    // CALCULER LE TOTAL
    // ==================================================
    function calculateTotal() {

        let total = 0;

        document
            .querySelectorAll('.subtotal')
            .forEach(function (input) {

                total +=
                    parseFloat(input.dataset.rawValue) || 0;

            });

        totalAmount.textContent =
            formatNumber(total);

    }

    // ==================================================
    // METTRE À JOUR UNE LIGNE
    // ==================================================
    function updateRow(row) {

        const select =
            row.querySelector('.product-select');

        const quantityInput =
            row.querySelector('.quantity-input');

        const stockDisplay =
            row.querySelector('.stock-display');

        const unitPrice =
            row.querySelector('.unit-price');

        const subtotal =
            row.querySelector('.subtotal');

        const selectedOption =
            select.options[select.selectedIndex];

        if (!selectedOption || !selectedOption.value) {

            stockDisplay.textContent = '—';

            unitPrice.value = '0';

            subtotal.dataset.rawValue = 0;
            subtotal.value = '0';

            calculateTotal();

            return;

        }

        const price =
            parseFloat(selectedOption.dataset.price) || 0;

        const stock =
            parseInt(selectedOption.dataset.stock) || 0;

        const quantity =
            parseInt(quantityInput.value) || 0;

        stockDisplay.textContent =
            stock;

        unitPrice.value =
            formatNumber(price);

        const subtotalValue =
            price * quantity;

        subtotal.dataset.rawValue =
            subtotalValue;

        subtotal.value =
            formatNumber(subtotalValue);

        calculateTotal();

    }

    // ==================================================
    // CHANGEMENT DE PRODUIT
    // ==================================================
    productRows.addEventListener('change', function (event) {

        if (
            event.target.classList.contains('product-select')
        ) {

            const row =
                event.target.closest('.product-row');

            updateRow(row);

        }

    });

    // ==================================================
    // CHANGEMENT DE QUANTITÉ
    // ==================================================
    productRows.addEventListener('input', function (event) {

        if (
            event.target.classList.contains('quantity-input')
        ) {

            const row =
                event.target.closest('.product-row');

            updateRow(row);

        }

    });

    // ==================================================
    // AJOUTER UNE LIGNE
    // ==================================================
    addProductButton.addEventListener('click', function () {

        const firstRow =
            document.querySelector('.product-row');

        const newRow =
            firstRow.cloneNode(true);

        const select =
            newRow.querySelector('.product-select');

        const quantity =
            newRow.querySelector('.quantity-input');

        const stock =
            newRow.querySelector('.stock-display');

        const unitPrice =
            newRow.querySelector('.unit-price');

        const subtotal =
            newRow.querySelector('.subtotal');

        select.name =
            `products[${rowIndex}][product_id]`;

        quantity.name =
            `products[${rowIndex}][quantity]`;

        select.value =
            '';

        quantity.value =
            1;

        stock.textContent =
            '—';

        unitPrice.value =
            '0';

        subtotal.dataset.rawValue =
            0;

        subtotal.value =
            '0';

        // Retirer les éventuels états visuels d'erreur
        select.classList.remove('is-invalid');

        quantity.classList.remove('is-invalid');

        newRow
            .querySelectorAll('.invalid-feedback')
            .forEach(function (feedback) {
                feedback.remove();
            });

        productRows.appendChild(newRow);

        rowIndex++;

        calculateTotal();

    });

    // ==================================================
    // SUPPRIMER UNE LIGNE
    // ==================================================
    productRows.addEventListener('click', function (event) {

        const button =
            event.target.closest('.remove-row');

        if (!button) {
            return;
        }

        const rows =
            document.querySelectorAll('.product-row');

        if (rows.length === 1) {

            alert(
                'Une vente doit contenir au moins un produit.'
            );

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
    document
        .querySelectorAll('.product-row')
        .forEach(function (row) {

            updateRow(row);

        });

    calculateTotal();

});
</script>

@endsection
