@extends('layouts.app')

@section('title', 'Mon profil')

@section('content')

    @php
        $profileUser = auth()->user();

        $statusMessages = [
            'profile-information-updated' => 'Vos informations personnelles ont été mises à jour.',
            'password-updated' => 'Votre mot de passe a été modifié avec succès.',
            'two-factor-authentication-enabled' =>
                'Configuration du 2FA commencée. Scannez le QR code puis confirmez le code.',
            'two-factor-authentication-confirmed' => 'L’authentification à deux facteurs est maintenant activée.',
            'two-factor-authentication-disabled' => 'L’authentification à deux facteurs a été désactivée.',
            'recovery-codes-generated' => 'De nouveaux codes de récupération ont été générés.',
            'passkey-registered' => 'Passkey enregistrée avec succès.',
            'passkey-deleted' => 'La passkey a été supprimée avec succès.',
        ];
    @endphp

    <div class="container-fluid py-4">

        {{-- En-tête --}}
        <div class="mb-4">
            <h1 class="h3 mb-1">Mon profil</h1>
            <p class="text-muted mb-0">
                Gérez vos informations personnelles et la sécurité de votre compte.
            </p>
        </div>

        {{-- Message de succès --}}
        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                {{ $statusMessages[session('status')] ?? session('status') }}

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        @endif

        {{-- Erreurs générales --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Une erreur est survenue.</strong>

                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        @endif

        <div class="row g-4">

            {{-- ==================================================
              PASSKEYS
               ================================================== --}}
            <div class="col-12">

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white py-3">
                        <h5 class="mb-1">
                            <i class="bi bi-fingerprint me-2"></i>
                            Connexion avec une passkey
                        </h5>

                        <p class="text-muted small mb-0">
                            Enregistrez une passkey pour vous connecter avec une
                            méthode prise en charge par votre appareil, comme Windows
                            Hello ou un authentificateur compatible.
                        </p>
                    </div>

                    <div class="card-body p-4">

                        <div id="passkeyStatus" class="alert d-none" role="status" aria-live="polite"></div>

                        <div class="row g-3 align-items-end">

                            <div class="col-12 col-md-7">

                                <label for="passkeyName" class="form-label fw-semibold">
                                    Nom de la passkey
                                </label>

                                <input type="text" id="passkeyName" class="form-control" maxlength="100"
                                    placeholder="Ex. : Mon ordinateur" value="Mon ordinateur" required>

                                <div class="form-text">
                                    Choisissez un nom qui vous permettra de reconnaître
                                    cette passkey plus tard.
                                </div>

                            </div>

                            <div class="col-12 col-md-5">

                                <button type="button" id="registerPasskeyButton" class="btn btn-primary w-100">
                                    <i class="bi bi-plus-circle me-1"></i>
                                    Ajouter une passkey
                                </button>

                            </div>

                        </div>

                        <div class="alert alert-info mt-4 mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            L’enregistrement peut demander une confirmation récente
                            de votre mot de passe. Utilisez un navigateur et un appareil
                            compatibles avec les passkeys.
                        </div>

                    </div>

                </div>

            </div>

            {{-- ==================================================
             INFORMATIONS PERSONNELLES
         ================================================== --}}
            <div class="col-12 col-lg-6">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-person me-2"></i>
                            Informations personnelles
                        </h5>
                    </div>

                    <div class="card-body p-4">

                        @if ($errors->updateProfileInformation->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->updateProfileInformation->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('user-profile-information.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold">
                                    Nom
                                </label>

                                <input type="text" id="name" name="name"
                                    class="form-control @error('name', 'updateProfileInformation') is-invalid @enderror"
                                    value="{{ old('name', $profileUser->name) }}" autocomplete="name" required>

                                @error('name', 'updateProfileInformation')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="email" class="form-label fw-semibold">
                                    Adresse e-mail
                                </label>

                                <input type="email" id="email" name="email"
                                    class="form-control @error('email', 'updateProfileInformation') is-invalid @enderror"
                                    value="{{ old('email', $profileUser->email) }}" autocomplete="email" required>

                                @error('email', 'updateProfileInformation')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>
                                Enregistrer
                            </button>

                        </form>

                    </div>
                </div>

            </div>

            {{-- ==================================================
             CHANGEMENT DU MOT DE PASSE
         ================================================== --}}
            <div class="col-12 col-lg-6">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-lock me-2"></i>
                            Changer le mot de passe
                        </h5>
                    </div>

                    <div class="card-body p-4">

                        @if ($errors->updatePassword->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->updatePassword->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('user-password.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="current_password" class="form-label fw-semibold">
                                    Mot de passe actuel
                                </label>

                                <input type="password" id="current_password" name="current_password"
                                    class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                                    autocomplete="current-password" required>

                                @error('current_password', 'updatePassword')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">
                                    Nouveau mot de passe
                                </label>

                                <input type="password" id="password" name="password"
                                    class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                                    autocomplete="new-password" required>

                                @error('password', 'updatePassword')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label fw-semibold">
                                    Confirmer le nouveau mot de passe
                                </label>

                                <input type="password" id="password_confirmation" name="password_confirmation"
                                    class="form-control" autocomplete="new-password" required>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-shield-lock me-1"></i>
                                Modifier le mot de passe
                            </button>

                        </form>

                    </div>
                </div>

            </div>

            {{-- ==================================================
             AUTHENTIFICATION À DEUX FACTEURS
         ================================================== --}}
            <div class="col-12">

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white py-3">
                        <h5 class="mb-1">
                            <i class="bi bi-shield-check me-2"></i>
                            Authentification à deux facteurs (2FA)
                        </h5>

                        <p class="text-muted small mb-0">
                            Renforcez la sécurité de votre compte avec un code
                            temporaire fourni par une application d’authentification.
                        </p>
                    </div>

                    <div class="card-body p-4">

                        {{-- 2FA activé et confirmé --}}
                        @if ($profileUser->hasEnabledTwoFactorAuthentication())

                            <div class="alert alert-success d-flex align-items-start">
                                <i class="bi bi-shield-check fs-4 me-3"></i>

                                <div>
                                    <strong>Le 2FA est activé et confirmé.</strong>
                                    <div class="small">
                                        Une deuxième vérification sera demandée
                                        lors de la connexion.
                                    </div>
                                </div>
                            </div>

                            {{-- Afficher les codes après confirmation ou régénération --}}
                            @if (in_array(session('status'), ['two-factor-authentication-confirmed', 'recovery-codes-generated'], true))

                                <div class="alert alert-warning">
                                    <h6 class="fw-bold">
                                        <i class="bi bi-key me-2"></i>
                                        Vos codes de récupération
                                    </h6>

                                    <p class="small">
                                        Conservez ces codes dans un endroit sécurisé.
                                        Chaque code est destiné à être utilisé en cas
                                        de perte d’accès à votre application
                                        d’authentification.
                                    </p>

                                    <div class="row g-2">
                                        @foreach ($profileUser->recoveryCodes() as $recoveryCode)
                                            <div class="col-12 col-sm-6 col-md-4">
                                                <code class="d-block bg-white border rounded p-2 text-center">
                                                    {{ $recoveryCode }}
                                                </code>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                            @endif

                            <div class="d-flex flex-wrap gap-2">

                                <form method="POST" action="{{ route('two-factor.regenerate-recovery-codes') }}"
                                    onsubmit="return confirm('Voulez-vous vraiment remplacer vos codes de récupération actuels ?');">
                                    @csrf

                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="bi bi-arrow-repeat me-1"></i>
                                        Régénérer les codes de récupération
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('two-factor.disable') }}"
                                    onsubmit="return confirm('Voulez-vous vraiment désactiver le 2FA ?');">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="bi bi-shield-x me-1"></i>
                                        Désactiver le 2FA
                                    </button>
                                </form>

                            </div>

                            {{-- Configuration commencée, mais pas encore confirmée --}}
                        @elseif ($profileUser->two_factor_secret)
                            <div class="alert alert-info">
                                <strong>Configuration en cours.</strong>

                                <p class="mb-0 mt-1">
                                    Scannez le QR code avec une application compatible,
                                    puis saisissez le code à six chiffres pour confirmer
                                    la configuration.
                                </p>
                            </div>

                            <div class="row align-items-center g-4">

                                <div class="col-12 col-md-4 text-center">

                                    <div class="border rounded bg-white p-3 d-inline-block">
                                        {!! $profileUser->twoFactorQrCodeSvg() !!}
                                    </div>

                                    <p class="text-muted small mt-2 mb-0">
                                        Scannez ce QR code avec votre application
                                        d’authentification.
                                    </p>

                                </div>

                                <div class="col-12 col-md-8">

                                    <form method="POST" action="{{ route('two-factor.confirm') }}">
                                        @csrf

                                        <div class="mb-3">
                                            <label for="two_factor_code" class="form-label fw-semibold">
                                                Code de vérification
                                            </label>

                                            <input type="text" id="two_factor_code" name="code"
                                                class="form-control @error('code') is-invalid @enderror"
                                                inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}"
                                                maxlength="6" placeholder="Ex. : 123456" required>

                                            @error('code')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>

                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-check-circle me-1"></i>
                                            Confirmer et activer le 2FA
                                        </button>

                                    </form>

                                </div>

                            </div>

                            <hr class="my-4">

                            <form method="POST" action="{{ route('two-factor.disable') }}"
                                onsubmit="return confirm('Voulez-vous annuler cette configuration ?');">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-outline-secondary">
                                    Annuler la configuration
                                </button>
                            </form>

                            {{-- 2FA désactivé --}}
                        @else
                            <div
                                class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

                                <div>
                                    <span class="badge bg-secondary mb-2">
                                        Désactivé
                                    </span>

                                    <p class="text-muted mb-0">
                                        Le 2FA n’est pas encore configuré sur votre compte.
                                        Activez-le pour ajouter une étape de vérification
                                        lors de la connexion.
                                    </p>
                                </div>

                                <form method="POST" action="{{ route('two-factor.enable') }}">
                                    @csrf

                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-shield-plus me-1"></i>
                                        Configurer le 2FA
                                    </button>
                                </form>

                            </div>

                        @endif

                        <div class="form-text mt-3">
                            Pour les opérations sensibles, Fortify peut demander une
                            confirmation récente de votre mot de passe.
                        </div>

                    </div>
                </div>

            </div>
            {{-- ==================================================
     LISTE DES PASSKEYS ENREGISTRÉES
================================================== --}}
            <div class="col-12">

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center gap-3">

                            <div>
                                <h5 class="mb-1">
                                    <i class="bi bi-fingerprint me-2"></i>
                                    Mes passkeys enregistrées
                                </h5>

                                <p class="text-muted small mb-0">
                                    Consultez et gérez les moyens de connexion associés à votre compte.
                                </p>
                            </div>

                            <span class="badge bg-primary rounded-pill">
                                {{ $passkeys->count() }}
                            </span>

                        </div>
                    </div>

                    <div class="card-body p-4">

                        @forelse ($passkeys as $passkey)
                            <div
                                class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 border rounded p-3 mb-3">

                                {{-- Informations sur la passkey --}}
                                <div class="d-flex align-items-start gap-3">

                                    <div class="fs-3 text-primary">
                                        <i class="bi bi-key"></i>
                                    </div>

                                    <div>

                                        <div class="fw-semibold">
                                            {{ $passkey->name }}
                                        </div>

                                        <div class="small text-muted mt-1">
                                            Authentificateur :
                                            {{ $passkey->authenticator ?? 'Non identifié' }}
                                        </div>

                                        <div class="small text-muted mt-1">
                                            Ajoutée le :
                                            {{ $passkey->created_at?->format('d/m/Y à H:i') ?? 'Date inconnue' }}
                                        </div>

                                        <div class="small text-muted">
                                            Dernière utilisation :
                                            {{ $passkey->last_used_at?->format('d/m/Y à H:i') ?? 'Pas encore utilisée' }}
                                        </div>

                                    </div>

                                </div>

                                {{-- Suppression --}}
                                <form method="POST" action="{{ route('passkey.destroy', $passkey) }}"
                                    onsubmit="return confirm('Voulez-vous vraiment supprimer cette passkey ? Vous ne pourrez plus l’utiliser pour vous connecter.');">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="bi bi-trash me-1"></i>
                                        Supprimer
                                    </button>

                                </form>

                            </div>

                        @empty

                            <div class="text-center py-4">

                                <i class="bi bi-fingerprint fs-1 text-muted"></i>

                                <h6 class="mt-3">
                                    Aucune passkey enregistrée
                                </h6>

                                <p class="text-muted mb-0">
                                    Vous pourrez enregistrer une passkey depuis cette page
                                    pour faciliter vos prochaines connexions.
                                </p>

                            </div>
                        @endforelse

                        <div class="alert alert-info mb-0 mt-3">
                            <i class="bi bi-info-circle me-2"></i>

                            Si vous supprimez une passkey, elle ne pourra plus être utilisée
                            pour vous connecter à BizManager. Vous pourrez toujours utiliser
                            une autre méthode de connexion encore active.
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection
