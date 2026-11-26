@extends('layouts.app')

@section('title', 'Modifier une catégorie de dépense')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h1 class="h3 mb-1">Modifier une catégorie de dépense</h1>
        <p class="text-muted mb-0">Le changement de nom ne modifie pas les dépenses déjà enregistrées.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('expense-categories.update', $expenseCategory) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="name" class="form-label">Nom de la catégorie</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $expenseCategory->name) }}"
                        maxlength="255"
                        required
                        autofocus
                        class="form-control @error('name') is-invalid @enderror"
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="d-flex flex-wrap justify-content-end gap-2">
                    <a href="{{ route('expense-categories.index') }}" class="btn btn-outline-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
