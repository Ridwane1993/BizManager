@extends('layouts.app')

@section('title', 'Créer une facture')

@section('content')
@php
    $invoiceSubtotal = $sale->items->sum(
        fn ($item) => round((float) $item->unit_price * (int) $item->quantity, 2)
    );
@endphp

<div class="container-fluid py-4">
    <div class="mb-4">
        <a href="{{ route('sales.show', $sale) }}" class="btn btn-link px-0 text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i>
            Retour à la vente
        </a>
        <h1 class="h3 mb-1">Créer une facture</h1>
        <p class="text-muted mb-0">
            Vente {{ $sale->sale_number }} — {{ $sale->sale_date->format('d/m/Y') }}
        </p>
    </div>

    <x-validation-errors message="La facture n’a pas pu être créée :" />

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <section class="card border-0 shadow-sm mb-4" aria-labelledby="invoice-client-heading">
                <div class="card-body">
                    <h2 id="invoice-client-heading" class="h5 mb-3">Facturé à</h2>
                    <div class="fw-semibold">{{ $sale->client->name }}</div>
                    @if ($sale->client->email)
                        <div class="text-muted">{{ $sale->client->email }}</div>
                    @endif
                    @if ($sale->client->phone)
                        <div class="text-muted">{{ $sale->client->phone }}</div>
                    @endif
                    @if ($sale->client->address)
                        <div class="text-muted">{!! nl2br(e($sale->client->address)) !!}</div>
                    @endif
                </div>
            </section>

            <section class="card border-0 shadow-sm" aria-labelledby="invoice-items-heading">
                <div class="card-header bg-white py-3">
                    <h2 id="invoice-items-heading" class="h5 mb-0">Produits de la vente</h2>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3 px-md-4">Produit</th>
                                <th class="text-end">Qté</th>
                                <th class="text-end">Prix unitaire</th>
                                <th class="text-end px-3 px-md-4">Sous-total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($sale->items as $item)
                                @php
                                    $lineSubtotal = round((float) $item->unit_price * (int) $item->quantity, 2);
                                @endphp
                                <tr>
                                    <td class="px-3 px-md-4">
                                        <div class="fw-semibold">{{ $item->product->name ?? 'Produit indisponible' }}</div>
                                        <small class="text-muted">{{ $item->product->reference ?? '—' }}</small>
                                    </td>
                                    <td class="text-end">{{ $item->quantity }}</td>
                                    <td class="text-end">{{ number_format($item->unit_price, 2, ',', ' ') }} FCFA</td>
                                    <td class="text-end px-3 px-md-4">{{ number_format($lineSubtotal, 2, ',', ' ') }} FCFA</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        Aucun produit dans cette vente.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="col-12 col-xl-4">
            <section class="card border-0 shadow-sm" aria-labelledby="invoice-summary-heading">
                <div class="card-body">
                    <h2 id="invoice-summary-heading" class="h5 mb-3">Récapitulatif</h2>
                    <p class="small text-muted mb-4">
                        La facture conservera un instantané du client, des produits et des prix.
                    </p>

                    <form method="POST" action="{{ route('sales.invoices.store', $sale) }}">
                        @csrf
                        <label for="tax_rate" class="form-label">Taux de taxe (%)</label>
                        <input
                            type="number"
                            id="tax_rate"
                            name="tax_rate"
                            class="form-control @error('tax_rate') is-invalid @enderror"
                            value="{{ old('tax_rate', '0.00') }}"
                            min="0"
                            max="100"
                            step="0.01"
                            inputmode="decimal"
                            aria-describedby="tax-rate-help"
                            required
                        >
                        <div id="tax-rate-help" class="form-text mb-3">
                            Saisis un taux entre 0 et 100 %. Le montant final est recalculé côté serveur.
                        </div>
                        @error('tax_rate')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror

                        <label for="due_date" class="form-label mt-3">Date d’échéance</label>
                        <input
                            type="date"
                            id="due_date"
                            name="due_date"
                            class="form-control @error('due_date') is-invalid @enderror"
                            value="{{ old('due_date', $defaultDueDate) }}"
                            min="{{ now()->toDateString() }}"
                            aria-describedby="due-date-help"
                        >
                        <div id="due-date-help" class="form-text mb-3">
                            Laissez vide pour appliquer le délai par défaut de {{ config('invoices.default_due_days', 30) }} jours.
                        </div>
                        @error('due_date')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror

                        <div
                            class="border-top pt-3"
                            data-invoice-summary
                            data-subtotal="{{ number_format($invoiceSubtotal, 2, '.', '') }}"
                        >
                            <div class="d-flex justify-content-between mb-2">
                                <span>Sous-total</span>
                                <span id="invoiceSubtotal">{{ number_format($invoiceSubtotal, 2, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Taxe estimée</span>
                                <span id="invoiceTaxAmount" data-invoice-tax-amount>0,00 FCFA</span>
                            </div>
                            <div class="d-flex justify-content-between border-top pt-3 mt-3 fw-bold fs-5">
                                <span>Total estimé</span>
                                <span id="invoiceTotal">—</span>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-receipt me-1"></i>
                                Émettre la facture
                            </button>
                            <a href="{{ route('sales.show', $sale) }}" class="btn btn-outline-secondary">
                                Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
</div>

@endsection
