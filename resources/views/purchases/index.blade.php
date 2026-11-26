@extends('layouts.app')

@section('title', 'Achats')

@section('content')

<div class="container-fluid py-4">

    {{-- ==================================================
        EN-TÊTE
    ================================================== --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="h3 mb-1">
                Achats
            </h1>

            <p class="text-muted mb-0">
                Gérez vos achats et vos approvisionnements.
            </p>

        </div>

        {{-- Nouveau achat --}}
        @can('create', App\Models\Purchase::class)

            <a href="{{ route('purchases.create') }}"
                class="btn btn-primary">

                <i class="bi bi-plus-lg me-1"></i>

                Nouvel achat

            </a>

        @endcan

    </div>

    {{-- ==================================================
        MESSAGE DE SUCCÈS
    ================================================== --}}

    {{-- ==================================================
        MESSAGE D'ERREUR
    ================================================== --}}

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('purchases.index') }}">
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
                            placeholder="Numéro d'achat ou fournisseur"
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
                            <option value="received" @selected(old('status', $filters['status']) === 'received')>
                                Reçu
                            </option>
                            <option value="cancelled" @selected(old('status', $filters['status']) === 'cancelled')>
                                Annulé
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
                            <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">
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

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th class="px-4">
                                Numéro
                            </th>

                            <th>
                                Fournisseur
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

                        @forelse ($purchases as $purchase)

                            <tr>

                                {{-- Numéro --}}
                                <td class="px-4">

                                    <strong>
                                        {{ $purchase->purchase_number }}
                                    </strong>

                                </td>

                                {{-- Fournisseur --}}
                                <td>

                                    {{ $purchase->supplier->name ?? 'Fournisseur supprimé' }}

                                </td>

                                {{-- Date --}}
                                <td>

                                    {{ $purchase->purchase_date->format('d/m/Y') }}

                                </td>

                                {{-- Total --}}
                                <td>

                                    <strong>

                                        {{ number_format(
                                            $purchase->total_amount,
                                            0,
                                            ',',
                                            ' '
                                        ) }}

                                        FCFA

                                    </strong>

                                </td>

                                {{-- Statut --}}
                                <td>

                                    <x-status-badge :status="$purchase->status" type="purchase" />

                                </td>

                                {{-- Actions --}}
                                <td class="text-end px-4">

                                    <div class="d-inline-flex gap-1">

                                        {{-- Voir --}}
                                        @can('view', $purchase)

                                            <a href="{{ route('purchases.show', $purchase) }}"
                                                class="btn btn-sm btn-outline-secondary"
                                                title="Voir">

                                                <i class="bi bi-eye"></i>

                                            </a>

                                        @endcan

                                        {{-- Modifier --}}
                                        @can('update', $purchase)

                                            <a href="{{ route('purchases.edit', $purchase) }}"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Modifier">

                                                <i class="bi bi-pencil"></i>

                                            </a>

                                        @endcan

                                        {{-- Supprimer --}}
                                        @can('delete', $purchase)

                                            <form action="{{ route('purchases.destroy', $purchase) }}"
                                                method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Voulez-vous vraiment supprimer cet achat ? Le stock sera mis à jour.');">

                                                @csrf

                                                @method('DELETE')

                                                <button type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Supprimer">

                                                    <i class="bi bi-trash"></i>

                                                </button>

                                            </form>

                                        @endcan

                                    </div>

                                </td>

                            </tr>

                        @empty

                            {{-- ==================================================
                                AUCUN ACHAT
                            ================================================== --}}
                            <tr>

                                <td colspan="6"
                                    class="text-center py-5 text-muted">

                                    <i class="bi bi-cart-x fs-1 d-block mb-2"></i>

                                    <h5 class="mt-3">
                                        Aucun achat
                                    </h5>

                                    <p class="mb-3">
                                        @if (
                                            $filters['search'] !== ''
                                            || $filters['status'] !== ''
                                            || $filters['start_date'] !== ''
                                            || $filters['end_date'] !== ''
                                        )
                                            Aucun achat ne correspond à ces filtres.
                                        @else
                                            Aucun achat enregistré.
                                        @endif
                                    </p>

                                    {{-- Créer un achat --}}
                                    @can('create', App\Models\Purchase::class)

                                        <a href="{{ route('purchases.create') }}"
                                            class="btn btn-primary">

                                            <i class="bi bi-plus-lg me-1"></i>

                                            Créer un achat

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
        @if ($purchases->hasPages())

            <div class="card-footer">

                {{ $purchases->withQueryString()->links() }}

            </div>

        @endif

    </div>

</div>

@endsection
