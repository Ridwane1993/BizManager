@extends('layouts.app')

@section('title', 'Modifier le fournisseur')

@section('content')

<div class="container-fluid py-4">

    {{-- ==================================================
        EN-TÊTE
    ================================================== --}}

    <div class="mb-4">

        <h1 class="h3 mb-1">
            Modifier le fournisseur
        </h1>

        <p class="text-muted mb-0">
            Modifiez les informations du fournisseur.
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
                action="{{ route('suppliers.update', $supplier) }}"
            >

                @csrf

                @method('PUT')

                <div class="row g-3">

                    {{-- Nom --}}
                    <div class="col-md-6">

                        <label
                            for="name"
                            class="form-label fw-semibold"
                        >
                            Nom du fournisseur *
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $supplier->name) }}"
                            required
                        >

                        @error('name')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>

                    {{-- Email --}}
                    <div class="col-md-6">

                        <label
                            for="email"
                            class="form-label fw-semibold"
                        >
                            Email
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $supplier->email) }}"
                        >

                        @error('email')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>

                    {{-- Téléphone --}}
                    <div class="col-md-6">

                        <label
                            for="phone"
                            class="form-label fw-semibold"
                        >
                            Téléphone
                        </label>

                        <input
                            type="text"
                            id="phone"
                            name="phone"
                            class="form-control @error('phone') is-invalid @enderror"
                            value="{{ old('phone', $supplier->phone) }}"
                        >

                        @error('phone')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>

                    {{-- Adresse --}}
                    <div class="col-12">

                        <label
                            for="address"
                            class="form-label fw-semibold"
                        >
                            Adresse
                        </label>

                        <textarea
                            id="address"
                            name="address"
                            rows="3"
                            class="form-control @error('address') is-invalid @enderror"
                        >{{ old('address', $supplier->address) }}</textarea>

                        @error('address')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>

                    {{-- Notes --}}
                    <div class="col-12">

                        <label
                            for="notes"
                            class="form-label fw-semibold"
                        >
                            Notes
                        </label>

                        <textarea
                            id="notes"
                            name="notes"
                            rows="3"
                            class="form-control @error('notes') is-invalid @enderror"
                        >{{ old('notes', $supplier->notes) }}</textarea>

                        @error('notes')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>

                </div>

                {{-- Boutons --}}
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">

                    <a
                        href="{{ route('suppliers.index') }}"
                        class="btn btn-outline-secondary"
                    >
                        Annuler
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        <i class="bi bi-check-lg me-1"></i>

                        Enregistrer les modifications

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection
