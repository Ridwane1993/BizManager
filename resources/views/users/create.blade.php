@extends('layouts.app')

@section('title', 'Nouvel utilisateur')

@section('content')

<div class="container-fluid py-4">

    {{-- ==================================================
        EN-TÊTE
    ================================================== --}}

    <div class="mb-4">

        <h1 class="h3 mb-1">
            Nouvel utilisateur
        </h1>

        <p class="text-muted mb-0">
            Créez un compte et définissez son rôle.
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
                action="{{ route('users.store') }}"
            >

                @csrf

                <div class="row g-3">

                    {{-- Nom --}}
                    <div class="col-md-6">

                        <label
                            for="name"
                            class="form-label fw-semibold"
                        >
                            Nom *
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}"
                            autocomplete="name"
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
                            Adresse e-mail *
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            required
                        >

                        @error('email')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>

                    {{-- Rôle --}}
                    <div class="col-md-6">

                        <label
                            for="role"
                            class="form-label fw-semibold"
                        >
                            Rôle *
                        </label>

                        <select
                            id="role"
                            name="role"
                            class="form-select @error('role') is-invalid @enderror"
                            required
                        >

                            <option value="">
                                Sélectionner un rôle
                            </option>

                            <option
                                value="admin"
                                {{ old('role') === 'admin' ? 'selected' : '' }}
                            >
                                Administrateur
                            </option>

                            <option
                                value="manager"
                                {{ old('role') === 'manager' ? 'selected' : '' }}
                            >
                                Manager
                            </option>

                            <option
                                value="employee"
                                {{ old('role') === 'employee' ? 'selected' : '' }}
                            >
                                Employé
                            </option>

                        </select>

                        @error('role')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>

                    {{-- Mot de passe --}}
                    <div class="col-md-6">

                        <label
                            for="password"
                            class="form-label fw-semibold"
                        >
                            Mot de passe *
                        </label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            autocomplete="new-password"
                            required
                        >

                        @error('password')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>

                    {{-- Confirmation --}}
                    <div class="col-md-6">

                        <label
                            for="password_confirmation"
                            class="form-label fw-semibold"
                        >
                            Confirmer le mot de passe *
                        </label>

                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="form-control"
                            autocomplete="new-password"
                            required
                        >

                    </div>

                </div>

                {{-- ==================================================
                    BOUTONS
                ================================================== --}}

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">

                    <a
                        href="{{ route('users.index') }}"
                        class="btn btn-outline-secondary"
                    >
                        Annuler
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        <i class="bi bi-person-plus me-1"></i>

                        Créer l'utilisateur

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection
