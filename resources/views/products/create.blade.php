@extends('layouts.app')

@section('title', 'Nouveau produit')

@section('content')

<div class="container-fluid py-4">

    {{-- ==================================================
        EN-TÊTE
    ================================================== --}}
    <div class="mb-4">

        <h1 class="h3 fw-bold mb-1">
            Nouveau produit
        </h1>

        <p class="text-muted mb-0">
            Ajoutez un nouveau produit à votre catalogue.
        </p>

    </div>

    {{-- ==================================================
        ERREURS
    ================================================== --}}
    <x-validation-errors message="Certaines informations sont incorrectes." />

    {{-- ==================================================
        FORMULAIRE
    ================================================== --}}
    <div class="row justify-content-center">

        <div class="col-xl-9 col-lg-10">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white py-3">

                    <h5 class="mb-1">
                        Informations du produit
                    </h5>

                    <p class="text-muted mb-0">
                        Renseignez les informations du nouveau produit.
                    </p>

                </div>

                <div class="card-body p-4">

                    <form action="{{ route('products.store') }}"
                        method="POST">

                        @csrf

                        <div class="row g-4">

                            {{-- Nom --}}
                            <div class="col-md-6">

                                <label for="name"
                                    class="form-label fw-semibold">

                                    Nom du produit
                                    <span class="text-danger">*</span>

                                </label>

                                <input type="text"
                                    name="name"
                                    id="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}"
                                    placeholder="Ex : HP ProBook"
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
                                    name="reference"
                                    id="reference"
                                    class="form-control @error('reference') is-invalid @enderror"
                                    value="{{ old('reference') }}"
                                    placeholder="Ex : HP-PB-001"
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

                                <select name="category_id"
                                    id="category_id"
                                    class="form-select @error('category_id') is-invalid @enderror"
                                    required>

                                    <option value="">
                                        Sélectionnez une catégorie
                                    </option>

                                    @foreach ($categories as $category)

                                        <option value="{{ $category->id }}"
                                            {{ old('category_id') == $category->id ? 'selected' : '' }}>

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

                                    Stock initial
                                    <span class="text-danger">*</span>

                                </label>

                                <input type="number"
                                    name="stock"
                                    id="stock"
                                    class="form-control @error('stock') is-invalid @enderror"
                                    value="{{ old('stock', 0) }}"
                                    min="0"
                                    required>

                                @error('stock')

                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>

                                @enderror

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
                                        name="purchase_price"
                                        id="purchase_price"
                                        class="form-control @error('purchase_price') is-invalid @enderror"
                                        value="{{ old('purchase_price') }}"
                                        min="0"
                                        step="0.01"
                                        placeholder="250000"
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
                                        name="selling_price"
                                        id="selling_price"
                                        class="form-control @error('selling_price') is-invalid @enderror"
                                        value="{{ old('selling_price') }}"
                                        min="0"
                                        step="0.01"
                                        placeholder="325000"
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
                                    name="stock_alert_threshold"
                                    id="stock_alert_threshold"
                                    class="form-control @error('stock_alert_threshold') is-invalid @enderror"
                                    value="{{ old('stock_alert_threshold', 5) }}"
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

                                <textarea name="description"
                                    id="description"
                                    rows="5"
                                    class="form-control @error('description') is-invalid @enderror"
                                    placeholder="Description du produit...">{{ old('description') }}</textarea>

                                @error('description')

                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>

                        </div>

                        {{-- ==================================================
                            BOUTONS
                        ================================================== --}}
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-4 border-top">

                            <a href="{{ route('products.index') }}"
                                class="btn btn-outline-secondary">

                                <i class="bi bi-x-lg me-1"></i>

                                Annuler

                            </a>

                            <button type="submit"
                                class="btn btn-primary">

                                <i class="bi bi-check-lg me-1"></i>

                                Enregistrer le produit

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection
