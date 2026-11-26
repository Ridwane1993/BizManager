@extends('layouts.app')

@section('title', 'Modifier le produit')

@section('content')

<div class="container-fluid py-4">

    {{-- ==================================================
        EN-TÊTE
    ================================================== --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="h3 mb-1 fw-bold">
                Modifier le produit
            </h1>

            <p class="text-muted mb-0">
                Modifiez les informations de {{ $product->name }}.
            </p>

        </div>

        <a href="{{ route('products.index') }}"
            class="btn btn-outline-secondary">

            <i class="bi bi-arrow-left me-1"></i>

            Retour

        </a>

    </div>

    {{-- ==================================================
        ERREURS
    ================================================== --}}
    @if ($errors->any())

        <div class="alert alert-danger alert-dismissible fade show"
            role="alert">

            <div class="d-flex">

                <i class="bi bi-exclamation-triangle me-2 mt-1"></i>

                <div>

                    <strong>
                        Impossible d'enregistrer les modifications.
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

            <button type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Fermer">
            </button>

        </div>

    @endif

    {{-- ==================================================
        FORMULAIRE
    ================================================== --}}
    <div class="row justify-content-center">

        <div class="col-xl-9 col-lg-10">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white py-3">

                    <h5 class="mb-1">
                        <i class="bi bi-box-seam me-2"></i>
                        Informations du produit
                    </h5>

                    <p class="text-muted mb-0">
                        Modifiez les informations puis enregistrez.
                    </p>

                </div>

                <div class="card-body p-4">

                    <form action="{{ route('products.update', $product) }}"
                        method="POST">

                        @csrf

                        @method('PUT')

                        <div class="row g-4">

                            {{-- Nom --}}
                            <div class="col-md-6">

                                <label for="name"
                                    class="form-label fw-semibold">

                                    Nom du produit
                                    <span class="text-danger">*</span>

                                </label>

                                <input type="text"
                                    id="name"
                                    name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $product->name) }}"
                                    required>

                                @error('name')

                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>

                            {{-- Référence --}}
                            <div class="col-md-6">

                                <label for="reference"
                                    class="form-label fw-semibold">

                                    Référence
                                    <span class="text-danger">*</span>

                                </label>

                                <input type="text"
                                    id="reference"
                                    name="reference"
                                    class="form-control @error('reference') is-invalid @enderror"
                                    value="{{ old('reference', $product->reference) }}"
                                    required>

                                @error('reference')

                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>

                            {{-- Catégorie --}}
                            <div class="col-md-6">

                                <label for="category_id"
                                    class="form-label fw-semibold">

                                    Catégorie
                                    <span class="text-danger">*</span>

                                </label>

                                <select id="category_id"
                                    name="category_id"
                                    class="form-select @error('category_id') is-invalid @enderror"
                                    required>

                                    <option value="">
                                        Sélectionner une catégorie
                                    </option>

                                    @foreach ($categories as $category)

                                        <option value="{{ $category->id }}"
                                            {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>

                                            {{ $category->name }}

                                        </option>

                                    @endforeach

                                </select>

                                @error('category_id')

                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>

                            {{-- Stock --}}
                            <div class="col-md-6">

                                <label for="stock"
                                    class="form-label fw-semibold">

                                    Stock
                                    <span class="text-danger">*</span>

                                </label>

                                <input type="number"
                                    id="stock"
                                    name="stock"
                                    class="form-control @error('stock') is-invalid @enderror"
                                    value="{{ old('stock', $product->stock) }}"
                                    min="0"
                                    required>

                                @error('stock')

                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>

                                @enderror

                                <div class="form-text">
                                    Toute modification du stock devrait être enregistrée dans l'historique des mouvements.
                                </div>

                            </div>

                            {{-- Prix d'achat --}}
                            <div class="col-md-6">

                                <label for="purchase_price"
                                    class="form-label fw-semibold">

                                    Prix d'achat
                                    <span class="text-danger">*</span>

                                </label>

                                <div class="input-group">

                                    <input type="number"
                                        id="purchase_price"
                                        name="purchase_price"
                                        class="form-control @error('purchase_price') is-invalid @enderror"
                                        value="{{ old('purchase_price', $product->purchase_price) }}"
                                        min="0"
                                        step="0.01"
                                        required>

                                    <span class="input-group-text">
                                        FCFA
                                    </span>

                                </div>

                                @error('purchase_price')

                                    <div class="text-danger small mt-1">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>

                            {{-- Prix de vente --}}
                            <div class="col-md-6">

                                <label for="selling_price"
                                    class="form-label fw-semibold">

                                    Prix de vente
                                    <span class="text-danger">*</span>

                                </label>

                                <div class="input-group">

                                    <input type="number"
                                        id="selling_price"
                                        name="selling_price"
                                        class="form-control @error('selling_price') is-invalid @enderror"
                                        value="{{ old('selling_price', $product->selling_price) }}"
                                        min="0"
                                        step="0.01"
                                        required>

                                    <span class="input-group-text">
                                        FCFA
                                    </span>

                                </div>

                                @error('selling_price')

                                    <div class="text-danger small mt-1">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>

                            {{-- Seuil --}}
                            <div class="col-md-6">

                                <label for="stock_alert_threshold"
                                    class="form-label fw-semibold">

                                    Seuil d'alerte du stock
                                    <span class="text-danger">*</span>

                                </label>

                                <input type="number"
                                    id="stock_alert_threshold"
                                    name="stock_alert_threshold"
                                    class="form-control @error('stock_alert_threshold') is-invalid @enderror"
                                    value="{{ old('stock_alert_threshold', $product->stock_alert_threshold) }}"
                                    min="0"
                                    required>

                                <div class="form-text">
                                    Une alerte sera affichée lorsque le stock sera inférieur ou égal à ce seuil.
                                </div>

                                @error('stock_alert_threshold')

                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>

                            {{-- Description --}}
                            <div class="col-12">

                                <label for="description"
                                    class="form-label fw-semibold">

                                    Description

                                </label>

                                <textarea id="description"
                                    name="description"
                                    rows="5"
                                    class="form-control @error('description') is-invalid @enderror"
                                    placeholder="Description du produit...">{{ old('description', $product->description) }}</textarea>

                                @error('description')

                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>

                        </div>

                        {{-- Boutons --}}
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-4 border-top">

                            <a href="{{ route('products.index') }}"
                                class="btn btn-outline-secondary">

                                <i class="bi bi-x-lg me-1"></i>

                                Annuler

                            </a>

                            <button type="submit"
                                class="btn btn-primary">

                                <i class="bi bi-check-lg me-1"></i>

                                Enregistrer les modifications

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection
