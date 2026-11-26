@extends('layouts.app')

@section('title', 'Mouvements de stock')

@section('content')

<div class="container-fluid py-4">

    {{-- ==================================================
        MESSAGES
    ================================================== --}}

    {{-- ==================================================
        EN-TÊTE
    ================================================== --}}

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="h3 mb-1">
                Mouvements de stock
            </h1>

            <p class="text-muted mb-0">
                Consultez l'historique des entrées et sorties de stock.
            </p>

        </div>

        @can('create', \App\Models\StockMovement::class)

            <a
                href="{{ route('stock-movements.create') }}"
                class="btn btn-primary"
            >

                <i class="bi bi-plus-lg me-1"></i>

                Nouveau mouvement

            </a>

        @endcan

    </div>

    {{-- ==================================================
        STATISTIQUES
    ================================================== --}}

    <div class="row g-3 mb-4">

        {{-- Mouvements --}}
        <div class="col-md-6 col-xl-3">

            <div class="card h-100 border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <p class="text-muted mb-1">
                                Mouvements
                            </p>

                            <h3 class="mb-0">
                                {{ number_format($totalMovements, 0, ',', ' ') }}
                            </h3>

                        </div>

                        <div class="fs-2 text-primary">

                            <i class="bi bi-arrow-left-right"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- Entrées --}}
        <div class="col-md-6 col-xl-3">

            <div class="card h-100 border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <p class="text-muted mb-1">
                                Entrées
                            </p>

                            <h3 class="mb-0 text-success">
                                +{{ number_format($totalEntries, 0, ',', ' ') }}
                            </h3>

                        </div>

                        <div class="fs-2 text-success">

                            <i class="bi bi-box-arrow-in-down"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- Sorties --}}
        <div class="col-md-6 col-xl-3">

            <div class="card h-100 border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <p class="text-muted mb-1">
                                Sorties
                            </p>

                            <h3 class="mb-0 text-danger">
                                -{{ number_format($totalOutputs, 0, ',', ' ') }}
                            </h3>

                        </div>

                        <div class="fs-2 text-danger">

                            <i class="bi bi-box-arrow-up"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- Solde net --}}
        <div class="col-md-6 col-xl-3">

            <div class="card h-100 border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <p class="text-muted mb-1">
                                Solde net
                            </p>

                            <h3
                                class="mb-0 {{ $netMovement >= 0 ? 'text-success' : 'text-danger' }}"
                            >

                                {{ $netMovement >= 0 ? '+' : '' }}

                                {{ number_format($netMovement, 0, ',', ' ') }}

                            </h3>

                        </div>

                        <div
                            class="fs-2 {{ $netMovement >= 0 ? 'text-success' : 'text-danger' }}"
                        >

                            <i class="bi bi-bar-chart-line"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- ==================================================
        FILTRES
    ================================================== --}}

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <form
                method="GET"
                action="{{ route('stock-movements.index') }}"
            >

                <div class="row g-3">

                    {{-- Date début --}}
                    <div class="col-md-3">

                        <label
                            for="start_date"
                            class="form-label"
                        >
                            Date début
                        </label>

                        <input
                            type="date"
                            id="start_date"
                            name="start_date"
                            class="form-control"
                            value="{{ request('start_date') }}"
                        >

                    </div>

                    {{-- Date fin --}}
                    <div class="col-md-3">

                        <label
                            for="end_date"
                            class="form-label"
                        >
                            Date fin
                        </label>

                        <input
                            type="date"
                            id="end_date"
                            name="end_date"
                            class="form-control"
                            value="{{ request('end_date') }}"
                        >

                    </div>

                    {{-- Produit --}}
                    <div class="col-md-3">

                        <label
                            for="product_id"
                            class="form-label"
                        >
                            Produit
                        </label>

                        <select
                            id="product_id"
                            name="product_id"
                            class="form-select"
                        >

                            <option value="">
                                Tous les produits
                            </option>

                            @foreach ($products as $product)

                                <option
                                    value="{{ $product->id }}"
                                    {{ request('product_id') == $product->id ? 'selected' : '' }}
                                >
                                    {{ $product->name }}
                                    — {{ $product->reference }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    {{-- Type --}}
                    <div class="col-md-3">

                        <label
                            for="type"
                            class="form-label"
                        >
                            Type de mouvement
                        </label>

                        <select
                            id="type"
                            name="type"
                            class="form-select"
                        >

                            <option value="">
                                Tous les types
                            </option>

                            <option
                                value="in"
                                {{ request('type') === 'in' ? 'selected' : '' }}
                            >
                                Entrée
                            </option>

                            <option
                                value="out"
                                {{ request('type') === 'out' ? 'selected' : '' }}
                            >
                                Sortie
                            </option>

                            <option
                                value="return"
                                {{ request('type') === 'return' ? 'selected' : '' }}
                            >
                                Retour
                            </option>

                            <option
                                value="loss"
                                {{ request('type') === 'loss' ? 'selected' : '' }}
                            >
                                Perte
                            </option>

                            <option
                                value="adjustment"
                                {{ request('type') === 'adjustment' ? 'selected' : '' }}
                            >
                                Correction
                            </option>

                        </select>

                    </div>

                    {{-- Boutons --}}
                    <div class="col-12 d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-dark"
                        >

                            <i class="bi bi-funnel me-1"></i>

                            Filtrer

                        </button>

                        <a
                            href="{{ route('stock-movements.index') }}"
                            class="btn btn-outline-secondary"
                        >

                            <i class="bi bi-arrow-counterclockwise me-1"></i>

                            Réinitialiser

                        </a>

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

                            <th class="px-3">
                                Date
                            </th>

                            <th>
                                Produit
                            </th>

                            <th>
                                Type
                            </th>

                            <th>
                                Quantité
                            </th>

                            <th>
                                Origine
                            </th>

                            <th>
                                Description
                            </th>

                            <th class="text-end px-3">
                                Actions
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($movements as $movement)

                            <tr>

                                {{-- DATE --}}
                                <td class="px-3">

                                    {{ $movement->created_at->format('d/m/Y H:i') }}

                                </td>

                                {{-- PRODUIT --}}
                                <td>

                                    @if ($movement->product)

                                        <strong>
                                            {{ $movement->product->name }}
                                        </strong>

                                        <br>

                                        <small class="text-muted">
                                            {{ $movement->product->reference }}
                                        </small>

                                    @else

                                        <span class="text-muted">
                                            Produit supprimé
                                        </span>

                                    @endif

                                </td>

                                {{-- TYPE --}}
                                <td>

                                    @if ($movement->type === 'in')

                                        <span class="badge bg-success">
                                            Entrée
                                        </span>

                                    @elseif ($movement->type === 'out')

                                        <span class="badge bg-danger">
                                            Sortie
                                        </span>

                                    @elseif ($movement->type === 'adjustment')

                                        <span class="badge bg-warning text-dark">
                                            Correction
                                        </span>

                                    @elseif ($movement->type === 'loss')

                                        <span class="badge bg-dark">
                                            Perte
                                        </span>

                                    @elseif ($movement->type === 'return')

                                        <span class="badge bg-info text-dark">
                                            Retour
                                        </span>

                                    @else

                                        <span class="badge bg-secondary">
                                            {{ $movement->type }}
                                        </span>

                                    @endif

                                </td>

                                {{-- QUANTITÉ --}}
                                <td>

                                    @if (
                                        in_array(
                                            $movement->type,
                                            ['in', 'return']
                                        )
                                    )

                                        <span class="text-success fw-bold">

                                            +{{ abs($movement->quantity) }}

                                        </span>

                                    @elseif (
                                        in_array(
                                            $movement->type,
                                            ['out', 'loss']
                                        )
                                    )

                                        <span class="text-danger fw-bold">

                                            -{{ abs($movement->quantity) }}

                                        </span>

                                    @else

                                        @if ($movement->quantity > 0)

                                            <span class="text-success fw-bold">

                                                +{{ $movement->quantity }}

                                            </span>

                                        @elseif ($movement->quantity < 0)

                                            <span class="text-danger fw-bold">

                                                {{ $movement->quantity }}

                                            </span>

                                        @else

                                            <span class="text-muted fw-bold">
                                                0
                                            </span>

                                        @endif

                                    @endif

                                </td>

                                {{-- ORIGINE --}}
                                <td>

                                    @if ($movement->isFromSale())

                                        @if ($movement->source)

                                            <a
                                                href="{{ route('sales.show', $movement->source) }}"
                                                class="text-decoration-none"
                                            >

                                                <span class="badge bg-primary">

                                                    <i class="bi bi-receipt me-1"></i>

                                                    Vente

                                                </span>

                                            </a>

                                            <br>

                                            <small class="text-muted">

                                                {{ $movement->source->sale_number }}

                                            </small>

                                        @else

                                            <span class="badge bg-secondary">

                                                <i class="bi bi-receipt me-1"></i>

                                                Vente supprimée

                                            </span>

                                        @endif

                                    @elseif ($movement->isFromPurchase())

                                        @if ($movement->source)

                                            <a
                                                href="{{ route('purchases.show', $movement->source) }}"
                                                class="text-decoration-none"
                                            >

                                                <span class="badge bg-secondary">

                                                    <i class="bi bi-bag me-1"></i>

                                                    Achat

                                                </span>

                                            </a>

                                            <br>

                                            <small class="text-muted">

                                                {{ $movement->source->purchase_number }}

                                            </small>

                                        @else

                                            <span class="badge bg-secondary">

                                                <i class="bi bi-bag me-1"></i>

                                                Achat supprimé

                                            </span>

                                        @endif

                                    @elseif ($movement->isManual())

                                        <span class="badge bg-light text-dark border">

                                            <i class="bi bi-pencil me-1"></i>

                                            Manuel

                                        </span>

                                    @else

                                        <span class="badge bg-warning text-dark">

                                            <i class="bi bi-question-circle me-1"></i>

                                            Origine inconnue

                                        </span>

                                    @endif

                                </td>

                                {{-- DESCRIPTION --}}
                                <td>

                                    {{ $movement->description ?? '—' }}

                                </td>

                                {{-- ACTIONS --}}
                                <td class="text-end px-3">

                                    <div class="d-flex justify-content-end gap-1">

                                        {{-- VOIR --}}
                                        @can('view', $movement)

                                            <a
                                                href="{{ route('stock-movements.show', $movement) }}"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Voir"
                                            >

                                                <i class="bi bi-eye"></i>

                                            </a>

                                        @endcan

                                        {{-- MODIFIER --}}
                                        @can('update', $movement)

                                            <a
                                                href="{{ route('stock-movements.edit', $movement) }}"
                                                class="btn btn-sm btn-outline-warning"
                                                title="Modifier"
                                            >

                                                <i class="bi bi-pencil"></i>

                                            </a>

                                        @endcan

                                        {{-- SUPPRIMER --}}
                                        @can('delete', $movement)

                                            <form
                                                action="{{ route('stock-movements.destroy', $movement) }}"
                                                method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Voulez-vous vraiment supprimer ce mouvement ? Le stock sera recalculé.');"
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
                                    colspan="7"
                                    class="text-center py-5 text-muted"
                                >

                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>

                                    Aucun mouvement de stock trouvé.

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

        @if ($movements->hasPages())

            <div class="card-footer">

                {{ $movements->withQueryString()->links() }}

            </div>

        @endif

    </div>

</div>

@endsection
