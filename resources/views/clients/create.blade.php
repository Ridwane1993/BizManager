@extends('layouts.app')

@section('title', 'Nouveau client')

@section('content')

<div class="container-fluid py-4">

    {{-- En-tête --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">
                <i class="bi bi-person-plus me-2"></i>
                Nouveau client
            </h1>

            <p class="text-muted mb-0">
                Ajoutez un nouveau client à votre entreprise.
            </p>
        </div>

        <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Retour
        </a>
    </div>

    {{-- Formulaire --}}
    <div class="card border-0 shadow-sm">

        <div class="card-body p-4">

            <form action="{{ route('clients.store') }}" method="POST">

                @csrf

                <div class="row g-4">

                    {{-- Nom --}}
                    <div class="col-12 col-md-6">

                        <label for="name" class="form-label fw-semibold">
                            Nom complet <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}"
                            placeholder="Ex : Jean Dupont"
                            required
                        >

                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    {{-- Email --}}
                    <div class="col-12 col-md-6">

                        <label for="email" class="form-label fw-semibold">
                            Adresse email
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email') }}"
                            placeholder="Ex : jean@example.com"
                        >

                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    {{-- Téléphone --}}
                    <div class="col-12 col-md-6">

                        <label for="phone" class="form-label fw-semibold">
                            Téléphone
                        </label>

                        <input
                            type="text"
                            id="phone"
                            name="phone"
                            class="form-control @error('phone') is-invalid @enderror"
                            value="{{ old('phone') }}"
                            placeholder="Ex : 97000000"
                        >

                        @error('phone')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    {{-- Adresse --}}
                    <div class="col-12 col-md-6">

                        <label for="address" class="form-label fw-semibold">
                            Adresse
                        </label>

                        <input
                            type="text"
                            id="address"
                            name="address"
                            class="form-control @error('address') is-invalid @enderror"
                            value="{{ old('address') }}"
                            placeholder="Ex : Cotonou"
                        >

                        @error('address')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    {{-- Notes --}}
                    <div class="col-12">

                        <label for="notes" class="form-label fw-semibold">
                            Notes
                        </label>

                        <textarea
                            id="notes"
                            name="notes"
                            rows="4"
                            class="form-control @error('notes') is-invalid @enderror"
                            placeholder="Informations supplémentaires concernant ce client..."
                        >{{ old('notes') }}</textarea>

                        @error('notes')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

                {{-- Boutons --}}
                <div class="d-flex justify-content-end gap-2 mt-4 pt-4 border-top">

                    <a
                        href="{{ route('clients.index') }}"
                        class="btn btn-outline-secondary"
                    >
                        Annuler
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>
                        Enregistrer le client
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection
