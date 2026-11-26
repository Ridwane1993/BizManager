@extends('layouts.app')

@section('title', 'Achat ' . $purchase->purchase_number)

@section('content')

<div class="container-fluid py-4">

    {{-- ==================================================
        EN-TÊTE
    ================================================== --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="h3 mb-1">
                Achat {{ $purchase->purchase_number }}
            </h1>

            <p class="text-muted mb-0">
                Détails de l'achat et produits reçus.
            </p>

        </div>

        <div class="d-flex gap-2">

            @can('update', $purchase)

                @if ($purchase->status === 'pending')

                    <form
                        action="{{ route('purchases.receive', $purchase) }}"
                        method="POST"
                        class="d-inline"
                        onsubmit="return confirm('Réceptionner cet achat ? Le stock sera mis à jour.');"
                    >
                        @csrf

                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i>
                            Réceptionner
                        </button>
                    </form>

                @endif

                @if ($purchase->status !== 'cancelled')

                    <form
                        action="{{ route('purchases.cancel', $purchase) }}"
                        method="POST"
                        class="d-inline"
                        onsubmit="return confirm('Annuler cet achat ? Le stock sera décrémenté s\'il était réceptionné.');"
                    >
                        @csrf

                        <button type="submit" class="btn btn-outline-warning">
                            <i class="bi bi-x-circle me-1"></i>
                            Annuler
                        </button>
                    </form>

                @endif

            @endcan

            {{-- Modifier --}}
            @can('update', $purchase)

                <a href="{{ route('purchases.edit', $purchase) }}"
                    class="btn btn-outline-primary">

                    <i class="bi bi-pencil me-1"></i>

                    Modifier

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
                        class="btn btn-outline-danger">

                        <i class="bi bi-trash me-1"></i>

                        Supprimer

                    </button>

                </form>

            @endcan

            {{-- Retour --}}
            <a href="{{ route('purchases.index') }}"
                class="btn btn-outline-secondary">

                <i class="bi bi-arrow-left me-1"></i>

                Retour

            </a>

        </div>

    </div>

    {{-- ==================================================
        INFORMATIONS GÉNÉRALES
    ================================================== --}}
    <div class="row g-4 mb-4">

        {{-- ==================================================
            ACHAT
        ================================================== --}}
        <div class="col-lg-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white">

                    <h5 class="mb-0">
                        Achat
                    </h5>

                </div>

                <div class="card-body">

                    {{-- Numéro --}}
                    <div class="mb-3">

                        <small class="text-muted d-block">
                            Numéro
                        </small>

                        <strong>
                            {{ $purchase->purchase_number }}
                        </strong>

                    </div>

                    {{-- Date --}}
                    <div class="mb-3">

                        <small class="text-muted d-block">
                            Date
                        </small>

                        {{ $purchase->purchase_date->format('d/m/Y') }}

                    </div>

                    {{-- Statut --}}
                    <div>

                        <small class="text-muted d-block mb-1">
                            Statut
                        </small>

                        @if ($purchase->status === 'received')

                            <span class="badge bg-success">
                                Reçu
                            </span>

                        @elseif ($purchase->status === 'pending')

                            <span class="badge bg-warning text-dark">
                                En attente
                            </span>

                        @elseif ($purchase->status === 'cancelled')

                            <span class="badge bg-danger">
                                Annulé
                            </span>

                        @else

                            <span class="badge bg-secondary">
                                {{ $purchase->status }}
                            </span>

                        @endif

                    </div>

                </div>

            </div>

        </div>

        {{-- ==================================================
            FOURNISSEUR
        ================================================== --}}
        <div class="col-lg-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white">

                    <h5 class="mb-0">
                        Fournisseur
                    </h5>

                </div>

                <div class="card-body">

                    @if ($purchase->supplier)

                        <h5 class="mb-3">
                            {{ $purchase->supplier->name }}
                        </h5>

                        {{-- Email --}}
                        <div class="mb-3">

                            <small class="text-muted d-block">
                                Email
                            </small>

                            {{ $purchase->supplier->email ?? '—' }}

                        </div>

                        {{-- Téléphone --}}
                        <div>

                            <small class="text-muted d-block">
                                Téléphone
                            </small>

                            {{ $purchase->supplier->phone ?? '—' }}

                        </div>

                    @else

                        <div class="text-muted">

                            <i class="bi bi-exclamation-circle me-1"></i>

                            Fournisseur supprimé.

                        </div>

                    @endif

                </div>

            </div>

        </div>

        {{-- ==================================================
            TOTAL
        ================================================== --}}
        <div class="col-lg-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white">

                    <h5 class="mb-0">
                        Total
                    </h5>

                </div>

                <div class="card-body text-center d-flex flex-column justify-content-center">

                    <small class="text-muted">
                        Montant total
                    </small>

                    <h2 class="mt-2 mb-0">

                        {{ number_format(
                            $purchase->total_amount,
                            0,
                            ',',
                            ' '
                        ) }}

                        FCFA

                    </h2>

                </div>

            </div>

        </div>

    </div>

    {{-- ==================================================
        PRODUITS
    ================================================== --}}
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white">

            <h5 class="mb-0">
                Produits achetés
            </h5>

        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th class="px-4">
                                Produit
                            </th>

                            <th>
                                Référence
                            </th>

                            <th>
                                Quantité
                            </th>

                            <th>
                                Prix unitaire
                            </th>

                            <th class="text-end px-4">
                                Sous-total
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($purchase->items as $item)

                            <tr>

                                {{-- Produit --}}
                                <td class="px-4">

                                    @if ($item->product)

                                        <strong>
                                            {{ $item->product->name }}
                                        </strong>

                                    @else

                                        <span class="text-muted">
                                            Produit supprimé
                                        </span>

                                    @endif

                                </td>

                                {{-- Référence --}}
                                <td>

                                    @if ($item->product)

                                        {{ $item->product->reference }}

                                    @else

                                        —

                                    @endif

                                </td>

                                {{-- Quantité --}}
                                <td>

                                    <span class="fw-semibold">
                                        {{ $item->quantity }}
                                    </span>

                                </td>

                                {{-- Prix unitaire --}}
                                <td>

                                    {{ number_format(
                                        $item->unit_price,
                                        0,
                                        ',',
                                        ' '
                                    ) }}

                                    FCFA

                                </td>

                                {{-- Sous-total --}}
                                <td class="text-end px-4">

                                    <strong>

                                        {{ number_format(
                                            $item->subtotal,
                                            0,
                                            ',',
                                            ' '
                                        ) }}

                                        FCFA

                                    </strong>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="5"
                                    class="text-center py-5 text-muted">

                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>

                                    Aucun produit dans cet achat.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                    {{-- ==================================================
                        TOTAL
                    ================================================== --}}
                    <tfoot>

                        <tr>

                            <td colspan="4"
                                class="text-end fw-bold">

                                Total

                            </td>

                            <td class="text-end px-4 fw-bold">

                                {{ number_format(
                                    $purchase->total_amount,
                                    0,
                                    ',',
                                    ' '
                                ) }}

                                FCFA

                            </td>

                        </tr>

                    </tfoot>

                </table>

            </div>

        </div>

    </div>

    {{-- ==================================================
        NOTES
    ================================================== --}}
    @if ($purchase->notes)

        <div class="card border-0 shadow-sm mt-4">

            <div class="card-header bg-white">

                <h5 class="mb-0">
                    Notes
                </h5>

            </div>

            <div class="card-body">

                <div class="text-muted">

                    {!! nl2br(e($purchase->notes)) !!}

                </div>

            </div>

        </div>

    @endif

</div>

@endsection
