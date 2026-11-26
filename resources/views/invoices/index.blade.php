@extends('layouts.app')

@section('title', 'Factures')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Factures</h1>
            <p class="text-muted mb-0">Factures émises à partir de vos ventes.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('invoices.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-6">
                        <label for="search" class="form-label">Recherche</label>
                        <input
                            type="search"
                            id="search"
                            name="search"
                            value="{{ old('search', $filters['search']) }}"
                            maxlength="100"
                            class="form-control @error('search') is-invalid @enderror"
                            placeholder="Numéro de facture ou nom du client"
                        >
                        @error('search')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="payment_status" class="form-label">Statut de paiement</label>
                        <select
                            id="payment_status"
                            name="payment_status"
                            class="form-select @error('payment_status') is-invalid @enderror"
                        >
                            <option value="">Tous les statuts</option>
                            <option
                                value="unpaid"
                                @selected(old('payment_status', $filters['payment_status']) === 'unpaid')
                            >
                                Non payées
                            </option>
                            <option
                                value="paid"
                                @selected(old('payment_status', $filters['payment_status']) === 'paid')
                            >
                                Payées
                            </option>
                        </select>
                        @error('payment_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-md-2 d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1" aria-hidden="true"></i>
                            Filtrer
                        </button>
                        @if ($filters['search'] !== '' || $filters['payment_status'] !== '')
                            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                                Effacer
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4">Numéro</th>
                        <th>Client</th>
                        <th>Date d’émission</th>
                        <th class="text-end">Total</th>
                        <th>Statut</th>
                        <th class="text-end px-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td class="px-4 fw-semibold">{{ $invoice->invoice_number }}</td>
                            <td>{{ $invoice->client_name }}</td>
                            <td>{{ $invoice->issued_at->format('d/m/Y') }}</td>
                            <td class="text-end">{{ number_format($invoice->total_amount, 2, ',', ' ') }} FCFA</td>
                            <td>
                                @if ($invoice->payment_status === 'paid')
                                    <span class="badge bg-success">Payée</span>
                                @else
                                    <span class="badge bg-warning text-dark">Non payée</span>
                                @endif
                            </td>
                            <td class="text-end px-4">
                                <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary">
                                    Ouvrir
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                @if ($filters['search'] !== '' || $filters['payment_status'] !== '')
                                    Aucune facture ne correspond à ces filtres.
                                @else
                                    Aucune facture pour le moment.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($invoices->hasPages())
            <div class="card-footer bg-white">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
