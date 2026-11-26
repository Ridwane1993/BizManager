<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Connexion - BizManager</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="bg-light">

    <div class="min-vh-100 d-flex align-items-center justify-content-center p-3">

        <div class="card shadow-sm border-0" style="max-width: 420px; width: 100%;">

            <div class="card-body p-4 p-md-5">

                {{-- Logo / nom --}}

                <div class="text-center mb-4">

                    <div class="mb-3">

                        <i class="bi bi-grid-1x2-fill fs-1 text-primary"></i>

                    </div>

                    <h2 class="fw-bold mb-1">
                        BizManager
                    </h2>

                    <p class="text-muted mb-0">
                        Connectez-vous à votre espace
                    </p>

                </div>

                {{-- Message de session --}}

                @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                {{-- Erreurs --}}

                <x-validation-errors message="La connexion a échoué." />

                {{-- Formulaire --}}

                <form method="POST" action="{{ url('/login') }}">

                    @csrf

                    {{-- Email --}}

                    <div class="mb-3">

                        <label for="email" class="form-label">
                            Adresse e-mail
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                <i class="bi bi-envelope"></i>
                            </span>

                            <input type="email" id="email" name="email"
                                class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}"
                                placeholder="exemple@email.com" required autofocus>

                        </div>

                        @error('email')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    {{-- Mot de passe --}}

                    <div class="mb-3">

                        <div class="d-flex justify-content-between">

                            <label for="password" class="form-label">
                                Mot de passe
                            </label>

                            <a href="{{ url('/forgot-password') }}" class="small text-decoration-none">
                                Mot de passe oublié ?
                            </a>

                        </div>

                        <div class="input-group">

                            <span class="input-group-text">
                                <i class="bi bi-lock"></i>
                            </span>

                            <input type="password" id="password" name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Votre mot de passe" required>

                        </div>

                        @error('password')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    {{-- Se souvenir de moi --}}

                    <div class="form-check mb-4">

                        <input type="checkbox" id="remember" name="remember" value="1" class="form-check-input">

                        <label for="remember" class="form-check-label">
                            Se souvenir de moi
                        </label>

                    </div>

                    {{-- Bouton --}}

                    <button type="submit" class="btn btn-primary w-100 py-2">

                        <i class="bi bi-box-arrow-in-right me-1"></i>

                        Se connecter

                    </button>

                </form>
                {{-- Séparateur --}}
                <div class="d-flex align-items-center my-4">
                    <hr class="flex-grow-1">
                    <span class="text-muted small px-3">OU</span>
                    <hr class="flex-grow-1">
                </div>

                {{-- Message de statut passkey --}}
                <div id="loginPasskeyStatus" class="alert d-none" role="status" aria-live="polite"></div>

                {{-- Connexion avec une passkey --}}
                <button type="button" id="loginPasskeyButton" class="btn btn-outline-primary w-100 py-2">
                    <i class="bi bi-fingerprint me-1"></i>
                    Se connecter avec une passkey
                </button>

                <p class="text-muted small text-center mt-2 mb-0">
                    Utilisez une passkey déjà enregistrée sur votre appareil.
                </p>

                {{-- Inscription --}}

                <div class="text-center mt-4">

                    <span class="text-muted">
                        Vous n'avez pas encore de compte ?
                    </span>

                    <a href="{{ url('/register') }}" class="text-decoration-none fw-semibold">
                        Créer un compte
                    </a>

                </div>

            </div>

        </div>

    </div>

</body>

</html>
