@extends('layouts.auth')

@section('title', 'Vérification en deux étapes')

@section('content')

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <div class="text-center mb-4">
                        <div class="fs-1 text-primary mb-2">
                            <i class="bi bi-shield-lock"></i>
                        </div>

                        <h3 class="fw-bold mb-2">
                            Vérification en deux étapes
                        </h3>

                        <p class="text-muted mb-0">
                            Entrez le code affiché dans votre application
                            d'authentification.
                        </p>
                    </div>

                    <x-validation-errors />

                    <form method="POST" action="{{ url('/two-factor-challenge') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="code" class="form-label">
                                Code de vérification
                            </label>

                            <input
                                type="text"
                                class="form-control @error('code') is-invalid @enderror"
                                id="code"
                                name="code"
                                inputmode="numeric"
                                autocomplete="one-time-code"
                                maxlength="6"
                                autofocus
                                required
                            >

                            @error('code')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check-circle me-1"></i>
                            Vérifier le code
                        </button>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="text-muted small mb-2">
                            Vous n'avez plus accès à votre application
                            d'authentification ?
                        </p>

                        <form method="POST" action="{{ url('/two-factor-challenge') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="recovery_code" class="form-label">
                                    Code de récupération
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="recovery_code"
                                    name="recovery_code"
                                    autocomplete="off"
                                >
                            </div>

                            <button type="submit" class="btn btn-outline-secondary w-100">
                                Utiliser un code de récupération
                            </button>
                        </form>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

@endsection
