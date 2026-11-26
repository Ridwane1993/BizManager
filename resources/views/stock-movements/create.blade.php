@extends('layouts.app')

@section('title', 'Nouveau mouvement de stock')

@section('content')

<div class="container-fluid py-4">

    {{-- ==================================================
        EN-TÊTE
    ================================================== --}}
    <div class="mb-4">

        <h1 class="h3 mb-1">
            Nouveau mouvement de stock
        </h1>

        <p class="text-muted mb-0">
            Ajoutez manuellement une entrée, une sortie, une perte,
            un retour ou une correction.
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

            <strong>
                Veuillez corriger les erreurs suivantes :
            </strong>

            <ul class="mb-0 mt-2">

                @foreach ($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

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
    <div class="card border-0 shadow-sm">

        <div class="card-body p-4">

            <form
                method="POST"
                action="{{ route('stock-movements.store') }}"
            >

                @csrf

                {{-- Produit --}}
                <div class="mb-3">

                    <label
                        for="product_id"
                        class="form-label fw-semibold"
                    >
                        Produit
                    </label>

                    <select
                        name="product_id"
                        id="product_id"
                        class="form-select @error('product_id') is-invalid @enderror"
                        required
                    >

                        <option value="">
                            Sélectionner un produit
                        </option>

                        @foreach ($products as $product)

                            <option
                                value="{{ $product->id }}"
                                {{ old('product_id') == $product->id ? 'selected' : '' }}
                            >
                                {{ $product->name }}
                                — Stock actuel : {{ $product->stock }}
                            </option>

                        @endforeach

                    </select>

                    @error('product_id')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>

                {{-- Type --}}
                <div class="mb-3">

                    <label
                        for="type"
                        class="form-label fw-semibold"
                    >
                        Type de mouvement
                    </label>

                    <select
                        name="type"
                        id="type"
                        class="form-select @error('type') is-invalid @enderror"
                        required
                    >

                        <option value="">
                            Sélectionner un type
                        </option>

                        <option
                            value="in"
                            {{ old('type') === 'in' ? 'selected' : '' }}
                        >
                            Entrée
                        </option>

                        <option
                            value="out"
                            {{ old('type') === 'out' ? 'selected' : '' }}
                        >
                            Sortie
                        </option>

                        <option
                            value="return"
                            {{ old('type') === 'return' ? 'selected' : '' }}
                        >
                            Retour
                        </option>

                        <option
                            value="loss"
                            {{ old('type') === 'loss' ? 'selected' : '' }}
                        >
                            Perte
                        </option>

                        <option
                            value="adjustment"
                            {{ old('type') === 'adjustment' ? 'selected' : '' }}
                        >
                            Correction
                        </option>

                    </select>

                    @error('type')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>

                {{-- Quantité --}}
                <div class="mb-3">

                    <label
                        for="quantity"
                        class="form-label fw-semibold"
                    >
                        Quantité
                    </label>

                    <input
                        type="number"
                        name="quantity"
                        id="quantity"
                        class="form-control @error('quantity') is-invalid @enderror"
                        value="{{ old('quantity') }}"
                        step="1"
                        required
                    >

                    <div
                        id="quantityHelp"
                        class="form-text"
                    >
                        La quantité doit être positive pour une entrée,
                        sortie, perte ou retour.
                    </div>

                    @error('quantity')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>

                {{-- Description --}}
                <div class="mb-4">

                    <label
                        for="description"
                        class="form-label fw-semibold"
                    >
                        Description
                    </label>

                    <textarea
                        name="description"
                        id="description"
                        rows="4"
                        class="form-control @error('description') is-invalid @enderror"
                        placeholder="Exemple : Réception d'une nouvelle livraison..."
                    >{{ old('description') }}</textarea>

                    @error('description')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>

                {{-- Boutons --}}
                <div class="d-flex justify-content-end gap-2">

                    <a
                        href="{{ route('stock-movements.index') }}"
                        class="btn btn-outline-secondary"
                    >
                        Annuler
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        <i class="bi bi-check-lg me-1"></i>

                        Enregistrer le mouvement

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    const typeSelect =
        document.getElementById('type');

    const quantityInput =
        document.getElementById('quantity');

    const quantityHelp =
        document.getElementById('quantityHelp');

    function updateQuantityRules() {

        if (typeSelect.value === 'adjustment') {

            quantityInput.removeAttribute('min');

            quantityHelp.textContent =
                'Pour une correction, utilisez un nombre positif pour augmenter le stock ou négatif pour le diminuer.';

        } else {

            quantityInput.setAttribute(
                'min',
                '1'
            );

            quantityHelp.textContent =
                'La quantité doit être positive pour une entrée, sortie, perte ou retour.';

            if (
                quantityInput.value !== ''
                && parseInt(quantityInput.value) < 1
            ) {

                quantityInput.value = 1;
            }
        }
    }

    typeSelect.addEventListener(
        'change',
        updateQuantityRules
    );

    updateQuantityRules();

});
</script>

@endpush

@endsection
