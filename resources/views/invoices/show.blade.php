@extends('layouts.app')

@section('title', 'Facture ' . $invoice->invoice_number)

@section('content')
<style>
    @media print {
        .sidebar, .topbar, .invoice-actions, .alert { display: none !important; }
        .main-wrapper { width: 100% !important; margin: 0 !important; }
        .main-content { padding: 0 !important; }
        .invoice-sheet { box-shadow: none !important; border: 0 !important; }
        body { background: #fff !important; }
    }
</style>

<div class="container-fluid py-4">

    <div class="invoice-actions d-flex flex-wrap justify-content-between gap-2 mb-4">
        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Retour aux factures
        </a>
        <div class="d-flex flex-wrap gap-2">
            @can('update', $invoice)
                <form method="POST" action="{{ route('invoices.payment-status', $invoice) }}">
                    @csrf
                    @method('PATCH')
                    <input
                        type="hidden"
                        name="payment_status"
                        value="{{ $invoice->payment_status === 'paid' ? 'unpaid' : 'paid' }}"
                    >
                    <button type="submit" class="btn btn-outline-primary">
                        {{ $invoice->payment_status === 'paid' ? 'Marquer non payée' : 'Marquer payée' }}
                    </button>
                </form>
            @endcan
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Imprimer / enregistrer en PDF
            </button>
        </div>
    </div>

    <article class="invoice-sheet card border-0 shadow-sm">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex flex-wrap justify-content-between gap-4 mb-5">
                <div>
                    <h1 class="h3">{{ $invoice->company->name }}</h1>
                    @if ($invoice->company->address)
                        <div class="text-muted">{!! nl2br(e($invoice->company->address)) !!}</div>
                    @endif
                    @if ($invoice->company->email)
                        <div class="text-muted">{{ $invoice->company->email }}</div>
                    @endif
                    @if ($invoice->company->phone)
                        <div class="text-muted">{{ $invoice->company->phone }}</div>
                    @endif
                </div>
                <div class="text-md-end">
                    <h2 class="h4 text-uppercase">Facture</h2>
                    <div class="fw-semibold">{{ $invoice->invoice_number }}</div>
                    <div class="text-muted">Émise le {{ $invoice->issued_at->format('d/m/Y') }}</div>
                    <div class="mt-2">
                        @if ($invoice->payment_status === 'paid')
                            <span class="badge bg-success">Payée</span>
                        @else
                            <span class="badge bg-warning text-dark">Non payée</span>
                        @endif
                    </div>
                </div>
            </div>

            <section class="mb-4">
                <h3 class="h6 text-uppercase text-muted">Facturé à</h3>
                <div class="fw-semibold">{{ $invoice->client_name }}</div>
                @if ($invoice->client_address)
                    <div>{!! nl2br(e($invoice->client_address)) !!}</div>
                @endif
                @if ($invoice->client_email)
                    <div>{{ $invoice->client_email }}</div>
                @endif
                @if ($invoice->client_phone)
                    <div>{{ $invoice->client_phone }}</div>
                @endif
            </section>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Produit</th>
                            <th>Référence</th>
                            <th class="text-end">Qté</th>
                            <th class="text-end">Prix unitaire</th>
                            <th class="text-end">Sous-total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice->items as $item)
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->product_reference ?: '—' }}</td>
                                <td class="text-end">{{ $item->quantity }}</td>
                                <td class="text-end">{{ number_format($item->unit_price, 2, ',', ' ') }} FCFA</td>
                                <td class="text-end">{{ number_format($item->subtotal, 2, ',', ' ') }} FCFA</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">Sous-total</th>
                            <th class="text-end">{{ number_format($invoice->subtotal, 2, ',', ' ') }} FCFA</th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-end">Taxe ({{ number_format($invoice->tax_rate, 2, ',', ' ') }} %)</th>
                            <th class="text-end">{{ number_format($invoice->tax_amount, 2, ',', ' ') }} FCFA</th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-end fs-5">Total</th>
                            <th class="text-end fs-5">{{ number_format($invoice->total_amount, 2, ',', ' ') }} FCFA</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if ($invoice->notes)
                <section class="mt-4">
                    <h3 class="h6">Notes</h3>
                    <div>{!! nl2br(e($invoice->notes)) !!}</div>
                </section>
            @endif
        </div>
    </article>
</div>
@endsection
