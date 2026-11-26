@extends('layouts.app')

@section('content')

    <div class="container-fluid py-4">

        {{-- ================================================== --}}
        {{-- EN-TÊTE --}}
        {{-- ================================================== --}}

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h1 class="h3 mb-1 fw-bold">
                    <i class="bi bi-people me-2"></i>
                    Clients
                </h1>

                <p class="text-muted mb-0">
                    Gérez vos clients et leurs informations.
                </p>

            </div>

            {{-- Nouveau client --}}
            @can('create', \App\Models\Client::class)

                <a
                    href="{{ route('clients.create') }}"
                    class="btn btn-primary"
                >
                    <i class="bi bi-person-plus me-1"></i>
                    Nouveau client
                </a>

            @endcan

        </div>

        {{-- ================================================== --}}
        {{-- ================================================== --}}

        {{-- ================================================== --}}
        {{-- ================================================== --}}

        {{-- ================================================== --}}
        {{-- CARTE PRINCIPALE --}}
        {{-- ================================================== --}}

        <div class="card border-0 shadow-sm">

            {{-- ================================================== --}}
            {{-- RECHERCHE --}}
            {{-- ================================================== --}}

            <div class="p-3 border-bottom">

                <form
                    method="GET"
                    action="{{ route('clients.index') }}"
                >

                    <div class="row g-3 align-items-end">

                        <div class="col-12 col-md-8">

                            <label
                                for="search"
                                class="form-label small fw-semibold"
                            >
                                Recherche
                            </label>

                            <div class="input-group">

                                <span class="input-group-text bg-white">
                                    <i class="bi bi-search"></i>
                                </span>

                                <input
                                    type="text"
                                    id="search"
                                    name="search"
                                    maxlength="100"
                                    class="form-control @error('search') is-invalid @enderror"
                                    value="{{ old('search', $filters['search']) }}"
                                    placeholder="Rechercher par nom, email ou téléphone..."
                                >

                            </div>
                            @error('search')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                        </div>

                        <div class="col-12 col-md-4">

                            <button
                                type="submit"
                                class="btn btn-primary w-100"
                            >
                                <i class="bi bi-search me-1"></i>
                                Rechercher
                            </button>

                        </div>

                    </div>
                    @if ($filters['search'] !== '')
                        <div class="mt-3">
                            <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary btn-sm">
                                Effacer la recherche
                            </a>
                        </div>
                    @endif

                </form>

            </div>

            {{-- ================================================== --}}
            {{-- TABLEAU --}}
            {{-- ================================================== --}}

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th>Client</th>

                            <th>Email</th>

                            <th>Téléphone</th>

                            <th>Adresse</th>

                            <th class="text-end">
                                Actions
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($clients as $client)

                            <tr>

                                {{-- ====================================== --}}
                                {{-- CLIENT --}}
                                {{-- ====================================== --}}

                                <td>

                                    <div class="d-flex align-items-center">

                                        <div
                                            class="bg-primary bg-opacity-10 rounded-circle
                                                   d-flex align-items-center justify-content-center me-3"
                                            style="width: 40px; height: 40px;"
                                        >
                                            <i class="bi bi-person text-primary"></i>
                                        </div>

                                        <div>

                                            <div class="fw-semibold">
                                                {{ $client->name }}
                                            </div>

                                            <small class="text-muted">
                                                Client #{{ $client->id }}
                                            </small>

                                        </div>

                                    </div>

                                </td>

                                {{-- ====================================== --}}
                                {{-- EMAIL --}}
                                {{-- ====================================== --}}

                                <td>

                                    @if ($client->email)

                                        <a
                                            href="mailto:{{ $client->email }}"
                                        >
                                            {{ $client->email }}
                                        </a>

                                    @else

                                        <span class="text-muted">
                                            Non renseigné
                                        </span>

                                    @endif

                                </td>

                                {{-- ====================================== --}}
                                {{-- TÉLÉPHONE --}}
                                {{-- ====================================== --}}

                                <td>

                                    @if ($client->phone)

                                        {{ $client->phone }}

                                    @else

                                        <span class="text-muted">
                                            Non renseigné
                                        </span>

                                    @endif

                                </td>

                                {{-- ====================================== --}}
                                {{-- ADRESSE --}}
                                {{-- ====================================== --}}

                                <td>

                                    @if ($client->address)

                                        {{ $client->address }}

                                    @else

                                        <span class="text-muted">
                                            Non renseignée
                                        </span>

                                    @endif

                                </td>

                                {{-- ====================================== --}}
                                {{-- ACTIONS --}}
                                {{-- ====================================== --}}

                                <td class="text-end">

                                    <div
                                        class="btn-group"
                                        role="group"
                                    >

                                        {{-- Voir --}}
                                        @can('view', $client)

                                            <a
                                                href="{{ route('clients.show', $client) }}"
                                                class="btn btn-sm btn-outline-secondary"
                                                title="Voir"
                                            >
                                                <i class="bi bi-eye"></i>
                                            </a>

                                        @endcan

                                        {{-- Modifier --}}
                                        @can('update', $client)

                                            <a
                                                href="{{ route('clients.edit', $client) }}"
                                                class="btn btn-sm btn-outline-secondary"
                                                title="Modifier"
                                            >
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                        @endcan

                                        {{-- Supprimer --}}
                                        @can('delete', $client)

                                            <form
                                                action="{{ route('clients.destroy', $client) }}"
                                                method="POST"
                                                class="d-inline"
                                            >

                                                @csrf

                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Supprimer"
                                                    onclick="return confirm('Supprimer ce client ?')"
                                                >
                                                    <i class="bi bi-trash"></i>
                                                </button>

                                            </form>

                                        @endcan

                                    </div>

                                </td>

                            </tr>

                        @empty

                            {{-- ========================================== --}}
                            {{-- AUCUN CLIENT --}}
                            {{-- ========================================== --}}

                            <tr>

                                <td
                                    colspan="5"
                                    class="text-center py-5"
                                >

                                    <div class="mb-3">

                                        <i
                                            class="bi bi-people"
                                            style="font-size: 3rem;"
                                        ></i>

                                    </div>

                                    <h5 class="fw-semibold">
                                        Aucun client
                                    </h5>

                                    <p class="text-muted mb-3">
                                        @if ($filters['search'] !== '')
                                            Aucun client ne correspond à cette recherche.
                                        @else
                                            Aucun client n'a encore été enregistré.
                                        @endif
                                    </p>

                                    {{-- Ajouter un client --}}
                                    @can('create', \App\Models\Client::class)

                                        <a
                                            href="{{ route('clients.create') }}"
                                            class="btn btn-primary"
                                        >
                                            <i class="bi bi-person-plus me-1"></i>
                                            Ajouter un client
                                        </a>

                                    @endcan

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            @if ($clients->hasPages())
                <div class="card-footer bg-white">
                    {{ $clients->links() }}
                </div>
            @endif

        </div>

    </div>

@endsection
