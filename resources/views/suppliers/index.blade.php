@extends('layouts.app')

@section('title', 'Fournisseurs')

@section('content')

<div class="container-fluid py-4">

    {{-- ==================================================
        EN-TÊTE
    ================================================== --}}

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="h3 mb-1">
                Fournisseurs
            </h1>

            <p class="text-muted mb-0">
                Gérez vos fournisseurs et leurs informations.
            </p>

        </div>

        @can('create', \App\Models\Supplier::class)

            <a
                href="{{ route('suppliers.create') }}"
                class="btn btn-primary"
            >

                <i class="bi bi-plus-lg me-1"></i>

                Nouveau fournisseur

            </a>

        @endcan

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
                action="{{ route('suppliers.index') }}"
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
                            placeholder="Nom, email ou téléphone..."
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
                                Fournisseur
                            </th>

                            <th>
                                Email
                            </th>

                            <th>
                                Téléphone
                            </th>

                            <th>
                                Achats
                            </th>

                            <th class="text-end px-4">
                                Actions
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($suppliers as $supplier)

                            <tr>

                                {{-- Fournisseur --}}
                                <td class="px-4">

                                    <strong>
                                        {{ $supplier->name }}
                                    </strong>

                                </td>

                                {{-- Email --}}
                                <td>

                                    {{ $supplier->email ?? '—' }}

                                </td>

                                {{-- Téléphone --}}
                                <td>

                                    {{ $supplier->phone ?? '—' }}

                                </td>

                                {{-- Achats --}}
                                <td>

                                    <span class="badge bg-primary">

                                        {{ $supplier->purchases_count }}

                                    </span>

                                </td>

                                {{-- Actions --}}
                                <td class="text-end px-4">

                                    <div class="d-inline-flex gap-1">

                                        {{-- Voir --}}
                                        @can('view', $supplier)

                                            <a
                                                href="{{ route('suppliers.show', $supplier) }}"
                                                class="btn btn-sm btn-outline-secondary"
                                                title="Voir"
                                            >

                                                <i class="bi bi-eye"></i>

                                            </a>

                                        @endcan

                                        {{-- Modifier --}}
                                        @can('update', $supplier)

                                            <a
                                                href="{{ route('suppliers.edit', $supplier) }}"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Modifier"
                                            >

                                                <i class="bi bi-pencil"></i>

                                            </a>

                                        @endcan

                                        {{-- Supprimer --}}
                                        @can('delete', $supplier)

                                            <form
                                                action="{{ route('suppliers.destroy', $supplier) }}"
                                                method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Voulez-vous vraiment supprimer ce fournisseur ?');"
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

                                        @endcan

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="5"
                                    class="text-center py-5 text-muted"
                                >

                                    <i class="bi bi-truck fs-1"></i>

                                    <p class="mt-3 mb-3">
                                        Aucun fournisseur trouvé.
                                    </p>

                                    @can('create', \App\Models\Supplier::class)

                                        <a
                                            href="{{ route('suppliers.create') }}"
                                            class="btn btn-primary"
                                        >

                                            <i class="bi bi-plus-lg me-1"></i>

                                            Ajouter un fournisseur

                                        </a>

                                    @endcan

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

        @if ($suppliers->hasPages())

            <div class="card-footer">

                {{ $suppliers->withQueryString()->links() }}

            </div>

        @endif

    </div>

</div>

@endsection
