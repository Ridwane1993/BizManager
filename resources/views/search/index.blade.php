@extends('layouts.app')

@section('title', 'Recherche')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h1 class="h3 mb-1">Recherche globale</h1>
        @if ($term !== '')
            <p class="text-muted mb-0">Résultats pour « {{ $term }} »</p>
        @else
            <p class="text-muted mb-0">
                Recherchez des produits, clients, fournisseurs, ventes, achats, factures ou dépenses.
            </p>
        @endif
    </div>

    @if ($term === '')
        <div class="alert alert-info">Saisissez un terme dans la barre de recherche.</div>
    @else
        @php($hasResults = $products->isNotEmpty()
            || $clients->isNotEmpty()
            || $suppliers->isNotEmpty()
            || $sales->isNotEmpty()
            || $purchases->isNotEmpty()
            || $invoices->isNotEmpty()
            || $expenses->isNotEmpty())

        @if (!$hasResults)
            <div class="alert alert-secondary">Aucun résultat trouvé.</div>
        @endif

        @if ($products->isNotEmpty())
            <section class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Produits</div>
                <div class="list-group list-group-flush">
                    @foreach ($products as $product)
                        <a class="list-group-item list-group-item-action" href="{{ route('products.show', $product) }}">
                            {{ $product->name }} <span class="text-muted">({{ $product->reference }})</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($clients->isNotEmpty())
            <section class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Clients</div>
                <div class="list-group list-group-flush">
                    @foreach ($clients as $client)
                        <a class="list-group-item list-group-item-action" href="{{ route('clients.show', $client) }}">
                            {{ $client->name }} <span class="text-muted">{{ $client->email }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($suppliers->isNotEmpty())
            <section class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Fournisseurs</div>
                <div class="list-group list-group-flush">
                    @foreach ($suppliers as $supplier)
                        <a class="list-group-item list-group-item-action" href="{{ route('suppliers.show', $supplier) }}">
                            {{ $supplier->name }}
                            <span class="text-muted">{{ $supplier->email ?: $supplier->phone }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($sales->isNotEmpty())
            <section class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Ventes</div>
                <div class="list-group list-group-flush">
                    @foreach ($sales as $sale)
                        <a class="list-group-item list-group-item-action" href="{{ route('sales.show', $sale) }}">
                            {{ $sale->sale_number }} <span class="text-muted">{{ $sale->client->name ?? '' }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($purchases->isNotEmpty())
            <section class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Achats</div>
                <div class="list-group list-group-flush">
                    @foreach ($purchases as $purchase)
                        <a class="list-group-item list-group-item-action" href="{{ route('purchases.show', $purchase) }}">
                            {{ $purchase->purchase_number }}
                            <span class="text-muted">{{ $purchase->supplier->name ?? '' }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($invoices->isNotEmpty())
            <section class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Factures</div>
                <div class="list-group list-group-flush">
                    @foreach ($invoices as $invoice)
                        <a class="list-group-item list-group-item-action" href="{{ route('invoices.show', $invoice) }}">
                            {{ $invoice->invoice_number }} <span class="text-muted">{{ $invoice->client_name }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($expenses->isNotEmpty())
            <section class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Dépenses</div>
                <div class="list-group list-group-flush">
                    @foreach ($expenses as $expense)
                        <a class="list-group-item list-group-item-action d-flex flex-column flex-sm-row justify-content-between gap-1" href="{{ route('expenses.show', $expense) }}">
                            <span>
                                {{ $expense->category }}
                                @if ($expense->description)
                                    <span class="text-muted">— {{ $expense->description }}</span>
                                @endif
                            </span>
                            <span class="text-nowrap">{{ number_format($expense->amount, 0, ',', ' ') }} FCFA</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    @endif
</div>
@endsection
