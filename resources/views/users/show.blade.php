@extends('layouts.app')

@section('title', $user->name)

@section('content')

@php

    $isCurrentUser =
        $user->getKey() === auth()->id();

@endphp

<div class="container-fluid py-4">

    {{-- ==================================================
        EN-TÊTE
    ================================================== --}}

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="h3 mb-1">
                {{ $user->name }}
            </h1>

            <p class="text-muted mb-0">
                Détails de l'utilisateur.
            </p>

        </div>

        <div class="d-flex gap-2">

            <a
                href="{{ route('users.edit', $user) }}"
                class="btn btn-outline-primary"
            >

                <i class="bi bi-pencil me-1"></i>

                Modifier

            </a>

            @if (!$isCurrentUser)

                <form
                    action="{{ route('users.destroy', $user) }}"
                    method="POST"
                    class="d-inline"
                    onsubmit="return confirm('Voulez-vous vraiment supprimer cet utilisateur ?');"
                >

                    @csrf

                    @method('DELETE')

                    <button
                        type="submit"
                        class="btn btn-outline-danger"
                    >

                        <i class="bi bi-trash me-1"></i>

                        Supprimer

                    </button>

                </form>

            @endif

            <a
                href="{{ route('users.index') }}"
                class="btn btn-outline-secondary"
            >

                <i class="bi bi-arrow-left me-1"></i>

                Retour

            </a>

        </div>

    </div>

    {{-- ==================================================
        PROFIL
    ================================================== --}}

    <div class="row g-4">

        {{-- Profil --}}
        <div class="col-lg-5">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body text-center p-4">

                    <div
                        class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3"
                        style="width: 80px; height: 80px; font-size: 32px;"
                    >

                        {{ strtoupper(
                            substr($user->name, 0, 1)
                        ) }}

                    </div>

                    <h4 class="mb-1">
                        {{ $user->name }}
                    </h4>

                    <p class="text-muted mb-3">
                        {{ $user->email }}
                    </p>

                    @if ($user->role === 'admin')

                        <span class="badge bg-danger">
                            Administrateur
                        </span>

                    @elseif ($user->role === 'manager')

                        <span class="badge bg-primary">
                            Manager
                        </span>

                    @else

                        <span class="badge bg-secondary">
                            Employé
                        </span>

                    @endif

                    @if ($isCurrentUser)

                        <div class="mt-3">

                            <span class="badge bg-light text-dark border">

                                <i class="bi bi-person-check me-1"></i>

                                Compte actuellement connecté

                            </span>

                        </div>

                    @endif

                </div>

            </div>

        </div>

        {{-- Informations --}}
        <div class="col-lg-7">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white">

                    <h5 class="mb-0">
                        Informations
                    </h5>

                </div>

                <div class="card-body">

                    {{-- Nom --}}
                    <div class="mb-4">

                        <small class="text-muted d-block mb-1">
                            Nom
                        </small>

                        <div class="fw-semibold">
                            {{ $user->name }}
                        </div>

                    </div>

                    {{-- Email --}}
                    <div class="mb-4">

                        <small class="text-muted d-block mb-1">
                            E-mail
                        </small>

                        <div class="fw-semibold">
                            {{ $user->email }}
                        </div>

                    </div>

                    {{-- Rôle --}}
                    <div class="mb-4">

                        <small class="text-muted d-block mb-1">
                            Rôle
                        </small>

                        @if ($user->role === 'admin')

                            <span class="badge bg-danger">
                                Administrateur
                            </span>

                        @elseif ($user->role === 'manager')

                            <span class="badge bg-primary">
                                Manager
                            </span>

                        @else

                            <span class="badge bg-secondary">
                                Employé
                            </span>

                        @endif

                    </div>

                    {{-- Création --}}
                    <div>

                        <small class="text-muted d-block mb-1">
                            Compte créé le
                        </small>

                        <div>

                            {{ $user->created_at->format('d/m/Y à H:i') }}

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection
