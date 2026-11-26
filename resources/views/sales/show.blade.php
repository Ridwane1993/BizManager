@extends('layouts.app')

@section('title', 'Vente ' . $sale->sale_number)

@section('content')

<div class="container-fluid py-4">

    {{-- ==================================================
        EN-TÊTE
    ================================================== --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="h3 mb-1">
                Détail de la vente
            </h1>

            <p class="text-muted mb-0">
                Informations complètes de la vente.
            </p>

        </div>

        <div class="d-flex gap-2">

            @can('update', $sale)

                @if ($sale->status === 'pending')

                    <form
                        action="{{ route('sales.complete', $sale) }}"
                        method="POST"
                        class="d-inline"
                        onsubmit="return confirm('Valider cette vente ? Le stock sera décrémenté.');"
                    >
                        @csrf

                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i>
                            Valider la vente
                        </button>
                    </form>

                @endif

                @if ($sale->status !== 'cancelled')

                    <form
                        action="{{ route('sales.cancel', $sale) }}"
                        method="POST"
                        class="d-inline"
                        onsubmit="return confirm('Annuler cette vente ? Le stock sera restauré si elle était validée.');"
                    >
                        @csrf

                        <button type="submit" class="btn btn-outline-warning">
                            <i class="bi bi-x-circle me-1"></i>
                            Annuler
                        </button>
                    </form>

                @endif

            @endcan

            @if ($sale->status === 'completed')
                @if ($sale->invoice)
                    <a
                        href="{{ route('invoices.show', $sale->invoice) }}"
                        class="btn btn-outline-success"
                    >
                        <i class="bi bi-receipt me-1"></i>
                        Voir la facture
                    </a>
                @else
                    <a
                        href="{{ route('sales.invoices.create', $sale) }}"
                        class="btn btn-outline-success"
                    >
                        <i class="bi bi-receipt me-1"></i>
                        Créer une facture
                    </a>
                @endif
            @endif

            {{-- Modifier --}}
            @can('update', $sale)

                <a
                    href="{{ route('sales.edit', $sale) }}"
                    class="btn btn-primary"
                >

                    <i class="bi bi-pencil me-1"></i>

                    Modifier

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
                        class="btn btn-outline-danger"
                    >

                        <i class="bi bi-trash me-1"></i>

                        Supprimer

                    </button>

                </form>

            @endcan

            {{-- Retour --}}
            <a
                href="{{ route('sales.index') }}"
                class="btn btn-outline-secondary"
            >

                <i class="bi bi-arrow-left me-1"></i>

                Retour

            </a>

        </div>

    </div>

    {{-- ==================================================
        INFORMATIONS GÉNÉRALES
    ================================================== --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <div class="row g-4">

                {{-- Numéro --}}
                <div class="col-12 col-md-3">

                    <div class="text-muted small">
                        N° de vente
                    </div>

                    <div class="fw-semibold">
                        {{ $sale->sale_number }}
                    </div>

                </div>

                {{-- Client --}}
                <div class="col-12 col-md-3">

                    <div class="text-muted small">
                        Client
                    </div>

                    <div class="fw-semibold">
                        {{ $sale->client->name ?? 'Client supprimé' }}
                    </div>

                </div>

                {{-- Date --}}
                <div class="col-12 col-md-3">

                    <div class="text-muted small">
                        Date
                    </div>

                    <div class="fw-semibold">
                        {{ $sale->sale_date->format('d/m/Y') }}
                    </div>

                </div>

                {{-- Statut --}}
                <div class="col-12 col-md-3">

                    <div class="text-muted small">
                        Statut
                    </div>

                    <x-status-badge :status="$sale->status" type="sale" />

                </div>

            </div>

        </div>

    </div>

    {{-- ==================================================
        PRODUITS
    ================================================== --}}
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white py-3">

            <h5 class="mb-0">

                <i class="bi bi-cart3 me-2"></i>

                Produits vendus

            </h5>

        </div>

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

                        <th class="text-center">
                            Quantité
                        </th>

                        <th class="text-end">
                            Prix unitaire
                        </th>

                        <th class="text-end px-4">
                            Sous-total
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse ($sale->items as $item)

                        <tr>

                            {{-- Produit --}}
                            <td class="px-4">

                                <div class="fw-semibold">

                                    {{ $item->product->name ?? 'Produit supprimé' }}

                                </div>

                            </td>

                            {{-- Référence --}}
                            <td>

                                {{ $item->product->reference ?? '—' }}

                            </td>

                            {{-- Quantité --}}
                            <td class="text-center">

                                {{ $item->quantity }}

                            </td>

                            {{-- Prix unitaire --}}
                            <td class="text-end">

                                {{ number_format(
                                    $item->unit_price,
                                    0,
                                    ',',
                                    ' '
                                ) }}

                                FCFA

                            </td>

                            {{-- Sous-total --}}
                            <td class="text-end px-4 fw-semibold">

                                {{ number_format(
                                    $item->subtotal,
                                    0,
                                    ',',
                                    ' '
                                ) }}

                                FCFA

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="5"
                                class="text-center py-5 text-muted"
                            >

                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>

                                Aucun produit dans cette vente.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

                {{-- ==================================================
                    TOTAL
                ================================================== --}}
                <tfoot>

                    <tr>

                        <th
                            colspan="4"
                            class="text-end"
                        >
                            Total
                        </th>

                        <th
                            class="text-end fs-5 px-4"
                        >

                            {{ number_format(
                                $sale->total_amount,
                                0,
                                ',',
                                ' '
                            ) }}

                            FCFA

                        </th>

                    </tr>

                </tfoot>

            </table>

        </div>

    </div>

    {{-- ==================================================
        NOTES
    ================================================== --}}
    @if ($sale->notes)

        <div class="card border-0 shadow-sm mt-4">

            <div class="card-body">

                <h6 class="fw-semibold">

                    <i class="bi bi-chat-left-text me-2"></i>

                    Notes

                </h6>

                <div class="text-muted">

                    {!! nl2br(e($sale->notes)) !!}

                </div>

            </div>

        </div>

    @endif

</div>

@endsection
