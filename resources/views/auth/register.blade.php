<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Créer un compte - BizManager</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="bg-light">

<div class="min-vh-100 d-flex align-items-center justify-content-center p-3">

    <div
        class="card shadow-sm border-0"
        style="max-width: 480px; width: 100%;"
    >

        <div class="card-body p-4 p-md-5">

            {{-- En-tête --}}

            <div class="text-center mb-4">

                <div class="mb-3">

                    <i class="bi bi-grid-1x2-fill fs-1 text-primary"></i>

                </div>

                <h2 class="fw-bold mb-1">
                    Créer un compte
                </h2>

                <p class="text-muted mb-0">
                    Commencez à utiliser BizManager
                </p>

            </div>

            {{-- Erreurs --}}

            <x-validation-errors message="Vérifiez les informations saisies." />

            {{-- Formulaire --}}

            <form
                method="POST"
                action="{{ url('/register') }}"
            >

                @csrf

                <div class="mb-3">

                    <label
                        for="company_name"
                        class="form-label"
                    >
                        Nom de l’entreprise
                    </label>

                    <div class="input-group">

                        <span class="input-group-text">
                            <i class="bi bi-building"></i>
                        </span>

                        <input
                            type="text"
                            id="company_name"
                            name="company_name"
                            class="form-control"
                            value="{{ old('company_name') }}"
                            placeholder="Nom de votre entreprise"
                            maxlength="255"
                        >

                    </div>

                    @error('company_name')

                        <div class="text-danger small mt-1">
                            {{ $message }}
                        </div>

                    @enderror

                </div>

                <div class="text-center text-muted small mb-3">ou</div>

                <div class="mb-3">
                    <label for="join_code" class="form-label">Code de rattachement à une entreprise</label>
                    <input
                        type="text"
                        id="join_code"
                        name="join_code"
                        class="form-control @error('join_code') is-invalid @enderror"
                        value="{{ old('join_code') }}"
                        maxlength="10"
                        autocomplete="off"
                    >
                    <div class="form-text">Saisissez un code reçu de l’administrateur de votre entreprise.</div>
                    @error('join_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Nom --}}

                <div class="mb-3">

                    <label
                        for="name"
                        class="form-label"
                    >
                        Nom
                    </label>

                    <div class="input-group">

                        <span class="input-group-text">
                            <i class="bi bi-person"></i>
                        </span>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-control"
                            value="{{ old('name') }}"
                            placeholder="Votre nom"
                            required
                            autofocus
                        >

                    </div>

                    @error('name')

                        <div class="text-danger small mt-1">
                            {{ $message }}
                        </div>

                    @enderror

                </div>

                {{-- Email --}}

                <div class="mb-3">

                    <label
                        for="email"
                        class="form-label"
                    >
                        Adresse e-mail
                    </label>

                    <div class="input-group">

                        <span class="input-group-text">
                            <i class="bi bi-envelope"></i>
                        </span>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            value="{{ old('email') }}"
                            placeholder="exemple@email.com"
                            required
                        >

                    </div>

                    @error('email')

                        <div class="text-danger small mt-1">
                            {{ $message }}
                        </div>

                    @enderror

                </div>

                {{-- Mot de passe --}}

                <div class="mb-3">

                    <label
                        for="password"
                        class="form-label"
                    >
                        Mot de passe
                    </label>

                    <div class="input-group">

                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="Votre mot de passe"
                            required
                        >

                    </div>

                    @error('password')

                        <div class="text-danger small mt-1">
                            {{ $message }}
                        </div>

                    @enderror

                </div>

                {{-- Confirmation --}}

                <div class="mb-4">

                    <label
                        for="password_confirmation"
                        class="form-label"
                    >
                        Confirmer le mot de passe
                    </label>

                    <div class="input-group">

                        <span class="input-group-text">
                            <i class="bi bi-lock-fill"></i>
                        </span>

                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="form-control"
                            placeholder="Confirmez votre mot de passe"
                            required
                        >

                    </div>

                </div>

                {{-- Bouton --}}

                <button
                    type="submit"
                    class="btn btn-primary w-100 py-2"
                >

                    <i class="bi bi-person-plus me-1"></i>

                    Créer mon compte

                </button>

            </form>

            {{-- Connexion --}}

            <div class="text-center mt-4">

                <span class="text-muted">
                    Vous avez déjà un compte ?
                </span>

                <a
                    href="{{ url('/login') }}"
                    class="text-decoration-none fw-semibold"
                >
                    Se connecter
                </a>

            </div>

        </div>

    </div>

</div>

</body>

</html>
