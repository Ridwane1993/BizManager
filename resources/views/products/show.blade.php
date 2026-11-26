@extends('layouts.app')

@section('title', 'Détail du produit')

@section('content')

<div class="container-fluid">

    {{-- En-tête --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Détail du produit</h1>
            <p class="text-muted mb-0">
                Consultez les informations et l'historique de stock du produit.
            </p>
        </div>

        <div class="d-flex gap-2">
            @can('update', $product)
                <a href="{{ route('products.edit', $product) }}" class="btn btn-warning">
                    <i class="bi bi-pencil me-1"></i>
                    Modifier
                </a>
            @endcan

            @can('viewAny', \App\Models\StockMovement::class)
                <a href="{{ route('stock-movements.index', ['product_id' => $product->id]) }}"
                   class="btn btn-outline-primary">
                    <i class="bi bi-clock-history me-1"></i>
                    Voir tout l'historique
                </a>
            @endcan

            <a href="{{ route('products.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Retour
            </a>
        </div>
    </div>

    {{-- Informations du produit --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="bi bi-box-seam me-2"></i>
                Informations du produit
            </h5>
        </div>

        <div class="card-body">
            <div class="row g-4">

                <div class="col-md-6 col-lg-4">
                    <small class="text-muted d-block">Nom</small>
                    <strong>{{ $product->name }}</strong>
                </div>

                <div class="col-md-6 col-lg-4">
                    <small class="text-muted d-block">Référence</small>
                    <strong>{{ $product->reference }}</strong>
                </div>

                <div class="col-md-6 col-lg-4">
                    <small class="text-muted d-block">Catégorie</small>
                    <strong>
                        {{ $product->category?->name ?? 'Sans catégorie' }}
                    </strong>
                </div>

                <div class="col-md-6 col-lg-4">
                    <small class="text-muted d-block">Prix d'achat</small>
                    <strong>
                        {{ number_format($product->purchase_price, 0, ',', ' ') }} FCFA
                    </strong>
                </div>

                <div class="col-md-6 col-lg-4">
                    <small class="text-muted d-block">Prix de vente</small>
                    <strong>
                        {{ number_format($product->selling_price, 0, ',', ' ') }} FCFA
                    </strong>
                </div>

                <div class="col-md-6 col-lg-4">
                    <small class="text-muted d-block">Seuil d'alerte</small>
                    <strong>
                        {{ number_format($product->stock_alert_threshold, 0, ',', ' ') }}
                    </strong>
                </div>

                @if($product->description)
                    <div class="col-12">
                        <small class="text-muted d-block">Description</small>
                        <div class="mt-1">
                            {{ $product->description }}
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    {{-- Statistiques du stock --}}
    <div class="row g-4 mb-4">

        {{-- Stock actuel --}}
        <div class="col-md-6 col-xl-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Stock actuel</div>
                            <h3 class="mb-0 mt-1">
                                {{ number_format($product->stock, 0, ',', ' ') }}
                            </h3>
                        </div>

                        <div class="fs-2 text-primary">
                            <i class="bi bi-box-seam"></i>
                        </div>
                    </div>

                    @if($product->stock <= $product->stock_alert_threshold)
                        <div class="mt-3">
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Stock faible
                            </span>
                        </div>
                    @else
                        <div class="mt-3">
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>
                                Stock normal
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Entrées --}}
        <div class="col-md-6 col-xl-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Total entrées</div>
                            <h3 class="mb-0 mt-1 text-success">
                                {{ number_format($stockSummary->total_entries ?? 0, 0, ',', ' ') }}
                            </h3>
                        </div>

                        <div class="fs-2 text-success">
                            <i class="bi bi-arrow-down-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sorties --}}
        <div class="col-md-6 col-xl-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Total sorties</div>
                            <h3 class="mb-0 mt-1 text-danger">
                                {{ number_format($stockSummary->total_outputs ?? 0, 0, ',', ' ') }}
                            </h3>
                        </div>

                        <div class="fs-2 text-danger">
                            <i class="bi bi-arrow-up-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pertes --}}
        <div class="col-md-6 col-xl-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Total pertes</div>
                            <h3 class="mb-0 mt-1 text-warning">
                                {{ number_format($stockSummary->total_losses ?? 0, 0, ',', ' ') }}
                            </h3>
                        </div>

                        <div class="fs-2 text-warning">
                            <i class="bi bi-exclamation-octagon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Historique des mouvements --}}
    <div class="card shadow-sm">

        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">
                    <i class="bi bi-clock-history me-2"></i>
                    Historique du stock
                </h5>

                <small class="text-muted">
                    Tous les mouvements enregistrés pour ce produit.
                </small>
            </div>

            @can('create', \App\Models\StockMovement::class)
                <a href="{{ route('stock-movements.create', ['product_id' => $product->id]) }}"
                   class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i>
                    Nouveau mouvement
                </a>
            @endcan
        </div>

        <div class="card-body p-0">

            @if($stockMovements->count())

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Quantité</th>
                                <th>Description</th>
                                <th>Origine</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($stockMovements as $movement)

                                <tr>

                                    {{-- Date --}}
                                    <td>
                                        {{ $movement->created_at?->format('d/m/Y H:i') }}
                                    </td>

                                    {{-- Type --}}
                                    <td>

                                        @switch($movement->type)

                                            @case('in')
                                                <span class="badge bg-success">
                                                    <i class="bi bi-arrow-down-circle me-1"></i>
                                                    Entrée
                                                </span>
                                                @break

                                            @case('out')
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-arrow-up-circle me-1"></i>
                                                    Sortie
                                                </span>
                                                @break

                                            @case('adjustment')
                                                <span class="badge bg-primary">
                                                    <i class="bi bi-sliders me-1"></i>
                                                    Ajustement
                                                </span>
                                                @break

                                            @case('loss')
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bi bi-exclamation-octagon me-1"></i>
                                                    Perte
                                                </span>
                                                @break

                                            @case('return')
                                                <span class="badge bg-info text-dark">
                                                    <i class="bi bi-arrow-return-left me-1"></i>
                                                    Retour
                                                </span>
                                                @break

                                            @default
                                                <span class="badge bg-secondary">
                                                    {{ ucfirst($movement->type) }}
                                                </span>

                                        @endswitch

                                    </td>

                                    {{-- Quantité --}}
                                    <td>
                                        @if(in_array($movement->type, ['in', 'return']))
                                            <span class="text-success fw-semibold">
                                                +{{ number_format(abs($movement->quantity), 0, ',', ' ') }}
                                            </span>

                                        @elseif(in_array($movement->type, ['out', 'loss']))
                                            <span class="text-danger fw-semibold">
                                                -{{ number_format(abs($movement->quantity), 0, ',', ' ') }}
                                            </span>

                                        @else
                                            <span class="fw-semibold">
                                                {{ $movement->quantity > 0 ? '+' : '' }}
                                                {{ number_format($movement->quantity, 0, ',', ' ') }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Description --}}
                                    <td>
                                        {{ $movement->description ?: '—' }}
                                    </td>

                                    {{-- Origine --}}
                                    <td>

                                        @if($movement->isFromSale())

                                            @if($movement->source)
                                                <a href="{{ route('sales.show', $movement->source->id) }}"
                                                   class="text-decoration-none">
                                                    <span class="badge bg-danger-subtle text-danger">
                                                        <i class="bi bi-cart-check me-1"></i>
                                                        Vente
                                                    </span>
                                                </a>
                                            @else
                                                <span class="badge bg-secondary">
                                                    Vente supprimée
                                                </span>
                                            @endif

                                        @elseif($movement->isFromPurchase())

                                            @if($movement->source)
                                                <a href="{{ route('purchases.show', $movement->source->id) }}"
                                                   class="text-decoration-none">
                                                    <span class="badge bg-success-subtle text-success">
                                                        <i class="bi bi-truck me-1"></i>
                                                        Achat
                                                    </span>
                                                </a>
                                            @else
                                                <span class="badge bg-secondary">
                                                    Achat supprimé
                                                </span>
                                            @endif

                                        @elseif($movement->source_type === \App\Models\Product::class)

                                            @if($movement->source)

                                                <a href="{{ route('products.show', $movement->source->id) }}"
                                                   class="text-decoration-none">
                                                    <span class="badge bg-info-subtle text-info">
                                                        <i class="bi bi-box-seam me-1"></i>
                                                        Stock initial
                                                    </span>
                                                </a>

                                            @else

                                                <span class="badge bg-secondary">
                                                    Produit supprimé
                                                </span>

                                            @endif

                                        @elseif($movement->isManual())

                                            <span class="badge bg-primary-subtle text-primary">
                                                <i class="bi bi-person me-1"></i>
                                                Manuel
                                            </span>

                                        @else

                                            <span class="badge bg-secondary">
                                                <i class="bi bi-question-circle me-1"></i>
                                                Origine inconnue
                                            </span>

                                        @endif

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>
                </div>

            @else

                <div class="text-center py-5">

                    <div class="fs-1 text-muted mb-3">
                        <i class="bi bi-clock-history"></i>
                    </div>

                    <h5 class="mb-2">
                        Aucun mouvement enregistré
                    </h5>

                    <p class="text-muted mb-0">
                        Aucun mouvement de stock n'est disponible pour ce produit.
                    </p>

                </div>

            @endif

        </div>

        {{-- Pagination --}}
        @if($stockMovements->hasPages())
            <div class="card-footer bg-white">
                {{ $stockMovements->links() }}
            </div>
        @endif

    </div>

    {{-- Zone de suppression --}}
    @can('delete', $product)

        <div class="card border-danger shadow-sm mt-4">

            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Zone dangereuse
                </h5>
            </div>

            <div class="card-body">

                <p class="mb-3">
                    La suppression du produit est uniquement possible s'il
                    n'est utilisé dans aucun historique de stock, aucune vente
                    et aucun achat.
                </p>

                <form action="{{ route('products.destroy', $product) }}"
                      method="POST"
                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ? Cette action est irréversible.');">

                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>
                        Supprimer le produit
                    </button>

                </form>

            </div>

        </div>

    @endcan

</div>

@endsection
