@extends('layouts.app')

@section('title', 'Détail de la dépense')

@section('content')

<div class="container-fluid py-4">

{{-- En-tête --}}
<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h1 class="h3 mb-1">
            Détail de la dépense
        </h1>

        <p class="text-muted mb-0">
            Consultez les informations de cette dépense.
        </p>

    </div>

    <div class="d-flex gap-2">

        {{-- Modifier --}}
        @can('update', $expense)

            <a
                href="{{ route('expenses.edit', $expense) }}"
                class="btn btn-primary"
            >

                <i class="bi bi-pencil me-1"></i>

                Modifier

            </a>

        @endcan

        {{-- Supprimer --}}
        @can('delete', $expense)

            <form
                action="{{ route('expenses.destroy', $expense) }}"
                method="POST"
                class="d-inline"
                onsubmit="return confirm('Voulez-vous vraiment supprimer cette dépense ?');"
            >

                @csrf

                @method('DELETE')

                <button
                    type="submit"
                    class="btn btn-outline-danger"
                >

                    <i class="bi bi-trash me-1"></i>

                    Supprimer

                </button>

            </form>

        @endcan

        {{-- Retour --}}
        <a
            href="{{ route('expenses.index') }}"
            class="btn btn-light"
        >

            <i class="bi bi-arrow-left me-1"></i>

            Retour

        </a>

    </div>

</div>

{{-- Informations --}}
<div class="card border-0 shadow-sm">

    <div class="card-body p-4">

        <div class="row g-4">

            {{-- Catégorie --}}
            <div class="col-12 col-md-6">

                <div class="text-muted small mb-1">
                    Catégorie
                </div>

                <h5 class="mb-0">
                    {{ $expense->category }}
                </h5>

            </div>

            {{-- Montant --}}
            <div class="col-12 col-md-6">

                <div class="text-muted small mb-1">
                    Montant
                </div>

                <h5 class="mb-0 text-primary">
                    {{ number_format($expense->amount, 0, ',', ' ') }}
                    FCFA
                </h5>

            </div>

            {{-- Date --}}
            <div class="col-12 col-md-6">

                <div class="text-muted small mb-1">
                    Date de la dépense
                </div>

                <h5 class="mb-0">
                    {{ $expense->expense_date->format('d/m/Y') }}
                </h5>

            </div>

            {{-- Description --}}
            <div class="col-12">

                <div class="text-muted small mb-1">
                    Description
                </div>

                <div class="p-3 bg-light rounded">

                    {{ $expense->description ?: 'Aucune description.' }}

                </div>

            </div>

        </div>

    </div>

</div>
</div>

@endsection
