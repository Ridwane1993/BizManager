@extends('layouts.app')

@section('title', 'Modifier la dépense')

@section('content')

<div class="container-fluid py-4">

{{-- En-tête --}}
<div class="mb-4">

    <h1 class="h3 mb-1">
        Modifier la dépense
    </h1>

    <p class="text-muted mb-0">
        Modifiez les informations de cette dépense.
    </p>

</div>

{{-- Erreurs de validation --}}
<x-validation-errors message="Veuillez corriger les erreurs suivantes :" />

<div class="card border-0 shadow-sm">

    <div class="card-body p-4">

        <form
            action="{{ route('expenses.update', $expense) }}"
            method="POST"
        >

            @csrf

            @method('PUT')

            <div class="row g-4">

                {{-- Catégorie --}}
                <div class="col-12 col-md-6">

                    <label
                        for="category"
                        class="form-label fw-semibold"
                    >
                        Catégorie
                    </label>

                    <input
                        type="text"
                        id="category"
                        name="category"
                        class="form-control @error('category') is-invalid @enderror"
                        value="{{ old('category', $expense->category) }}"
                        maxlength="255"
                        list="expense-categories"
                        required
                    >

                    <datalist id="expense-categories">
                        @foreach ($expenseCategories as $expenseCategory)
                            <option value="{{ $expenseCategory->name }}">
                        @endforeach
                    </datalist>
                    @can('viewAny', \App\Models\ExpenseCategory::class)
                        <div class="form-text">
                            <a href="{{ route('expense-categories.index') }}">Gérer les catégories de dépenses</a>
                        </div>
                    @endcan

                    @error('category')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>

                {{-- Montant --}}
                <div class="col-12 col-md-6">

                    <label
                        for="amount"
                        class="form-label fw-semibold"
                    >
                        Montant
                    </label>

                    <div class="input-group">

                        <input
                            type="number"
                            id="amount"
                            name="amount"
                            class="form-control @error('amount') is-invalid @enderror"
                            value="{{ old('amount', $expense->amount) }}"
                            min="0.01"
                            step="0.01"
                            required
                        >

                        <span class="input-group-text">
                            FCFA
                        </span>

                        @error('amount')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>

                </div>

                {{-- Date --}}
                <div class="col-12 col-md-6">

                    <label
                        for="expense_date"
                        class="form-label fw-semibold"
                    >
                        Date de la dépense
                    </label>

                    <input
                        type="date"
                        id="expense_date"
                        name="expense_date"
                        class="form-control @error('expense_date') is-invalid @enderror"
                        value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}"
                        required
                    >

                    @error('expense_date')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>

                {{-- Description --}}
                <div class="col-12">

                    <label
                        for="description"
                        class="form-label fw-semibold"
                    >
                        Description
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        class="form-control @error('description') is-invalid @enderror"
                        placeholder="Ajoutez une description si nécessaire..."
                    >{{ old('description', $expense->description) }}</textarea>

                    @error('description')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>

            </div>

            {{-- Boutons --}}
            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">

                <a
                    href="{{ route('expenses.index') }}"
                    class="btn btn-light"
                >
                    Annuler
                </a>

                <button
                    type="submit"
                    class="btn btn-primary"
                >

                    <i class="bi bi-check-lg me-1"></i>

                    Enregistrer les modifications

                </button>

            </div>

        </form>

    </div>

</div>
</div>

@endsection
