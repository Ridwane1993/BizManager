@extends('layouts.app')

@section('title', 'Dashboard - BizManager')

@section('content')

<div class="container-fluid py-4">

    {{-- ==================================================
        EN-TÊTE
    ================================================== --}}

    <div class="dashboard-header">

        <div>

            <h1>
                Bonjour, {{ auth()->user()->name }} 👋
            </h1>

            <p>
                Voici ce qui se passe dans votre entreprise aujourd'hui.
            </p>

        </div>

        @can('create', \App\Models\Sale::class)

            <a
                href="{{ route('sales.create') }}"
                class="btn btn-primary"
            >

                <i class="bi bi-plus-lg me-1"></i>

                Nouvelle vente

            </a>

        @endcan

    </div>

    {{-- ==================================================
        STATISTIQUES
    ================================================== --}}

    <div class="row g-4 mb-4">

        {{-- VENTES --}}
        <div class="col-12 col-sm-6 col-xl">

            <div class="stat-card">

                <div class="stat-card-top">

                    <div class="stat-icon sales-icon">

                        <i class="bi bi-currency-dollar"></i>

                    </div>

                    <span class="stat-label">
                        Ventes
                    </span>

                </div>

                <h3>
                    {{ number_format($totalSales, 0, ',', ' ') }}
                    FCFA
                </h3>

                <div
                    class="stat-growth {{
                        $salesGrowth === null
                            ? ($currentMonthSales > 0 ? 'positive' : 'neutral')
                            : ($salesGrowth >= 0 ? 'positive' : 'negative')
                    }}"
                >

                    @if ($salesGrowth === null)

                        @if ($currentMonthSales > 0)
                            <i class="bi bi-arrow-up"></i>
                            Nouveau ce mois-ci
                        @else
                            <i class="bi bi-dash-circle"></i>
                            Aucune vente ce mois-ci
                        @endif

                    @elseif ($salesGrowth >= 0)

                        <i class="bi bi-arrow-up"></i>

                        {{ number_format($salesGrowth, 1, ',', ' ') }}%

                        <span>
                            vs mois dernier
                        </span>

                    @else

                        <i class="bi bi-arrow-down"></i>

                        {{ number_format(abs($salesGrowth), 1, ',', ' ') }}%

                        <span>
                            vs mois dernier
                        </span>

                    @endif

                </div>

            </div>

        </div>

        {{-- CLIENTS --}}
        <div class="col-12 col-sm-6 col-xl">

            <div class="stat-card">

                <div class="stat-card-top">

                    <div class="stat-icon customers-icon">

                        <i class="bi bi-people"></i>

                    </div>

                    <span class="stat-label">
                        Clients
                    </span>

                </div>

                <h3>
                    {{ number_format($totalClients, 0, ',', ' ') }}
                </h3>

                <div
                    class="stat-growth {{
                        $clientsGrowth === null
                            ? ($currentMonthClients > 0 ? 'positive' : 'neutral')
                            : ($clientsGrowth >= 0 ? 'positive' : 'negative')
                    }}"
                >

                    @if ($clientsGrowth === null)

                        @if ($currentMonthClients > 0)
                            <i class="bi bi-arrow-up"></i>
                            Nouveau ce mois-ci
                        @else
                            <i class="bi bi-dash-circle"></i>
                            Aucun nouveau client ce mois-ci
                        @endif

                    @elseif ($clientsGrowth >= 0)

                        <i class="bi bi-arrow-up"></i>

                        {{ number_format($clientsGrowth, 1, ',', ' ') }}%

                        <span>
                            vs mois dernier
                        </span>

                    @else

                        <i class="bi bi-arrow-down"></i>

                        {{ number_format(abs($clientsGrowth), 1, ',', ' ') }}%

                        <span>
                            vs mois dernier
                        </span>

                    @endif

                </div>

            </div>

        </div>

        {{-- PRODUITS --}}
        <div class="col-12 col-sm-6 col-xl">

            <div class="stat-card">

                <div class="stat-card-top">

                    <div class="stat-icon products-icon">

                        <i class="bi bi-box-seam"></i>

                    </div>

                    <span class="stat-label">
                        Produits
                    </span>

                </div>

                <h3>
                    {{ number_format($totalProducts, 0, ',', ' ') }}
                </h3>

                <div
                    class="stat-growth {{
                        $productsGrowth === null
                            ? ($currentMonthProducts > 0 ? 'positive' : 'neutral')
                            : ($productsGrowth >= 0 ? 'positive' : 'negative')
                    }}"
                >

                    @if ($productsGrowth === null)

                        @if ($currentMonthProducts > 0)
                            <i class="bi bi-arrow-up"></i>
                            Nouveau ce mois-ci
                        @else
                            <i class="bi bi-dash-circle"></i>
                            Aucun nouveau produit ce mois-ci
                        @endif

                    @elseif ($productsGrowth >= 0)

                        <i class="bi bi-arrow-up"></i>

                        {{ number_format($productsGrowth, 1, ',', ' ') }}%

                        <span>
                            vs mois dernier
                        </span>

                    @else

                        <i class="bi bi-arrow-down"></i>

                        {{ number_format(abs($productsGrowth), 1, ',', ' ') }}%

                        <span>
                            vs mois dernier
                        </span>

                    @endif

                </div>

            </div>

        </div>

        {{-- DÉPENSES --}}
        <div class="col-12 col-sm-6 col-xl">

            <div class="stat-card">

                <div class="stat-card-top">

                    <div class="stat-icon expenses-icon">

                        <i class="bi bi-wallet2"></i>

                    </div>

                    <span class="stat-label">
                        Dépenses du mois
                    </span>

                </div>

                <h3>

                    {{ number_format($totalExpenses, 0, ',', ' ') }}

                    FCFA

                </h3>

                <div
                    class="stat-growth {{
                        $expensesGrowth === null
                            ? ($currentMonthExpenses > 0 ? 'positive' : 'neutral')
                            : ($expensesGrowth >= 0 ? 'positive' : 'negative')
                    }}"
                >

                    @if ($expensesGrowth === null)

                        @if ($currentMonthExpenses > 0)
                            <i class="bi bi-arrow-up"></i>
                            Nouveau ce mois-ci
                        @else
                            <i class="bi bi-dash-circle"></i>
                            Aucune dépense ce mois-ci
                        @endif

                    @elseif ($expensesGrowth >= 0)

                        <i class="bi bi-arrow-up"></i>

                        {{ number_format($expensesGrowth, 1, ',', ' ') }}%

                        <span>
                            vs mois dernier
                        </span>

                    @else

                        <i class="bi bi-arrow-down"></i>

                        {{ number_format(abs($expensesGrowth), 1, ',', ' ') }}%

                        <span>
                            vs mois dernier
                        </span>

                    @endif

                </div>

            </div>

        </div>

        {{-- FACTURES À ENCAISSER --}}
        <div class="col-12 col-sm-6 col-xl">

            <div class="stat-card">

                <div class="stat-card-top">

                    <div class="stat-icon products-icon">

                        <i class="bi bi-receipt"></i>

                    </div>

                    <span class="stat-label">
                        À encaisser
                    </span>

                </div>

                <h3>
                    {{ number_format($unpaidInvoiceTotal, 0, ',', ' ') }}
                    FCFA
                </h3>

                <div class="stat-growth">
                    <a href="{{ route('invoices.index') }}" class="view-all">
                        @if ($unpaidInvoiceCount === 0)
                            Aucune facture impayée
                        @else
                            {{ $unpaidInvoiceCount }}
                            facture{{ $unpaidInvoiceCount > 1 ? 's' : '' }}
                            impayée{{ $unpaidInvoiceCount > 1 ? 's' : '' }}
                        @endif
                    </a>
                </div>

            </div>

        </div>

    </div>

    {{-- ==================================================
        GRAPHIQUE + STOCK FAIBLE
    ================================================== --}}

    <div class="row g-4 mb-4">

        {{-- GRAPHIQUE --}}
        <div class="col-12 col-xl-8">

            <div class="dashboard-card">

                <div class="card-header-custom">

                    <div>

                        <h5 id="salesChartTitle">
                            Évolution des ventes
                        </h5>

                        <p id="salesChartSubtitle">

                            @if ($period === 'week')

                                Ventes des 7 derniers jours

                            @elseif ($period === 'month')

                                Ventes du mois

                            @elseif ($period === 'quarter')

                                Ventes des 3 derniers mois

                            @endif

                        </p>

                    </div>

                    <div class="dropdown">

                        <button
                            id="salesPeriodButton"
                            class="btn btn-sm btn-light dropdown-toggle"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >

                            <span id="salesPeriodButtonText">

                                {{
                                    $period === 'week'
                                        ? 'Cette semaine'
                                        : ($period === 'month'
                                            ? 'Ce mois'
                                            : 'Ce trimestre')
                                }}

                            </span>

                        </button>

                        <ul class="dropdown-menu dropdown-menu-end">

                            <li>

                                <button
                                    class="dropdown-item period-option"
                                    type="button"
                                    data-period="week"
                                >
                                    Cette semaine
                                </button>

                            </li>

                            <li>

                                <button
                                    class="dropdown-item period-option"
                                    type="button"
                                    data-period="month"
                                >
                                    Ce mois
                                </button>

                            </li>

                            <li>

                                <button
                                    class="dropdown-item period-option"
                                    type="button"
                                    data-period="quarter"
                                >
                                    Ce trimestre
                                </button>

                            </li>

                        </ul>

                    </div>

                </div>

                <div class="chart-placeholder">

                    <div class="chart-area">

                        <canvas id="salesChart"></canvas>

                    </div>

                </div>

            </div>

        </div>

        {{-- STOCK FAIBLE --}}
        <div class="col-12 col-xl-4">

            <div class="dashboard-card h-100">

                <div class="card-header-custom">

                    <div>

                        <h5>
                            Stock faible
                        </h5>

                        <p>
                            Produits à surveiller
                        </p>

                    </div>

                    @can('viewAny', \App\Models\Product::class)

                        <a
                            href="{{ route('products.index') }}"
                            class="view-all"
                        >
                            Voir tout
                        </a>

                    @endcan

                </div>

                <div class="low-stock-list">

                    @forelse ($lowStockProducts as $product)

                        <div class="stock-item">

                            <div class="stock-product-icon">

                                <i class="bi bi-box-seam"></i>

                            </div>

                            <div class="stock-product-info">

                                <strong>
                                    {{ $product->name }}
                                </strong>

                                <small>
                                    {{ $product->category?->name ?? 'Sans catégorie' }}
                                </small>

                            </div>

                            <span
                                class="badge {{
                                    $product->stock == 0
                                        ? 'bg-danger-subtle text-danger'
                                        : 'bg-warning-subtle text-warning-emphasis'
                                }}"
                            >

                                {{ $product->stock }}

                                {{ $product->stock > 1 ? 'unités' : 'unité' }}

                            </span>

                        </div>

                    @empty

                        <div class="text-center text-muted py-4">

                            <i class="bi bi-check-circle fs-3 d-block mb-2 text-success"></i>

                            Aucun produit en stock faible.

                        </div>

                    @endforelse

                </div>

            </div>

        </div>

    </div>

    {{-- ==================================================
        PRODUITS LES PLUS VENDUS
    ================================================== --}}

    <div class="dashboard-card mb-4">

        <div class="card-header-custom">

            <div>
                <h5>
                    Produits les plus vendus
                </h5>
                <p>
                    Quantités vendues depuis le début du mois
                </p>
            </div>

            @can('viewAny', \App\Models\Sale::class)
                <a href="{{ route('sales.index') }}" class="view-all">
                    Voir les ventes
                </a>
            @endcan

        </div>

        @if ($topProducts->isNotEmpty())
            <div class="table-responsive">
                <table class="table dashboard-table mb-0">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Quantité vendue</th>
                            <th>Ventes générées</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($topProducts as $product)
                            <tr>
                                <td>
                                    <strong>{{ $product['name'] }}</strong>
                                </td>
                                <td>
                                    {{ number_format($product['quantity_sold'], 0, ',', ' ') }}
                                    {{ $product['quantity_sold'] > 1 ? 'unités' : 'unité' }}
                                </td>
                                <td>
                                    {{ number_format($product['total_revenue'], 0, ',', ' ') }}
                                    FCFA
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center text-muted py-4">
                Aucune vente de produits ce mois-ci.
            </div>
        @endif

    </div>

    {{-- ==================================================
        RÉPARTITION DES DÉPENSES
    ================================================== --}}

    <div class="dashboard-card mb-4">

        <div class="card-header-custom">

            <div>

                <h5>
                    Dépenses par catégorie
                </h5>

                <p>
                    Répartition du mois en cours
                </p>

            </div>

            @can('viewAny', \App\Models\Expense::class)
                <a href="{{ route('expenses.index') }}" class="view-all">
                    Voir les dépenses
                </a>
            @endcan

        </div>

        @if ($expenseBreakdown->isNotEmpty())
            <div class="chart-placeholder">
                <div class="chart-area">
                    <canvas
                        id="expenseChart"
                        role="img"
                        aria-label="Répartition des dépenses du mois par catégorie"
                    ></canvas>
                </div>
            </div>
        @else
            <div class="text-center text-muted py-4">
                Aucune dépense enregistrée ce mois-ci.
            </div>
        @endif

    </div>

    {{-- ==================================================
        DERNIÈRES VENTES
    ================================================== --}}

    <div class="dashboard-card">

        <div class="card-header-custom">

            <div>

                <h5>
                    Dernières ventes
                </h5>

                <p>
                    Les dernières transactions effectuées
                </p>

            </div>

            @can('viewAny', \App\Models\Sale::class)

                <a
                    href="{{ route('sales.index') }}"
                    class="view-all"
                >
                    Voir toutes les ventes
                </a>

            @endcan

        </div>

        <div class="table-responsive">

            <table class="table dashboard-table">

                <thead>

                    <tr>

                        <th>
                            Référence
                        </th>

                        <th>
                            Client
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

                    </tr>

                </thead>

                <tbody>

                    @forelse ($recentSales as $sale)

                        <tr>

                            {{-- Référence --}}
                            <td>

                                <strong>
                                    #{{ $sale->sale_number }}
                                </strong>

                            </td>

                            {{-- Client --}}
                            <td>

                                {{ $sale->client?->name ?? 'Client supprimé' }}

                            </td>

                            {{-- Date --}}
                            <td>

                                {{ $sale->sale_date->format('d/m/Y') }}

                            </td>

                            {{-- Montant --}}
                            <td>

                                <strong>

                                    {{ number_format(
                                        $sale->total_amount,
                                        0,
                                        ',',
                                        ' '
                                    ) }}

                                    FCFA

                                </strong>

                            </td>

                            {{-- Statut --}}
                            <td>

                                @if ($sale->status === 'completed')

                                    <span class="badge bg-success-subtle text-success">
                                        Terminée
                                    </span>

                                @elseif ($sale->status === 'pending')

                                    <span class="badge bg-warning-subtle text-warning-emphasis">
                                        En attente
                                    </span>

                                @elseif ($sale->status === 'cancelled')

                                    <span class="badge bg-danger-subtle text-danger">
                                        Annulée
                                    </span>

                                @else

                                    <span class="badge bg-secondary-subtle text-secondary">
                                        {{ $sale->status }}
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="5"
                                class="text-center text-muted py-4"
                            >

                                <i class="bi bi-receipt fs-3 d-block mb-2"></i>

                                Aucune vente enregistrée.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

{{-- ==================================================
    DONNÉES DU GRAPHIQUE
================================================== --}}

<script>

window.salesChartData = @json($salesChart);
window.expenseChartData = @json($expenseBreakdown);

document.addEventListener(
    'DOMContentLoaded',
    function () {

        document
            .querySelectorAll('.period-option')
            .forEach(function (button) {

                button.addEventListener(
                    'click',
                    async function (event) {

                        const period =
                            this.dataset.period;

                        event.preventDefault();
                        event.stopPropagation();

                        const periodButton =
                            document.getElementById(
                                'salesPeriodButton'
                            );

                        const periodButtonText =
                            document.getElementById(
                                'salesPeriodButtonText'
                            );

                        const chartSubtitle =
                            document.getElementById(
                                'salesChartSubtitle'
                            );

                        if (
                            !window.salesChart
                            || !window.salesChart.data
                        ) {

                            console.error(
                                'Le graphique des ventes n\'est pas disponible.'
                            );

                            return;

                        }

                        // ==================================================
                        // CHARGEMENT
                        // ==================================================

                        window.bootstrap.Dropdown
                            .getOrCreateInstance(periodButton)
                            .hide();

                        periodButton.disabled = true;

                        periodButtonText.textContent =
                            'Chargement...';

                        try {

                            const response =
                                await fetch(
                                    `/dashboard/sales-chart?period=${encodeURIComponent(period)}`,
                                    {
                                        headers: {
                                            'Accept': 'application/json'
                                        }
                                    }
                                );

                            if (!response.ok) {

                                throw new Error(
                                    'Erreur lors de la récupération des données.'
                                );

                            }

                            const result =
                                await response.json();

                            // ==================================================
                            // METTRE À JOUR LE GRAPHIQUE
                            // ==================================================

                            window.salesChart.data.labels =
                                result.data.map(
                                    item => item.label
                                );

                            window.salesChart
                                .data
                                .datasets[0]
                                .data =
                                result.data.map(
                                    item => item.total
                                );

                            window.salesChart.update();

                            // ==================================================
                            // TEXTES
                            // ==================================================

                            const periodTexts = {

                                week: {
                                    button: 'Cette semaine',
                                    subtitle: 'Ventes des 7 derniers jours'
                                },

                                month: {
                                    button: 'Ce mois',
                                    subtitle: 'Ventes du mois'
                                },

                                quarter: {
                                    button: 'Ce trimestre',
                                    subtitle: 'Ventes des 3 derniers mois'
                                }

                            };

                            periodButtonText.textContent =
                                periodTexts[period].button;

                            chartSubtitle.textContent =
                                periodTexts[period].subtitle;

                        } catch (error) {

                            console.error(error);

                            periodButtonText.textContent =
                                'Erreur de chargement';

                        } finally {

                            periodButton.disabled =
                                false;

                        }

                    }
                );

            });

    }
);

</script>

@endsection
