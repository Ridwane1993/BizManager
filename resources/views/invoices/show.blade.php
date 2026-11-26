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
            <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-outline-danger">
                <i class="bi bi-file-earmark-pdf me-1"></i> PDF serveur
            </a>
            @can('update', $invoice)
                @if ($invoice->client_email)
                    <form method="POST" action="{{ route('invoices.send', $invoice) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-info">
                            <i class="bi bi-envelope me-1"></i> Envoyer par e-mail
                        </button>
                    </form>
                @endif
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
                <i class="bi bi-printer me-1"></i> Imprimer
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
                    @if ($invoice->due_date)
                        <div class="text-muted">Échéance : {{ $invoice->due_date->format('d/m/Y') }}</div>
                    @endif
                    @if ($invoice->sent_at)
                        <div class="text-muted">Envoyée le {{ $invoice->sent_at->format('d/m/Y H:i') }}</div>
                    @endif
                    <div class="mt-2">
                        @if ($invoice->payment_status === 'paid')
                            <span class="badge bg-success">Payée</span>
                        @elseif ($invoice->payment_status === 'partial')
                            <span class="badge bg-info text-dark">Partiellement payée</span>
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

            <section class="mt-4 invoice-actions">
                <h3 class="h6">Règlement</h3>
                <div class="mb-3">
                    <div>Déjà réglé : <strong>{{ number_format($invoice->paidAmount(), 2, ',', ' ') }} FCFA</strong></div>
                    <div>Reste dû : <strong>{{ number_format($invoice->remainingAmount(), 2, ',', ' ') }} FCFA</strong></div>
                </div>

                @if ($invoice->payments->isNotEmpty())
                    <div class="table-responsive mb-4">
                        <table class="table table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Note</th>
                                    <th class="text-end">Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoice->payments as $payment)
                                    <tr>
                                        <td>{{ $payment->paid_at->format('d/m/Y') }}</td>
                                        <td>{{ $payment->note ?: '—' }}</td>
                                        <td class="text-end">{{ number_format($payment->amount, 2, ',', ' ') }} FCFA</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @can('update', $invoice)
                    @if ($invoice->remainingAmount() > 0)
                        <form method="POST" action="{{ route('invoices.payments.store', $invoice) }}" class="row g-3">
                            @csrf
                            <div class="col-md-3">
                                <label for="amount" class="form-label">Montant</label>
                                <input
                                    type="number"
                                    id="amount"
                                    name="amount"
                                    class="form-control @error('amount') is-invalid @enderror"
                                    min="0.01"
                                    max="{{ $invoice->remainingAmount() }}"
                                    step="0.01"
                                    value="{{ old('amount', $invoice->remainingAmount()) }}"
                                    required
                                >
                                @error('amount')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="paid_at" class="form-label">Date du paiement</label>
                                <input
                                    type="date"
                                    id="paid_at"
                                    name="paid_at"
                                    class="form-control @error('paid_at') is-invalid @enderror"
                                    value="{{ old('paid_at', now()->toDateString()) }}"
                                    required
                                >
                                @error('paid_at')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="note" class="form-label">Note (facultatif)</label>
                                <input
                                    type="text"
                                    id="note"
                                    name="note"
                                    maxlength="255"
                                    class="form-control @error('note') is-invalid @enderror"
                                    value="{{ old('note') }}"
                                >
                                @error('note')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-cash me-1"></i> Enregistrer
                                </button>
                            </div>
                        </form>
                    @endif
                @endcan
            </section>
        </div>
    </article>
</div>
@endsection
