@extends('layouts.app')

@section('title', 'Modifier la catégorie')

@section('content')

<div class="container-fluid py-4">

    {{-- ==================================================
        EN-TÊTE
    ================================================== --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="h3 fw-bold mb-1">
                Modifier la catégorie
            </h1>

            <p class="text-muted mb-0">
                Modifiez les informations de la catégorie.
            </p>

        </div>

        <a href="{{ route('categories.index') }}"
            class="btn btn-outline-secondary">

            <i class="bi bi-arrow-left me-1"></i>

            Retour aux catégories

        </a>

    </div>

    {{-- ==================================================
        ERREURS
    ================================================== --}}
    <x-validation-errors message="Impossible d'enregistrer les modifications." />

    {{-- ==================================================
        FORMULAIRE
    ================================================== --}}
    <div class="row justify-content-center">

        <div class="col-xl-8 col-lg-9">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white">

                    <h5 class="mb-1">

                        <i class="bi bi-pencil-square me-2"></i>

                        Informations de la catégorie

                    </h5>

                    <p class="text-muted mb-0">

                        Modifiez les informations puis enregistrez.

                    </p>

                </div>

                <div class="card-body p-4">

                    <form action="{{ route('categories.update', $category) }}"
                        method="POST">

                        @csrf

                        @method('PUT')

                        {{-- ==================================================
                            NOM
                        ================================================== --}}
                        <div class="mb-4">

                            <label for="name"
                                class="form-label fw-semibold">

                                Nom de la catégorie

                                <span class="text-danger">
                                    *
                                </span>

                            </label>

                            <input type="text"
                                id="name"
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $category->name) }}"
                                placeholder="Ex : Informatique"
                                required>

                            @error('name')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>

                        {{-- ==================================================
                            DESCRIPTION
                        ================================================== --}}
                        <div class="mb-4">

                            <label for="description"
                                class="form-label fw-semibold">

                                Description

                            </label>

                            <textarea id="description"
                                name="description"
                                rows="5"
                                class="form-control @error('description') is-invalid @enderror"
                                placeholder="Description de la catégorie...">{{ old('description', $category->description) }}</textarea>

                            @error('description')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>

                        {{-- ==================================================
                            BOUTONS
                        ================================================== --}}
                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">

                            <a href="{{ route('categories.index') }}"
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
