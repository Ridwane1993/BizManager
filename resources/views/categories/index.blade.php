@extends('layouts.app')

@section('title', 'Catégories')

@section('content')

    <div class="container-fluid">

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
                    Catégories
                </h1>

                <p class="text-muted mb-0">
                    Organisez vos produits par catégorie.
                </p>

            </div>

            {{-- Nouvelle catégorie --}}
            @can('create', \App\Models\Category::class)

                <a
                    href="{{ route('categories.create') }}"
                    class="btn btn-primary"
                >
                    <i class="bi bi-plus-lg me-1"></i>
                    Nouvelle catégorie
                </a>

            @endcan

        </div>

        {{-- ================================================== --}}
        {{-- CARTE PRINCIPALE --}}
        {{-- ================================================== --}}

        <div class="dashboard-card">

            <div class="table-responsive">

                <table
                    class="table dashboard-table align-middle mb-0"
                >

                    <thead>

                        <tr>

                            <th>Nom</th>

                            <th>Description</th>

                            <th>Produits</th>

                            <th class="text-end">
                                Actions
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($categories as $category)

                            <tr>

                                {{-- ====================================== --}}
                                {{-- NOM --}}
                                {{-- ====================================== --}}

                                <td>

                                    <span class="fw-semibold">
                                        {{ $category->name }}
                                    </span>

                                </td>

                                {{-- ====================================== --}}
                                {{-- DESCRIPTION --}}
                                {{-- ====================================== --}}

                                <td>

                                    <span class="text-muted">

                                        {{ $category->description ?? 'Aucune description' }}

                                    </span>

                                </td>

                                {{-- ====================================== --}}
                                {{-- NOMBRE DE PRODUITS --}}
                                {{-- ====================================== --}}

                                <td>

                                    <span class="badge text-bg-primary">
                                        {{ $category->products()->count() }}
                                    </span>

                                </td>

                                {{-- ====================================== --}}
                                {{-- ACTIONS --}}
                                {{-- ====================================== --}}

                                <td class="text-end">

                                    <div class="btn-group">

                                        {{-- Modifier --}}
                                        @can('update', $category)

                                            <a
                                                href="{{ route('categories.edit', $category) }}"
                                                class="btn btn-sm btn-outline-secondary"
                                                title="Modifier"
                                            >
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                        @endcan

                                        {{-- Supprimer --}}
                                        @can('delete', $category)

                                            <form
                                                action="{{ route('categories.destroy', $category) }}"
                                                method="POST"
                                                class="d-inline"
                                            >

                                                @csrf

                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Supprimer"
                                                    onclick="return confirm('Supprimer cette catégorie ?')"
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
                            {{-- AUCUNE CATÉGORIE --}}
                            {{-- ========================================== --}}

                            <tr>

                                <td
                                    colspan="4"
                                    class="text-center py-5"
                                >

                                    <div class="text-muted">

                                        <i
                                            class="bi bi-folder2-open fs-1 d-block mb-3"
                                        ></i>

                                        <h5>
                                            Aucune catégorie
                                        </h5>

                                        <p class="mb-3">
                                            Vous n'avez encore créé aucune catégorie.
                                        </p>

                                        {{-- Créer une catégorie --}}
                                        @can('create', \App\Models\Category::class)

                                            <a
                                                href="{{ route('categories.create') }}"
                                                class="btn btn-primary"
                                            >
                                                <i class="bi bi-plus-lg me-1"></i>
                                                Créer une catégorie
                                            </a>

                                        @endcan

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

@endsection
