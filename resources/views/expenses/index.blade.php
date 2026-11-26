@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">

        {{-- =========================
        EN-TÊTE
    ========================== --}}
        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>
                <h1 class="h3 mb-1">
                    Dépenses
                </h1>

                <p class="text-muted mb-0">
                    Gérez les dépenses de votre entreprise.
                </p>
            </div>

            {{-- Nouvelle dépense --}}
            @can('create', App\Models\Expense::class)
                <a href="{{ route('expenses.create') }}" class="btn btn-primary">

                    <i class="bi bi-plus-lg me-1"></i>

                    Nouvelle dépense

                </a>
            @endcan

        </div>

        {{-- =========================
        MESSAGE DE SUCCÈS
    ========================== --}}

        {{-- =========================
        MESSAGE D'ERREUR
    ========================== --}}

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('expenses.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-6 col-xl-3">
                            <label for="search" class="form-label">Recherche</label>
                            <input
                                type="search"
                                id="search"
                                name="search"
                                value="{{ old('search', $filters['search']) }}"
                                maxlength="100"
                                class="form-control @error('search') is-invalid @enderror"
                                placeholder="Catégorie ou description"
                            >
                            @error('search')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <label for="category_id" class="form-label">Catégorie</label>
                            <select
                                id="category_id"
                                name="category_id"
                                class="form-select @error('category_id') is-invalid @enderror"
                            >
                                <option value="">Toutes les catégories</option>
                                @foreach ($expenseCategories as $expenseCategory)
                                    <option
                                        value="{{ $expenseCategory->getKey() }}"
                                        @selected((string) $expenseCategory->getKey() === (string) old('category_id', $filters['category_id']))
                                    >
                                        {{ $expenseCategory->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-sm-6 col-xl-2">
                            <label for="start_date" class="form-label">Du</label>
                            <input
                                type="date"
                                id="start_date"
                                name="start_date"
                                value="{{ old('start_date', $filters['start_date']) }}"
                                max="{{ $filters['end_date'] }}"
                                class="form-control @error('start_date') is-invalid @enderror"
                            >
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-sm-6 col-xl-2">
                            <label for="end_date" class="form-label">Au</label>
                            <input
                                type="date"
                                id="end_date"
                                name="end_date"
                                value="{{ old('end_date', $filters['end_date']) }}"
                                min="{{ $filters['start_date'] }}"
                                class="form-control @error('end_date') is-invalid @enderror"
                            >
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-xl-2 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel me-1" aria-hidden="true"></i>
                                Filtrer
                            </button>
                            @if (
                                $filters['search'] !== ''
                                || $filters['category_id'] !== ''
                                || $filters['start_date'] !== ''
                                || $filters['end_date'] !== ''
                            )
                                <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">
                                    Effacer
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- =========================
        TABLEAU
    ========================== --}}
        <div class="card border-0 shadow-sm">

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th class="px-4">
                                    Catégorie
                                </th>

                                <th>
                                    Montant
                                </th>

                                <th>
                                    Date
                                </th>

                                <th>
                                    Description
                                </th>

                                <th class="text-end px-4">
                                    Actions
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse ($expenses as $expense)
                                <tr>

                                    {{-- Catégorie --}}
                                    <td class="px-4">

                                        <strong>
                                            {{ $expense->category }}
                                        </strong>

                                    </td>

                                    {{-- Montant --}}
                                    <td>

                                        <strong>
                                            {{ number_format($expense->amount, 0, ',', ' ') }}
                                            FCFA
                                        </strong>

                                    </td>

                                    {{-- Date --}}
                                    <td>

                                        {{ $expense->expense_date->format('d/m/Y') }}

                                    </td>

                                    {{-- Description --}}
                                    <td>

                                        {{ $expense->description ?: '—' }}

                                    </td>

                                    {{-- Actions --}}
                                    <td class="text-end px-4">

                                        <div class="d-inline-flex gap-1">

                                            {{-- =====================
                                            VOIR
                                        ====================== --}}
                                            @can('view', $expense)
                                                <a href="{{ route('expenses.show', $expense) }}"
                                                    class="btn btn-sm btn-outline-secondary" title="Voir">

                                                    <i class="bi bi-eye"></i>

                                                </a>
                                            @endcan

                                            {{-- =====================
                                            MODIFIER
                                        ====================== --}}
                                            @can('update', $expense)
                                                <a href="{{ route('expenses.edit', $expense) }}"
                                                    class="btn btn-sm btn-outline-primary" title="Modifier">

                                                    <i class="bi bi-pencil"></i>

                                                </a>
                                            @endcan

                                            {{-- =====================
                                            SUPPRIMER
                                        ====================== --}}
                                            @can('delete', $expense)
                                                <form action="{{ route('expenses.destroy', $expense) }}" method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Voulez-vous vraiment supprimer cette dépense ?');">

                                                    @csrf

                                                    @method('DELETE')

                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        title="Supprimer">

                                                        <i class="bi bi-trash"></i>

                                                    </button>

                                                </form>
                                            @endcan

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                {{-- =====================
                                AUCUNE DÉPENSE
                            ====================== --}}
                                <tr>

                                    <td colspan="5" class="text-center py-5">

                                        <i class="bi bi-wallet2 fs-1 text-muted"></i>

                                        <p class="text-muted mt-3 mb-3">
                                            @if (
                                                $filters['search'] !== ''
                                                || $filters['category_id'] !== ''
                                                || $filters['start_date'] !== ''
                                                || $filters['end_date'] !== ''
                                            )
                                                Aucune dépense ne correspond à ces filtres.
                                            @else
                                                Aucune dépense enregistrée.
                                            @endif
                                        </p>

                                        {{-- Créer une dépense --}}
                                        @can('create', App\Models\Expense::class)
                                            <a href="{{ route('expenses.create') }}" class="btn btn-primary">

                                                <i class="bi bi-plus-lg me-1"></i>

                                                Ajouter une dépense

                                            </a>
                                        @endcan

                                    </td>

                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

                {{-- Pagination --}}
                <div class="px-4 py-3">
                    {{ $expenses->links() }}
                </div>

            </div>

        </div>

    </div>
@endsection
