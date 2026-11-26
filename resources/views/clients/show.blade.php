@extends('layouts.app')

@section('title', 'Client - ' . $client->name)

@section('content')

<div class="container-fluid py-4">

    {{-- =========================
        EN-TÊTE
    ========================== --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="h3 mb-1 fw-bold">

                <i class="bi bi-person me-2"></i>

                Détails du client

            </h1>

            <p class="text-muted mb-0">
                Consultez les informations de ce client.
            </p>

        </div>

        <div class="d-flex gap-2">

            {{-- Modifier --}}
            @can('update', $client)

                <a href="{{ route('clients.edit', $client) }}"
                    class="btn btn-primary">

                    <i class="bi bi-pencil me-1"></i>

                    Modifier

                </a>

            @endcan

            {{-- Retour --}}
            <a href="{{ route('clients.index') }}"
                class="btn btn-outline-secondary">

                <i class="bi bi-arrow-left me-1"></i>

                Retour

            </a>

        </div>

    </div>

    {{-- =========================
        INFORMATIONS
    ========================== --}}
    <div class="row g-4">

        {{-- Informations principales --}}
        <div class="col-12 col-lg-8">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white py-3">

                    <h5 class="mb-0 fw-semibold">

                        <i class="bi bi-person-vcard me-2"></i>

                        Informations du client

                    </h5>

                </div>

                <div class="card-body">

                    {{-- Nom --}}
                    <div class="d-flex align-items-center mb-4">

                        <div
                            class="bg-primary bg-opacity-10 rounded-circle
                                   d-flex align-items-center justify-content-center me-3"
                            style="width: 60px; height: 60px;"
                        >

                            <i class="bi bi-person text-primary fs-4"></i>

                        </div>

                        <div>

                            <small class="text-muted">
                                Nom complet
                            </small>

                            <h4 class="mb-0 fw-bold">
                                {{ $client->name }}
                            </h4>

                        </div>

                    </div>

                    <div class="row g-4">

                        {{-- Email --}}
                        <div class="col-12 col-md-6">

                            <div class="border rounded p-3 h-100">

                                <small class="text-muted d-block mb-1">

                                    <i class="bi bi-envelope me-1"></i>

                                    Email

                                </small>

                                @if ($client->email)

                                    <a href="mailto:{{ $client->email }}">

                                        {{ $client->email }}

                                    </a>

                                @else

                                    <span class="text-muted">
                                        Non renseigné
                                    </span>

                                @endif

                            </div>

                        </div>

                        {{-- Téléphone --}}
                        <div class="col-12 col-md-6">

                            <div class="border rounded p-3 h-100">

                                <small class="text-muted d-block mb-1">

                                    <i class="bi bi-telephone me-1"></i>

                                    Téléphone

                                </small>

                                @if ($client->phone)

                                    <span>
                                        {{ $client->phone }}
                                    </span>

                                @else

                                    <span class="text-muted">
                                        Non renseigné
                                    </span>

                                @endif

                            </div>

                        </div>

                        {{-- Adresse --}}
                        <div class="col-12">

                            <div class="border rounded p-3">

                                <small class="text-muted d-block mb-1">

                                    <i class="bi bi-geo-alt me-1"></i>

                                    Adresse

                                </small>

                                @if ($client->address)

                                    <span>
                                        {{ $client->address }}
                                    </span>

                                @else

                                    <span class="text-muted">
                                        Non renseignée
                                    </span>

                                @endif

                            </div>

                        </div>

                        {{-- Notes --}}
                        <div class="col-12">

                            <div class="border rounded p-3">

                                <small class="text-muted d-block mb-2">

                                    <i class="bi bi-sticky me-1"></i>

                                    Notes

                                </small>

                                @if ($client->notes)

                                    <p class="mb-0">
                                        {{ $client->notes }}
                                    </p>

                                @else

                                    <span class="text-muted">
                                        Aucune note
                                    </span>

                                @endif

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- =========================
            INFORMATIONS SYSTÈME
        ========================== --}}
        <div class="col-12 col-lg-4">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white py-3">

                    <h5 class="mb-0 fw-semibold">

                        <i class="bi bi-info-circle me-2"></i>

                        Informations

                    </h5>

                </div>

                <div class="card-body">

                    {{-- Identifiant --}}
                    <div class="mb-4">

                        <small class="text-muted d-block">
                            Identifiant
                        </small>

                        <span class="fw-semibold">

                            Client #{{ $client->id }}

                        </span>

                    </div>

                    {{-- Création --}}
                    <div class="mb-4">

                        <small class="text-muted d-block">
                            Date de création
                        </small>

                        <span class="fw-semibold">

                            {{ $client->created_at->format('d/m/Y à H:i') }}

                        </span>

                    </div>

                    {{-- Modification --}}
                    <div>

                        <small class="text-muted d-block">
                            Dernière modification
                        </small>

                        <span class="fw-semibold">

                            {{ $client->updated_at->format('d/m/Y à H:i') }}

                        </span>

                    </div>

                </div>

            </div>

            {{-- =========================
                SUPPRESSION
            ========================== --}}
            @can('delete', $client)

                <div class="card border-0 shadow-sm mt-4">

                    <div class="card-body">

                        <h6 class="fw-semibold mb-2">

                            Supprimer ce client

                        </h6>

                        <p class="text-muted small">

                            Cette action supprimera définitivement le client.

                        </p>

                        <form action="{{ route('clients.destroy', $client) }}"
                            method="POST"
                            onsubmit="return confirm('Voulez-vous vraiment supprimer ce client ?');">

                            @csrf

                            @method('DELETE')

                            <button type="submit"
                                class="btn btn-outline-danger w-100">

                                <i class="bi bi-trash me-1"></i>

                                Supprimer le client

                            </button>

                        </form>

                    </div>

                </div>

            @endcan

        </div>

    </div>

</div>

@endsection
