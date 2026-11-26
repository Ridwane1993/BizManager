@extends('layouts.auth')

@section('title', 'Confirmer le mot de passe')

@section('content')

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-5">

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">

                    <div class="text-center mb-4">
                        <i class="bi bi-shield-lock fs-1 text-primary"></i>

                        <h1 class="h4 mt-3">
                            Confirmer votre mot de passe
                        </h1>

                        <p class="text-muted mb-0">
                            Pour continuer, veuillez saisir votre mot de passe actuel.
                        </p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form
                        method="POST"
                        action="{{ route('password.confirm.store') }}"
                    >
                        @csrf

                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">
                                Mot de passe actuel
                            </label>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                autocomplete="current-password"
                                autofocus
                                required
                            >

                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check-circle me-1"></i>
                            Confirmer
                        </button>

                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('profile.edit') }}" class="text-decoration-none">
                            Retour au profil
                        </a>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>

@endsection
