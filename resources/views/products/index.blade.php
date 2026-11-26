@extends('layouts.app')

@section('title', 'Produits')

@section('content')

    <div class="container-fluid">
        @if ($filters['low_stock'])
            <div class="alert alert-warning d-flex justify-content-between align-items-center gap-2">
                <span><i class="bi bi-exclamation-triangle me-2"></i>Produits au seuil d’alerte ou en dessous.</span>
                <a href="{{ route('products.index') }}" class="alert-link">Afficher tous les produits</a>
            </div>
        @endif

        {{-- ================================================== --}}
        {{-- ================================================== --}}

        {{-- ================================================== --}}
        {{-- ================================================== --}}

        {{-- ================================================== --}}
        {{-- EN-TÊTE --}}
        {{-- ================================================== --}}

        <div
            class="dashboard-header d-flex justify-content-between align-items-center mb-4"
        >

            <div>

                <h1 class="h3 fw-bold mb-1">
                    Produits
                </h1>

                <p class="text-muted mb-0">
                    Gérez vos produits et votre stock.
                </p>

            </div>

            {{-- Nouveau produit --}}
            @can('create', \App\Models\Product::class)

                <a
                    href="{{ route('products.create') }}"
                    class="btn btn-primary"
                >
                    <i class="bi bi-plus-lg me-1"></i>
                    Nouveau produit
                </a>

            @endcan

        </div>

        {{-- ================================================== --}}
        {{-- CARTE PRINCIPALE --}}
        {{-- ================================================== --}}

        <div class="dashboard-card">

            {{-- ============================================== --}}
            {{-- RECHERCHE / FILTRES --}}
            {{-- ============================================== --}}

            <div class="p-3 border-bottom">

                <div class="row g-3">

                    <div class="col-md-6">

                        <form
                            method="GET"
                            action="{{ route('products.index') }}"
                        >

                            <div class="row g-3 align-items-end">

                                {{-- Recherche --}}
                                <div class="col-12 col-md-6 col-lg-6">

                                    <label
                                        for="search"
                                        class="form-label small fw-semibold"
                                    >
                                        Recherche
                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text bg-white">
                                            <i class="bi bi-search"></i>
                                        </span>

                                        <input
                                            type="text"
                                            id="search"
                                            name="search"
                                            maxlength="100"
                                            class="form-control @error('search') is-invalid @enderror"
                                            value="{{ old('search', $filters['search']) }}"
                                            placeholder="Rechercher un produit..."
                                        >

                                    </div>
                                    @error('search')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror

                                </div>

                                {{-- Catégorie --}}
                                <div class="col-12 col-md-4 col-lg-4">

                                    <label
                                        for="category_id"
                                        class="form-label small fw-semibold"
                                    >
                                        Catégorie
                                    </label>

                                    <select
                                        name="category_id"
                                        id="category_id"
                                        class="form-select @error('category_id') is-invalid @enderror"
                                    >

                                        <option value="">
                                            Toutes les catégories
                                        </option>

                                        @foreach ($categories as $category)

                                            <option
                                                value="{{ $category->id }}"
                                                @selected((string) old('category_id', $filters['category_id']) === (string) $category->id)
                                            >
                                                {{ $category->name }}
                                            </option>

                                        @endforeach

                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror

                                </div>

                                {{-- Filtrer --}}
                                <div class="col-12 col-md-2 col-lg-2">

                                    <button
                                        type="submit"
                                        class="btn btn-primary w-100"
                                    >
                                        <i class="bi bi-funnel me-1"></i>
                                        Filtrer
                                    </button>

                                </div>

                            </div>
                            @if ($filters['low_stock'])
                                <input type="hidden" name="low_stock" value="1">
                            @endif

                        </form>

                    </div>

                </div>

            </div>

            {{-- ============================================== --}}
            {{-- TABLEAU --}}
            {{-- ============================================== --}}

            <div class="table-responsive">

                <table
                    class="table dashboard-table align-middle mb-0"
                >

                    <thead>

                        <tr>

                            <th>Produit</th>
                            <th>Référence</th>
                            <th>Catégorie</th>
                            <th>Prix d'achat</th>
                            <th>Prix de vente</th>
                            <th>Stock</th>
                            <th class="text-end">
                                Actions
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($products as $product)

                            <tr>

                                {{-- ====================================== --}}
                                {{-- PRODUIT --}}
                                {{-- ====================================== --}}

                                <td>

                                    <div class="fw-semibold">
                                        {{ $product->name }}
                                    </div>

                                </td>

                                {{-- ====================================== --}}
                                {{-- RÉFÉRENCE --}}
                                {{-- ====================================== --}}

                                <td>

                                    <span class="text-muted">
                                        {{ $product->reference }}
                                    </span>

                                </td>

                                {{-- ====================================== --}}
                                {{-- CATÉGORIE --}}
                                {{-- ====================================== --}}

                                <td>
                                    {{ $product->category->name ?? 'Sans catégorie' }}
                                </td>

                                {{-- ====================================== --}}
                                {{-- PRIX D'ACHAT --}}
                                {{-- ====================================== --}}

                                <td>

                                    {{ number_format(
                                        $product->purchase_price,
                                        0,
                                        ',',
                                        ' '
                                    ) }}

                                    FCFA

                                </td>

                                {{-- ====================================== --}}
                                {{-- PRIX DE VENTE --}}
                                {{-- ====================================== --}}

                                <td>

                                    <span class="fw-semibold">

                                        {{ number_format(
                                            $product->selling_price,
                                            0,
                                            ',',
                                            ' '
                                        ) }}

                                        FCFA

                                    </span>

                                </td>

                                {{-- ====================================== --}}
                                {{-- STOCK --}}
                                {{-- ====================================== --}}

                                <td>

                                    @if (
                                        $product->stock
                                        <= $product->stock_alert_threshold
                                    )

                                        <span class="badge text-bg-warning">
                                            {{ $product->stock }}
                                        </span>

                                    @else

                                        <span class="badge text-bg-success">
                                            {{ $product->stock }}
                                        </span>

                                    @endif

                                </td>

                                {{-- ====================================== --}}
                                {{-- ACTIONS --}}
                                {{-- ====================================== --}}

                                <td class="text-end">

                                    <div class="btn-group">

                                        {{-- Voir --}}
                                        @can('view', $product)

                                            <a
                                                href="{{ route('products.show', $product) }}"
                                                class="btn btn-sm btn-light"
                                                title="Voir"
                                            >
                                                <i class="bi bi-eye"></i>
                                            </a>

                                        @endcan

                                        {{-- Modifier --}}
                                        @can('update', $product)

                                            <a
                                                href="{{ route('products.edit', $product) }}"
                                                class="btn btn-sm btn-outline-secondary"
                                                title="Modifier"
                                            >
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                        @endcan

                                        {{-- Supprimer --}}
                                        @can('delete', $product)

                                            <form
                                                action="{{ route('products.destroy', $product) }}"
                                                method="POST"
                                                class="d-inline"
                                            >

                                                @csrf

                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Supprimer"
                                                    onclick="return confirm('Supprimer ce produit ?')"
                                                >
                                                    <i class="bi bi-trash"></i>
                                                </button>

                                            </form>

                                        @endcan

                                    </div>

                                </td>

                            </tr>

                        @empty

                            {{-- ========================================== --}}
                            {{-- AUCUN PRODUIT --}}
                            {{-- ========================================== --}}

                            <tr>

                                <td
                                    colspan="7"
                                    class="text-center py-5"
                                >

                                    <div class="text-muted">

                                        <i
                                            class="bi bi-box-seam fs-1 d-block mb-3"
                                        ></i>

                                        <h5>
                                            Aucun produit
                                        </h5>

                                        <p class="mb-0">
                                            Vous n'avez encore aucun produit enregistré.
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            @if ($products->hasPages())
                <div class="p-3 border-top">
                    {{ $products->links() }}
                </div>
            @endif

        </div>

    </div>

@endsection
