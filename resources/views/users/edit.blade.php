@extends('layouts.app')

@section('title', 'Modifier l\'utilisateur')

@section('content')

@php

    $isCurrentUser =
        $user->getKey() === auth()->id();

@endphp

<div class="container-fluid py-4">

    {{-- ==================================================
        EN-TÊTE
    ================================================== --}}

    <div class="mb-4">

        <h1 class="h3 mb-1">
            Modifier l'utilisateur
        </h1>

        <p class="text-muted mb-0">
            Modifiez les informations et le rôle de cet utilisateur.
        </p>

    </div>

    {{-- ==================================================
        MESSAGE DE SESSION
    ================================================== --}}

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
                action="{{ route('users.update', $user) }}"
            >

                @csrf

                @method('PUT')

                <div class="row g-3">

                    {{-- ==================================================
                        NOM
                    ================================================== --}}

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
                            value="{{ old('name', $user->name) }}"
                            autocomplete="name"
                            required
                        >

                        @error('name')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>

                    {{-- ==================================================
                        EMAIL
                    ================================================== --}}

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
                            value="{{ old('email', $user->email) }}"
                            autocomplete="email"
                            required
                        >

                        @error('email')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>

                    {{-- ==================================================
                        RÔLE
                    ================================================== --}}

                    <div class="col-md-6">

                        <label
                            for="role"
                            class="form-label fw-semibold"
                        >
                            Rôle *
                        </label>

                        @if ($isCurrentUser)

                            {{--
                                Le compte actuellement connecté doit rester
                                administrateur.
                            --}}
                            <input
                                type="hidden"
                                name="role"
                                value="admin"
                            >

                            <select
                                id="role"
                                class="form-select"
                                disabled
                            >

                                <option
                                    value="admin"
                                    selected
                                >
                                    Administrateur
                                </option>

                            </select>

                            <div class="form-text">
                                Vous ne pouvez pas retirer votre propre rôle administrateur.
                            </div>

                        @else

                            <select
                                id="role"
                                name="role"
                                class="form-select @error('role') is-invalid @enderror"
                                required
                            >

                                <option
                                    value="admin"
                                    {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}
                                >
                                    Administrateur
                                </option>

                                <option
                                    value="manager"
                                    {{ old('role', $user->role) === 'manager' ? 'selected' : '' }}
                                >
                                    Manager
                                </option>

                                <option
                                    value="employee"
                                    {{ old('role', $user->role) === 'employee' ? 'selected' : '' }}
                                >
                                    Employé
                                </option>

                            </select>

                            @error('role')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror

                        @endif

                    </div>

                    {{-- ==================================================
                        NOUVEAU MOT DE PASSE
                    ================================================== --}}

                    <div class="col-md-6">

                        <label
                            for="password"
                            class="form-label fw-semibold"
                        >
                            Nouveau mot de passe
                        </label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            autocomplete="new-password"
                        >

                        <div class="form-text">
                            Laissez vide pour conserver le mot de passe actuel.
                        </div>

                        @error('password')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>

                    {{-- ==================================================
                        CONFIRMATION MOT DE PASSE
                    ================================================== --}}

                    <div class="col-md-6">

                        <label
                            for="password_confirmation"
                            class="form-label fw-semibold"
                        >
                            Confirmer le nouveau mot de passe
                        </label>

                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="form-control"
                            autocomplete="new-password"
                        >

                    </div>

                </div>

                {{-- ==================================================
                    BOUTONS
                ================================================== --}}

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">

                    <a
                        href="{{ route('users.show', $user) }}"
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
