@extends('layouts.app')

@section('title', 'Modifier un mouvement de stock')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h1 class="h3 mb-1">Modifier un mouvement de stock</h1>
        <p class="text-muted mb-0">
            Ajustez le mouvement manuel lié à {{ $movement->product->name }}.
        </p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form
                method="POST"
                action="{{ route('stock-movements.update', $movement) }}"
            >
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="product_id" class="form-label">Produit</label>
                        <select
                            id="product_id"
                            name="product_id"
                            class="form-select @error('product_id') is-invalid @enderror"
                            required
                        >
                            @foreach ($products as $product)
                                <option
                                    value="{{ $product->id }}"
                                    {{ (string) old('product_id', $movement->product_id) === (string) $product->id ? 'selected' : '' }}
                                >
                                    {{ $product->name }} — Stock actuel : {{ $product->stock }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="type" class="form-label">Type de mouvement</label>
                        <select
                            id="type"
                            name="type"
                            class="form-select @error('type') is-invalid @enderror"
                            required
                        >
                            @foreach ([
                                'in' => 'Entrée',
                                'out' => 'Sortie',
                                'return' => 'Retour',
                                'loss' => 'Perte',
                                'adjustment' => 'Correction',
                            ] as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    {{ old('type', $movement->type) === $value ? 'selected' : '' }}
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="quantity" class="form-label">Quantité</label>
                        <input
                            type="number"
                            id="quantity"
                            name="quantity"
                            class="form-control @error('quantity') is-invalid @enderror"
                            value="{{ old('quantity', $movement->quantity) }}"
                            step="1"
                            required
                        >
                        <div id="quantityHelp" class="form-text">
                            La quantité doit être positive pour une entrée, sortie, perte ou retour.
                        </div>
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="description" class="form-label">Description</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                            class="form-control @error('description') is-invalid @enderror"
                        >{{ old('description', $movement->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a
                        href="{{ route('stock-movements.show', $movement) }}"
                        class="btn btn-outline-secondary"
                    >
                        Annuler
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('type');
    const quantityInput = document.getElementById('quantity');
    const quantityHelp = document.getElementById('quantityHelp');

    function updateQuantityRules() {
        if (typeSelect.value === 'adjustment') {
            quantityInput.removeAttribute('min');
            quantityHelp.textContent =
                'Utilisez une valeur positive pour augmenter le stock ou négative pour le diminuer.';
            return;
        }

        quantityInput.setAttribute('min', '1');
        quantityHelp.textContent =
            'La quantité doit être positive pour une entrée, sortie, perte ou retour.';

        if (quantityInput.value !== '' && Number(quantityInput.value) < 1) {
            quantityInput.value = 1;
        }
    }

    typeSelect.addEventListener('change', updateQuantityRules);
    updateQuantityRules();
});
</script>
@endpush
@endsection
