@extends('layouts.auth')

@section('title', 'Mot de passe oublié')

@section('content')

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <div class="text-center mb-4">
                        <div class="fs-1 text-primary mb-2">
                            <i class="bi bi-key"></i>
                        </div>

                        <h3 class="fw-bold mb-2">
                            Mot de passe oublié ?
                        </h3>

                        <p class="text-muted mb-0">
                            Entrez votre adresse email et nous vous
                            enverrons un lien pour réinitialiser votre mot de passe.
                        </p>
                    </div>

                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ url('/forgot-password') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">
                                Adresse email
                            </label>

                            <input
                                type="email"
                                name="email"
                                id="email"
                                value="{{ old('email') }}"
                                class="form-control @error('email') is-invalid @enderror"
                                autocomplete="email"
                                autofocus
                                required
                            >

                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-envelope me-1"></i>
                            Envoyer le lien
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
