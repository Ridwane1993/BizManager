@extends('layouts.app')

@section('title', 'Nouvelle catégorie')

@section('content')

<div class="container-fluid py-4">

    {{-- ==================================================
        EN-TÊTE
    ================================================== --}}
    <div class="mb-4">

        <h1 class="h3 fw-bold mb-1">
            Nouvelle catégorie
        </h1>

        <p class="text-muted mb-0">
            Ajoutez une catégorie à votre catalogue.
        </p>

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

        <div class="col-xl-8 col-lg-9">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white">

                    <h5 class="mb-1">
                        Informations de la catégorie
                    </h5>

                    <p class="text-muted mb-0">
                        Renseignez les informations de la nouvelle catégorie.
                    </p>

                </div>

                <div class="card-body p-4">

                    <form action="{{ route('categories.store') }}"
                        method="POST">

                        @csrf

                        {{-- Nom --}}
                        <div class="mb-4">

                            <label for="name"
                                class="form-label fw-semibold">

                                Nom de la catégorie
                                <span class="text-danger">*</span>

                            </label>

                            <input type="text"
                                name="name"
                                id="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}"
                                placeholder="Ex : Informatique"
                                required>

                            @error('name')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>

                        {{-- Description --}}
                        <div class="mb-4">

                            <label for="description"
                                class="form-label fw-semibold">

                                Description

                            </label>

                            <textarea name="description"
                                id="description"
                                rows="5"
                                class="form-control @error('description') is-invalid @enderror"
                                placeholder="Description de la catégorie...">{{ old('description') }}</textarea>

                            @error('description')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>

                        {{-- Boutons --}}
                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">

                            <a href="{{ route('categories.index') }}"
                                class="btn btn-outline-secondary">

                                <i class="bi bi-arrow-left me-1"></i>

                                Annuler

                            </a>

                            <button type="submit"
                                class="btn btn-primary">

                                <i class="bi bi-check-lg me-1"></i>

                                Enregistrer

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection
