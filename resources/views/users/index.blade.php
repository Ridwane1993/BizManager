@extends('layouts.app')

@section('title', 'Utilisateurs')

@section('content')

<div class="container-fluid py-4">

    {{-- ==================================================
        EN-TÊTE
    ================================================== --}}

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="h3 mb-1">
                Utilisateurs
            </h1>

            <p class="text-muted mb-0">
                Gérez les comptes et les rôles des utilisateurs.
            </p>

        </div>

        <a
            href="{{ route('users.create') }}"
            class="btn btn-primary"
        >

            <i class="bi bi-person-plus me-1"></i>

            Nouvel utilisateur

        </a>

    </div>

    @if (session('company_join_code'))
        <div class="alert alert-info">
            <div class="fw-semibold">Code entreprise — à communiquer à vos collaborateurs</div>
            <code class="fs-5">{{ session('company_join_code') }}</code>
            <div class="small mt-1">Ce code ne sera plus affiché après cette page. En générer un nouveau invalidera le précédent.</div>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="h6 mb-1">Inviter des collaborateurs</h2>
                <p class="text-muted mb-0">Générez un code à usage partagé pour rattacher une inscription à votre entreprise.</p>
            </div>
            <form method="POST" action="{{ route('company.join-code') }}">
                @csrf
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-key me-1"></i> Générer un code
                </button>
            </form>
        </div>
    </div>

    {{-- ==================================================
        MESSAGES
    ================================================== --}}

    {{-- ==================================================
        RECHERCHE
    ================================================== --}}

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <form
                method="GET"
                action="{{ route('users.index') }}"
            >

                <div class="row g-3">

                    <div class="col-md-10">

                        <label
                            for="search"
                            class="form-label fw-semibold"
                        >
                            Rechercher
                        </label>

                        <input
                            type="text"
                            id="search"
                            name="search"
                            class="form-control"
                            value="{{ request('search') }}"
                            placeholder="Nom ou adresse e-mail..."
                        >

                    </div>

                    <div class="col-md-2 d-flex align-items-end">

                        <button
                            type="submit"
                            class="btn btn-dark w-100"
                        >

                            <i class="bi bi-search me-1"></i>

                            Rechercher

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

    {{-- ==================================================
        TABLEAU
    ================================================== --}}

    <div class="card border-0 shadow-sm">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th class="px-4">
                                Utilisateur
                            </th>

                            <th>
                                E-mail
                            </th>

                            <th>
                                Rôle
                            </th>

                            <th>
                                Créé le
                            </th>

                            <th class="text-end px-4">
                                Actions
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($users as $user)

                            @php
                                $isCurrentUser =
                                    $user->getKey() === auth()->id();
                            @endphp

                            <tr>

                                {{-- ==================================================
                                    UTILISATEUR
                                ================================================== --}}

                                <td class="px-4">

                                    <div class="d-flex align-items-center gap-2">

                                        <div
                                            class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width: 40px; height: 40px;"
                                        >

                                            {{ strtoupper(
                                                substr($user->name, 0, 1)
                                            ) }}

                                        </div>

                                        <div>

                                            <strong>
                                                {{ $user->name }}
                                            </strong>

                                            @if ($isCurrentUser)

                                                <span class="badge bg-light text-dark border ms-1">
                                                    Vous
                                                </span>

                                            @endif

                                        </div>

                                    </div>

                                </td>

                                {{-- ==================================================
                                    EMAIL
                                ================================================== --}}

                                <td>
                                    {{ $user->email }}
                                </td>

                                {{-- ==================================================
                                    RÔLE
                                ================================================== --}}

                                <td>

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

                                </td>

                                {{-- ==================================================
                                    DATE
                                ================================================== --}}

                                <td>
                                    {{ $user->created_at->format('d/m/Y') }}
                                </td>

                                {{-- ==================================================
                                    ACTIONS
                                ================================================== --}}

                                <td class="text-end px-4">

                                    <div class="d-inline-flex gap-1">

                                        {{-- Voir --}}
                                        <a
                                            href="{{ route('users.show', $user) }}"
                                            class="btn btn-sm btn-outline-primary"
                                            title="Voir"
                                        >

                                            <i class="bi bi-eye"></i>

                                        </a>

                                        {{-- Modifier --}}
                                        <a
                                            href="{{ route('users.edit', $user) }}"
                                            class="btn btn-sm btn-outline-secondary"
                                            title="Modifier"
                                        >

                                            <i class="bi bi-pencil"></i>

                                        </a>

                                        {{-- Supprimer --}}
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
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Supprimer"
                                                >

                                                    <i class="bi bi-trash"></i>

                                                </button>

                                            </form>

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="5"
                                    class="text-center py-5 text-muted"
                                >

                                    <i class="bi bi-people fs-1 d-block mb-2"></i>

                                    Aucun utilisateur trouvé.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        {{-- ==================================================
            PAGINATION
        ================================================== --}}

        @if ($users->hasPages())

            <div class="card-footer">

                {{ $users->links() }}

            </div>

        @endif

    </div>

</div>

@endsection
