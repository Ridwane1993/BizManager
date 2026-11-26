@extends('layouts.auth')

@section('title', 'Réinitialiser le mot de passe')

@section('content')

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <div class="text-center mb-4">
                        <div class="fs-1 text-primary mb-2">
                            <i class="bi bi-lock"></i>
                        </div>

                        <h3 class="fw-bold mb-2">
                            Nouveau mot de passe
                        </h3>

                        <p class="text-muted mb-0">
                            Choisissez un nouveau mot de passe sécurisé.
                        </p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ url('/reset-password') }}">
                        @csrf

                        <input
                            type="hidden"
                            name="token"
                            value="{{ request()->route('token') }}"
                        >

                        <div class="mb-3">
                            <label for="email" class="form-label">
                                Adresse email
                            </label>

                            <input
                                type="email"
                                name="email"
                                id="email"
                                value="{{ old('email', $request->email ?? '') }}"
                                class="form-control @error('email') is-invalid @enderror"
                                autocomplete="email"
                                required
                            >

                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                Nouveau mot de passe
                            </label>

                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control @error('password') is-invalid @enderror"
                                autocomplete="new-password"
                                required
                            >

                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">
                                Confirmer le mot de passe
                            </label>

                            <input
                                type="password"
                                name="password_confirmation"
                                id="password_confirmation"
                                class="form-control"
                                autocomplete="new-password"
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check-circle me-1"></i>
                            Réinitialiser le mot de passe
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <a href="{{ route('login') }}" class="text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i>
                            Retour à la connexion
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

@endsection
