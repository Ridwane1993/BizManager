@extends('layouts.app')

@section('title', 'Détail du mouvement')

@section('content')

<div class="container-fluid py-4">

{{-- ==================================================
    EN-TÊTE
================================================== --}}

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h1 class="h3 mb-1">
            Détail du mouvement
        </h1>

        <p class="text-muted mb-0">
            Informations sur ce mouvement de stock.
        </p>

    </div>

    <div class="d-flex gap-2">

        {{-- Modifier --}}
        @can('update', $movement)

            <a
                href="{{ route('stock-movements.edit', $movement) }}"
                class="btn btn-warning"
            >

                <i class="bi bi-pencil me-1"></i>

                Modifier

            </a>

        @endcan

        {{-- Supprimer --}}
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
                    class="btn btn-danger"
                >

                    <i class="bi bi-trash me-1"></i>

                    Supprimer

                </button>

            </form>

        @endcan

        {{-- Retour --}}
        <a
            href="{{ route('stock-movements.index') }}"
            class="btn btn-outline-secondary"
        >

            <i class="bi bi-arrow-left me-1"></i>

            Retour

        </a>

    </div>

</div>

{{-- ==================================================
    MESSAGES
================================================== --}}

{{-- ==================================================
    CONTENU
================================================== --}}

<div class="row g-4">

    {{-- ==================================================
        INFORMATIONS
    ================================================== --}}

    <div class="col-lg-8">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-header bg-white">

                <h5 class="mb-0">
                    Informations du mouvement
                </h5>

            </div>

            <div class="card-body">

                <div class="row g-4">

                    {{-- PRODUIT --}}
                    <div class="col-md-6">

                        <small class="text-muted d-block mb-1">
                            Produit
                        </small>

                        @if ($movement->product)

                            <h5 class="mb-0">
                                {{ $movement->product->name }}
                            </h5>

                            <small class="text-muted">
                                Référence :
                                {{ $movement->product->reference }}
                            </small>

                        @else

                            <span class="text-muted">
                                Produit supprimé
                            </span>

                        @endif

                    </div>

                    {{-- TYPE --}}
                    <div class="col-md-6">

                        <small class="text-muted d-block mb-1">
                            Type
                        </small>

                        @if ($movement->type === 'in')

                            <span class="badge bg-success fs-6">

                                <i class="bi bi-box-arrow-in-down me-1"></i>

                                Entrée

                            </span>

                        @elseif ($movement->type === 'out')

                            <span class="badge bg-danger fs-6">

                                <i class="bi bi-box-arrow-up me-1"></i>

                                Sortie

                            </span>

                        @elseif ($movement->type === 'return')

                            <span class="badge bg-info text-dark fs-6">

                                <i class="bi bi-arrow-return-left me-1"></i>

                                Retour

                            </span>

                        @elseif ($movement->type === 'loss')

                            <span class="badge bg-dark fs-6">

                                <i class="bi bi-exclamation-triangle me-1"></i>

                                Perte

                            </span>

                        @elseif ($movement->type === 'adjustment')

                            <span class="badge bg-warning text-dark fs-6">

                                <i class="bi bi-sliders me-1"></i>

                                Correction

                            </span>

                        @else

                            <span class="badge bg-secondary fs-6">
                                {{ $movement->type }}
                            </span>

                        @endif

                    </div>

                    {{-- QUANTITÉ --}}
                    <div class="col-md-6">

                        <small class="text-muted d-block mb-1">
                            Quantité
                        </small>

                        @if (
                            in_array(
                                $movement->type,
                                ['in', 'return']
                            )
                        )

                            <h4 class="text-success mb-0">
                                +{{ abs($movement->quantity) }}
                            </h4>

                        @elseif ($movement->type === 'adjustment')

                            <h4
                                class="{{ $movement->quantity >= 0 ? 'text-success' : 'text-danger' }} mb-0"
                            >

                                {{ $movement->quantity >= 0 ? '+' : '' }}

                                {{ $movement->quantity }}

                            </h4>

                        @else

                            <h4 class="text-danger mb-0">
                                -{{ abs($movement->quantity) }}
                            </h4>

                        @endif

                    </div>

                    {{-- DATE --}}
                    <div class="col-md-6">

                        <small class="text-muted d-block mb-1">
                            Date du mouvement
                        </small>

                        <h6 class="mb-0">

                            {{ $movement->created_at->format('d/m/Y à H:i') }}

                        </h6>

                    </div>

                    {{-- ==================================================
                        ORIGINE
                    ================================================== --}}

                    <div class="col-12">

                        <small class="text-muted d-block mb-2">
                            Origine du mouvement
                        </small>

                        {{-- Vente --}}
                        @if ($movement->isFromSale())

                            @if ($movement->source)

                                <div
                                    class="d-flex justify-content-between align-items-center p-3 bg-light rounded"
                                >

                                    <div>

                                        <div class="fw-semibold">

                                            <i class="bi bi-receipt text-primary me-1"></i>

                                            Vente
                                            {{ $movement->source->sale_number }}

                                        </div>

                                        <small class="text-muted">

                                            Mouvement généré automatiquement
                                            par cette vente.

                                        </small>

                                    </div>

                                    <a
                                        href="{{ route('sales.show', $movement->source) }}"
                                        class="btn btn-sm btn-outline-primary"
                                    >

                                        <i class="bi bi-eye me-1"></i>

                                        Voir la vente

                                    </a>

                                </div>

                            @else

                                <div class="alert alert-secondary mb-0">

                                    <i class="bi bi-receipt me-1"></i>

                                    Cette vente n'existe plus.

                                </div>

                            @endif

                        {{-- Achat --}}
                        @elseif ($movement->isFromPurchase())

                            @if ($movement->source)

                                <div
                                    class="d-flex justify-content-between align-items-center p-3 bg-light rounded"
                                >

                                    <div>

                                        <div class="fw-semibold">

                                            <i class="bi bi-bag text-secondary me-1"></i>

                                            Achat
                                            {{ $movement->source->purchase_number }}

                                        </div>

                                        <small class="text-muted">

                                            Mouvement généré automatiquement
                                            par cet achat.

                                        </small>

                                    </div>

                                    <a
                                        href="{{ route('purchases.show', $movement->source) }}"
                                        class="btn btn-sm btn-outline-secondary"
                                    >

                                        <i class="bi bi-eye me-1"></i>

                                        Voir l'achat

                                    </a>

                                </div>

                            @else

                                <div class="alert alert-secondary mb-0">

                                    <i class="bi bi-bag me-1"></i>

                                    Cet achat n'existe plus.

                                </div>

                            @endif

                        {{-- Stock initial --}}
                        @elseif (
                            $movement->source_type === \App\Models\Product::class
                        )

                            @if ($movement->source)

                                <div
                                    class="d-flex justify-content-between align-items-center p-3 bg-light rounded"
                                >

                                    <div>

                                        <div class="fw-semibold">

                                            <i class="bi bi-box-seam text-success me-1"></i>

                                            Stock initial du produit

                                        </div>

                                        <small class="text-muted">

                                            Mouvement généré automatiquement
                                            lors de la création du produit
                                            {{ $movement->source->name }}.

                                        </small>

                                    </div>

                                    <a
                                        href="{{ route('products.show', $movement->source) }}"
                                        class="btn btn-sm btn-outline-success"
                                    >

                                        <i class="bi bi-eye me-1"></i>

                                        Voir le produit

                                    </a>

                                </div>

                            @else

                                <div class="alert alert-secondary mb-0">

                                    <i class="bi bi-box-seam me-1"></i>

                                    Le produit à l'origine de ce stock initial
                                    n'existe plus.

                                </div>

                            @endif

                        {{-- Mouvement manuel --}}
                        @elseif ($movement->isManual())

                            <div
                                class="d-flex align-items-center p-3 bg-light rounded"
                            >

                                <div>

                                    <div class="fw-semibold">

                                        <i class="bi bi-pencil text-dark me-1"></i>

                                        Mouvement manuel

                                    </div>

                                    <small class="text-muted">

                                        Mouvement créé manuellement
                                        depuis la gestion du stock.

                                    </small>

                                </div>

                            </div>

                        {{-- Origine inconnue --}}
                        @else

                            <div class="alert alert-warning mb-0">

                                <i class="bi bi-question-circle me-1"></i>

                                Origine du mouvement inconnue.

                            </div>

                        @endif

                    </div>

                    {{-- DESCRIPTION --}}
                    <div class="col-12">

                        <small class="text-muted d-block mb-2">
                            Description
                        </small>

                        <div class="p-3 bg-light rounded">

                            {{ $movement->description ?? 'Aucune description.' }}

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- ==================================================
        STOCK ACTUEL
    ================================================== --}}

    <div class="col-lg-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-header bg-white">

                <h5 class="mb-0">
                    État actuel du produit
                </h5>

            </div>

            <div class="card-body text-center">

                @if ($movement->product)

                    <small class="text-muted">
                        Stock actuel
                    </small>

                    <h1 class="display-5 fw-bold mt-2">

                        {{ $movement->product->stock }}

                    </h1>

                    <p class="text-muted mb-0">
                        unité(s) disponible(s)
                    </p>

                    <hr>

                    <a
                        href="{{ route('products.show', $movement->product) }}"
                        class="btn btn-outline-primary"
                    >

                        <i class="bi bi-box-seam me-1"></i>

                        Voir le produit

                    </a>

                @else

                    <div class="text-muted py-4">

                        <i class="bi bi-box-seam fs-1 d-block mb-2"></i>

                        Produit supprimé.

                    </div>

                @endif

            </div>

        </div>

    </div>

</div>
</div>

@endsection
