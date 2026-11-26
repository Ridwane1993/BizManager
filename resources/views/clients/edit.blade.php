@extends('layouts.app')

@section('title', 'Modifier le client')

@section('content')

<div class="container-fluid py-4">

    {{-- =========================
        EN-TÊTE
    ========================== --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="h3 mb-1 fw-bold">

                <i class="bi bi-pencil-square me-2"></i>

                Modifier le client

            </h1>

            <p class="text-muted mb-0">

                Modifiez les informations de {{ $client->name }}.

            </p>

        </div>

        <a href="{{ route('clients.index') }}"
            class="btn btn-outline-secondary">

            <i class="bi bi-arrow-left me-1"></i>

            Retour

        </a>

    </div>

    {{-- =========================
        ERREURS
    ========================== --}}
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

    {{-- =========================
        FORMULAIRE
    ========================== --}}
    <div class="row justify-content-center">

        <div class="col-xl-8 col-lg-9">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white py-3">

                    <h5 class="mb-1">
                        <i class="bi bi-person-vcard me-2"></i>
                        Informations du client
                    </h5>

                    <p class="text-muted mb-0">
                        Modifiez les informations puis enregistrez.
                    </p>

                </div>

                <div class="card-body p-4">

                    <form action="{{ route('clients.update', $client) }}"
                        method="POST">

                        @csrf

                        @method('PUT')

                        {{-- Nom --}}
                        <div class="mb-4">

                            <label for="name"
                                class="form-label fw-semibold">

                                Nom complet
                                <span class="text-danger">*</span>

                            </label>

                            <input type="text"
                                id="name"
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $client->name) }}"
                                required>

                            @error('name')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>

                        {{-- Email --}}
                        <div class="mb-4">

                            <label for="email"
                                class="form-label fw-semibold">

                                Adresse email

                            </label>

                            <input type="email"
                                id="email"
                                name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $client->email) }}">

                            @error('email')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>

                        {{-- Téléphone --}}
                        <div class="mb-4">

                            <label for="phone"
                                class="form-label fw-semibold">

                                Téléphone

                            </label>

                            <input type="text"
                                id="phone"
                                name="phone"
                                class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', $client->phone) }}">

                            @error('phone')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>

                        {{-- Adresse --}}
                        <div class="mb-4">

                            <label for="address"
                                class="form-label fw-semibold">

                                Adresse

                            </label>

                            <input type="text"
                                id="address"
                                name="address"
                                class="form-control @error('address') is-invalid @enderror"
                                value="{{ old('address', $client->address) }}">

                            @error('address')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>

                        {{-- Notes --}}
                        <div class="mb-4">

                            <label for="notes"
                                class="form-label fw-semibold">

                                Notes

                            </label>

                            <textarea id="notes"
                                name="notes"
                                rows="4"
                                class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $client->notes) }}</textarea>

                            @error('notes')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>

                        {{-- Boutons --}}
                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">

                            <a href="{{ route('clients.index') }}"
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
