@extends('layouts.app')

@section('title', 'Rapport d’activité')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h1 class="h3 mb-1">Rapport d’activité</h1>
        <p class="text-muted mb-0">
            Consultez séparément les ventes terminées, achats reçus et dépenses.
        </p>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-sm-6 col-lg-4">
                        <label for="start_date" class="form-label">Date de début</label>
                        <input
                            type="date"
                            id="start_date"
                            name="start_date"
                            value="{{ old('start_date', $startDate) }}"
                            max="{{ $endDate }}"
                            class="form-control @error('start_date') is-invalid @enderror"
                            required
                        >
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <label for="end_date" class="form-label">Date de fin</label>
                        <input
                            type="date"
                            id="end_date"
                            name="end_date"
                            value="{{ old('end_date', $endDate) }}"
                            min="{{ $startDate }}"
                            class="form-control @error('end_date') is-invalid @enderror"
                            required
                        >
                        @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-lg-4 d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel me-1"></i>
                            Appliquer
                        </button>
                        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
                            Période du mois
                        </a>
                        <a
                            href="{{ route('reports.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                            class="btn btn-outline-success"
                        >
                            <i class="bi bi-download me-1" aria-hidden="true"></i>
                            Exporter CSV
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <p class="text-muted mb-3">
        Période du {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
        au {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
    </p>

    <div class="row g-3">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <h2 class="h6 text-muted">Ventes terminées</h2>
                            <div class="h3 mb-1">
                                {{ number_format((float) $salesSummary->total_amount, 0, ',', ' ') }}
                                FCFA
                            </div>
                            <p class="mb-0 text-muted">
                                {{ (int) $salesSummary->record_count }}
                                transaction{{ (int) $salesSummary->record_count === 1 ? '' : 's' }}
                            </p>
                        </div>
                        <i class="bi bi-cart-check fs-3 text-primary" aria-hidden="true"></i>
                    </div>
                    <p class="small text-muted mt-3 mb-0">
                        Seules les ventes au statut terminé sont comptabilisées.
                    </p>
                    <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-primary mt-3">
                        Consulter les ventes
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <h2 class="h6 text-muted">Achats reçus</h2>
                            <div class="h3 mb-1">
                                {{ number_format((float) $purchaseSummary->total_amount, 0, ',', ' ') }}
                                FCFA
                            </div>
                            <p class="mb-0 text-muted">
                                {{ (int) $purchaseSummary->record_count }}
                                achat{{ (int) $purchaseSummary->record_count === 1 ? '' : 's' }}
                            </p>
                        </div>
                        <i class="bi bi-box-seam fs-3 text-success" aria-hidden="true"></i>
                    </div>
                    <p class="small text-muted mt-3 mb-0">
                        Seuls les achats réceptionnés sont comptabilisés.
                    </p>
                    <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-outline-primary mt-3">
                        Consulter les achats
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <h2 class="h6 text-muted">Dépenses enregistrées</h2>
                            <div class="h3 mb-1">
                                {{ number_format((float) $expenseSummary->total_amount, 0, ',', ' ') }}
                                FCFA
                            </div>
                            <p class="mb-0 text-muted">
                                {{ (int) $expenseSummary->record_count }}
                                dépense{{ (int) $expenseSummary->record_count === 1 ? '' : 's' }}
                            </p>
                        </div>
                        <i class="bi bi-wallet2 fs-3 text-warning" aria-hidden="true"></i>
                    </div>
                    <p class="small text-muted mt-3 mb-0">
                        Toutes les dépenses de la période sont comptabilisées.
                    </p>
                    <a href="{{ route('expenses.index') }}" class="btn btn-sm btn-outline-primary mt-3">
                        Consulter les dépenses
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
