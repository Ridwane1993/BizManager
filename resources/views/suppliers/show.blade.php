@extends('layouts.app')

@section('title', $supplier->name)

@section('content')

<div class="container-fluid py-4">

    {{-- ==================================================
        EN-TÊTE
    ================================================== --}}

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="h3 mb-1">
                {{ $supplier->name }}
            </h1>

            <p class="text-muted mb-0">
                Détails du fournisseur.
            </p>

        </div>

        <div class="d-flex gap-2">

            {{-- Modifier --}}
            @can('update', $supplier)

                <a
                    href="{{ route('suppliers.edit', $supplier) }}"
                    class="btn btn-outline-primary"
                >

                    <i class="bi bi-pencil me-1"></i>

                    Modifier

                </a>

            @endcan

            {{-- Supprimer --}}
            @can('delete', $supplier)

                @if ($supplier->purchases_count === 0)

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
                            class="btn btn-outline-danger"
                        >

                            <i class="bi bi-trash me-1"></i>

                            Supprimer

                        </button>

                    </form>

                @endif

            @endcan

            {{-- Retour --}}
            <a
                href="{{ route('suppliers.index') }}"
                class="btn btn-outline-secondary"
            >

                <i class="bi bi-arrow-left me-1"></i>

                Retour

            </a>

        </div>

    </div>

    {{-- ==================================================
        INFORMATIONS
    ================================================== --}}

    <div class="row g-4 mb-4">

        {{-- Informations fournisseur --}}
        <div class="col-lg-5">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white">

                    <h5 class="mb-0">
                        Informations
                    </h5>

                </div>

                <div class="card-body">

                    {{-- Nom --}}
                    <div class="mb-3">

                        <small class="text-muted d-block">
                            Nom
                        </small>

                        <strong>
                            {{ $supplier->name }}
                        </strong>

                    </div>

                    {{-- Email --}}
                    <div class="mb-3">

                        <small class="text-muted d-block">
                            Email
                        </small>

                        {{ $supplier->email ?? '—' }}

                    </div>

                    {{-- Téléphone --}}
                    <div class="mb-3">

                        <small class="text-muted d-block">
                            Téléphone
                        </small>

                        {{ $supplier->phone ?? '—' }}

                    </div>

                    {{-- Adresse --}}
                    <div class="mb-3">

                        <small class="text-muted d-block">
                            Adresse
                        </small>

                        {!! $supplier->address
                            ? nl2br(e($supplier->address))
                            : '—'
                        !!}

                    </div>

                    {{-- Notes --}}
                    <div>

                        <small class="text-muted d-block">
                            Notes
                        </small>

                        {!! $supplier->notes
                            ? nl2br(e($supplier->notes))
                            : '—'
                        !!}

                    </div>

                </div>

            </div>

        </div>

        {{-- Résumé --}}
        <div class="col-lg-7">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white">

                    <h5 class="mb-0">
                        Résumé
                    </h5>

                </div>

                <div class="card-body">

                    <div class="row g-4">

                        <div class="col-md-6">

                            <small class="text-muted d-block mb-1">
                                Nombre d'achats
                            </small>

                            <h2 class="mb-0 text-primary">

                                {{ $supplier->purchases()->count() }}

                            </h2>

                        </div>

                        <div class="col-md-6">

                            <small class="text-muted d-block mb-1">
                                Dernier achat
                            </small>

                            @php
                                $lastPurchase = $supplier->purchases()
                                    ->latest('purchase_date')
                                    ->first();
                            @endphp

                            @if ($lastPurchase)

                                <div class="fw-semibold">
                                    {{ $lastPurchase->purchase_number }}
                                </div>

                                <small class="text-muted">
                                    {{ $lastPurchase->purchase_date->format('d/m/Y') }}
                                </small>

                            @else

                                <span class="text-muted">
                                    Aucun achat
                                </span>

                            @endif

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- ==================================================
        HISTORIQUE DES ACHATS
    ================================================== --}}

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white">

            <h5 class="mb-0">
                Historique des achats
            </h5>

        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th class="px-4">
                                Numéro
                            </th>

                            <th>
                                Date
                            </th>

                            <th>
                                Montant
                            </th>

                            <th>
                                Statut
                            </th>

                            <th class="text-end px-4">
                                Action
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

                                {{-- Date --}}
                                <td>

                                    {{ $purchase->purchase_date->format('d/m/Y') }}

                                </td>

                                {{-- Montant --}}
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

                                </td>

                                {{-- Action --}}
                                <td class="text-end px-4">

                                    <a
                                        href="{{ route('purchases.show', $purchase) }}"
                                        class="btn btn-sm btn-outline-primary"
                                        title="Voir l'achat"
                                    >

                                        <i class="bi bi-eye"></i>

                                    </a>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="5"
                                    class="text-center py-5 text-muted"
                                >

                                    <i class="bi bi-cart-x fs-2 d-block mb-2"></i>

                                    Aucun achat pour ce fournisseur.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        {{-- Pagination des achats --}}
        @if ($purchases->hasPages())

            <div class="card-footer">

                {{ $purchases->links() }}

            </div>

        @endif

    </div>

</div>

@endsection
