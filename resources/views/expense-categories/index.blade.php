@extends('layouts.app')

@section('title', 'Catégories de dépenses')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Catégories de dépenses</h1>
            <p class="text-muted mb-0">Organisez les dépenses de votre entreprise.</p>
        </div>
        <a href="{{ route('expense-categories.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>
            Nouvelle catégorie
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4">Catégorie</th>
                        <th>Dépenses liées</th>
                        <th class="text-end px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenseCategories as $expenseCategory)
                        <tr>
                            <td class="px-4 fw-semibold">{{ $expenseCategory->name }}</td>
                            <td>{{ $expenseCategory->expenses_count }}</td>
                            <td class="text-end px-4">
                                <div class="d-inline-flex gap-2">
                                    <a
                                        href="{{ route('expense-categories.edit', $expenseCategory) }}"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        Modifier
                                    </a>
                                    <form
                                        action="{{ route('expense-categories.destroy', $expenseCategory) }}"
                                        method="POST"
                                        onsubmit="return confirm('Supprimer cette catégorie ? Les dépenses existantes seront conservées.');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-5">
                                Aucune catégorie de dépense. Créez-en une pour faciliter le classement.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($expenseCategories->hasPages())
            <div class="card-footer bg-white">
                {{ $expenseCategories->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
