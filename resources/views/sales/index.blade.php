@extends('layouts.app')

@section('title', 'Ventes')

@section('content')

<div class="container-fluid py-4">

    {{-- ==================================================
        EN-TÊTE
    ================================================== --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="h3 mb-1">
                Ventes
            </h1>

            <p class="text-muted mb-0">
                Gérez vos ventes et vos transactions.
            </p>

        </div>

        @can('create', \App\Models\Sale::class)

            <a
                href="{{ route('sales.create') }}"
                class="btn btn-primary"
            >

                <i class="bi bi-plus-lg me-1"></i>

                Nouvelle vente

            </a>

        @endcan

    </div>

    {{-- ==================================================
        MESSAGE SUCCÈS
    ================================================== --}}

    {{-- ==================================================
        MESSAGE ERREUR
    ================================================== --}}

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('sales.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-6 col-xl-3">
                        <label for="search" class="form-label">Recherche</label>
                        <input
                            type="search"
                            id="search"
                            name="search"
                            value="{{ old('search', $filters['search']) }}"
                            maxlength="100"
                            class="form-control @error('search') is-invalid @enderror"
                            placeholder="Numéro de vente ou nom du client"
                        >
                        @error('search')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-md-6 col-xl-3">
                        <label for="status" class="form-label">Statut</label>
                        <select
                            id="status"
                            name="status"
                            class="form-select @error('status') is-invalid @enderror"
                        >
                            <option value="">Tous les statuts</option>
                            <option value="pending" @selected(old('status', $filters['status']) === 'pending')>
                                En attente
                            </option>
                            <option value="completed" @selected(old('status', $filters['status']) === 'completed')>
                                Terminées
                            </option>
                            <option value="cancelled" @selected(old('status', $filters['status']) === 'cancelled')>
                                Annulées
                            </option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-sm-6 col-xl-2">
                        <label for="start_date" class="form-label">Du</label>
                        <input
                            type="date"
                            id="start_date"
                            name="start_date"
                            value="{{ old('start_date', $filters['start_date']) }}"
                            max="{{ $filters['end_date'] }}"
                            class="form-control @error('start_date') is-invalid @enderror"
                        >
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-sm-6 col-xl-2">
                        <label for="end_date" class="form-label">Au</label>
                        <input
                            type="date"
                            id="end_date"
                            name="end_date"
                            value="{{ old('end_date', $filters['end_date']) }}"
                            min="{{ $filters['start_date'] }}"
                            class="form-control @error('end_date') is-invalid @enderror"
                        >
                        @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-xl-2 d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel me-1" aria-hidden="true"></i>
                            Filtrer
                        </button>
                        @if (
                            $filters['search'] !== ''
                            || $filters['status'] !== ''
                            || $filters['start_date'] !== ''
                            || $filters['end_date'] !== ''
                        )
                            <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
                                Effacer
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================================================
        TABLEAU
    ================================================== --}}
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white py-3">

            <h5 class="mb-0">
                Liste des ventes
            </h5>

        </div>

        <div class="card-body p-0">

            @if ($sales->count())

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th class="px-4">
                                    N° Vente
                                </th>

                                <th>
                                    Client
                                </th>

                                <th>
                                    Date
                                </th>

                                <th>
                                    Total
                                </th>

                                <th>
                                    Statut
                                </th>

                                <th class="text-end px-4">
                                    Actions
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach ($sales as $sale)

                                <tr>

                                    {{-- Numéro --}}
                                    <td class="px-4">

                                        <span class="fw-semibold">
                                            {{ $sale->sale_number }}
                                        </span>

                                    </td>

                                    {{-- Client --}}
                                    <td>

                                        {{ $sale->client->name ?? 'Client supprimé' }}

                                    </td>

                                    {{-- Date --}}
                                    <td>

                                        {{ $sale->sale_date->format('d/m/Y') }}

                                    </td>

                                    {{-- Total --}}
                                    <td>

                                        <strong>

                                            {{ number_format(
                                                $sale->total_amount,
                                                0,
                                                ',',
                                                ' '
                                            ) }}

                                            FCFA

                                        </strong>

                                    </td>

                                    {{-- Statut --}}
                                    <td>

                                        <x-status-badge :status="$sale->status" type="sale" />

                                    </td>

                                    {{-- Actions --}}
                                    <td class="text-end px-4">

                                        <div class="btn-group">

                                            {{-- Voir --}}
                                            @can('view', $sale)

                                                <a
                                                    href="{{ route('sales.show', $sale) }}"
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="Voir"
                                                >
                                                    <i class="bi bi-eye"></i>
                                                </a>

                                            @endcan

                                            {{-- Modifier --}}
                                            @can('update', $sale)

                                                <a
                                                    href="{{ route('sales.edit', $sale) }}"
                                                    class="btn btn-sm btn-outline-secondary"
                                                    title="Modifier"
                                                >
                                                    <i class="bi bi-pencil"></i>
                                                </a>

                                            @endcan

                                            {{-- Supprimer --}}
                                            @can('delete', $sale)

                                                <form
                                                    action="{{ route('sales.destroy', $sale) }}"
                                                    method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Supprimer cette vente ? Le stock sera restauré.');"
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

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                {{-- ==================================================
                    AUCUNE VENTE
                ================================================== --}}
                <div class="text-center py-5">

                    <i class="bi bi-receipt fs-1 text-muted"></i>

                    <h5 class="mt-3">
                        Aucune vente
                    </h5>

                    <p class="text-muted mb-3">
                        @if (
                            $filters['search'] !== ''
                            || $filters['status'] !== ''
                            || $filters['start_date'] !== ''
                            || $filters['end_date'] !== ''
                        )
                            Aucune vente ne correspond à ces filtres.
                        @else
                            Vous n'avez encore enregistré aucune vente.
                        @endif
                    </p>

                    @can('create', \App\Models\Sale::class)

                        <a
                            href="{{ route('sales.create') }}"
                            class="btn btn-primary"
                        >

                            <i class="bi bi-plus-lg me-1"></i>

                            Créer une vente

                        </a>

                    @endcan

                </div>

            @endif

        </div>

        @if ($sales->hasPages())
            <div class="card-footer bg-white">
                {{ $sales->links() }}
            </div>
        @endif

    </div>

</div>

@endsection
