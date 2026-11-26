<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>
        @yield('title', 'BizManager')
    </title>


    {{-- ==================================================
        BOOTSTRAP 5
    ================================================== --}}

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


    {{-- ==================================================
        BOOTSTRAP ICONS
    ================================================== --}}

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


    {{-- ==================================================
        CSS + JAVASCRIPT DE L'APPLICATION
    ================================================== --}}

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')

</head>


<body>

    <div class="app-container">

        {{-- ==================================================
        SIDEBAR
    ================================================== --}}

        <aside class="sidebar" id="sidebar">

            {{-- Logo --}}
            <div class="sidebar-logo">

                <div class="logo-icon">

                    <i class="bi bi-graph-up-arrow"></i>

                </div>

                <span>
                    BIZMANAGER
                </span>

            </div>


            {{-- ==================================================
            NAVIGATION
        ================================================== --}}

            <nav class="sidebar-nav">

                {{-- Dashboard --}}
                <a href="{{ route('dashboard') }}"
                    class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">

                    <i class="bi bi-house-door"></i>

                    <span>
                        Dashboard
                    </span>

                </a>


                {{-- ==================================================
                GESTION
            ================================================== --}}

                <div class="sidebar-section-title">
                    GESTION
                </div>


                {{-- Produits --}}
                @can('viewAny', \App\Models\Product::class)
                    <a href="{{ route('products.index') }}"
                        class="sidebar-link {{ request()->routeIs('products.*') ? 'active' : '' }}">

                        <i class="bi bi-box-seam"></i>

                        <span>
                            Produits
                        </span>

                    </a>
                @endcan


                {{-- Catégories --}}
                @can('viewAny', \App\Models\Category::class)
                    <a href="{{ route('categories.index') }}"
                        class="sidebar-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">

                        <i class="bi bi-bookmark"></i>

                        <span>
                            Catégories
                        </span>

                    </a>
                @endcan


                {{-- Clients --}}
                @can('viewAny', \App\Models\Client::class)
                    <a href="{{ route('clients.index') }}"
                        class="sidebar-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">

                        <i class="bi bi-people"></i>

                        <span>
                            Clients
                        </span>

                    </a>
                @endcan


                {{-- Ventes --}}
                @can('viewAny', \App\Models\Sale::class)
                    <a href="{{ route('sales.index') }}"
                        class="sidebar-link {{ request()->routeIs('sales.*') ? 'active' : '' }}">

                        <i class="bi bi-cart3"></i>

                        <span>
                            Ventes
                        </span>

                    </a>
                @endcan


                {{-- Factures --}}
                @can('viewAny', \App\Models\Invoice::class)
                    <a href="{{ route('invoices.index') }}"
                        class="sidebar-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">

                        <i class="bi bi-receipt"></i>

                        <span>
                            Factures
                        </span>

                    </a>
                @endcan


                {{-- Achats --}}
                @can('viewAny', \App\Models\Purchase::class)
                    <a href="{{ route('purchases.index') }}"
                        class="sidebar-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}">

                        <i class="bi bi-cart-check"></i>

                        <span>
                            Achats
                        </span>

                    </a>
                @endcan


                {{-- Fournisseurs --}}
                @can('viewAny', \App\Models\Supplier::class)
                    <a href="{{ route('suppliers.index') }}"
                        class="sidebar-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">

                        <i class="bi bi-truck"></i>

                        <span>
                            Fournisseurs
                        </span>

                    </a>
                @endcan


                {{-- Stocks --}}
                @can('viewAny', \App\Models\StockMovement::class)
                    <a href="{{ route('stock-movements.index') }}"
                        class="sidebar-link {{ request()->routeIs('stock-movements.*') ? 'active' : '' }}">

                        <i class="bi bi-boxes"></i>

                        <span>
                            Stocks
                        </span>

                    </a>
                @endcan


                {{-- Dépenses --}}
                @can('viewAny', \App\Models\Expense::class)
                    <a href="{{ route('expenses.index') }}"
                        class="sidebar-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">

                        <i class="bi bi-wallet2"></i>

                        <span>
                            Dépenses
                        </span>

                    </a>
                @endcan

                @can('viewAny', \App\Models\ExpenseCategory::class)
                    <a href="{{ route('expense-categories.index') }}"
                        class="sidebar-link {{ request()->routeIs('expense-categories.*') ? 'active' : '' }}">

                        <i class="bi bi-tags"></i>

                        <span>
                            Catégories de dépenses
                        </span>

                    </a>
                @endcan

                @can('viewAny', \App\Models\Sale::class)
                    @can('viewAny', \App\Models\Purchase::class)
                        @can('viewAny', \App\Models\Expense::class)
                            <div class="sidebar-section-title mt-3">
                                ANALYSES
                            </div>
                            <a href="{{ route('reports.index') }}"
                                class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                                <i class="bi bi-graph-up"></i>
                                <span>Rapports</span>
                            </a>
                        @endcan
                    @endcan
                @endcan


                {{-- ==================================================
                ADMINISTRATION
            ================================================== --}}

                @auth

                    @if (auth()->user()->isAdmin())
                        <div class="sidebar-section-title mt-3">
                            ADMINISTRATION
                        </div>


                        <a href="{{ route('users.index') }}"
                            class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">

                            <i class="bi bi-person-gear"></i>

                            <span>
                                Utilisateurs
                            </span>

                        </a>
                    @endif

                @endauth


                {{-- ==================================================
                COMPTE
            ================================================== --}}

                @auth

                    <div class="sidebar-section-title">
                        COMPTE
                    </div>


                    {{-- Profil --}}
                    <a href="{{ route('profile.edit') }}"
                        class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">

                        <i class="bi bi-person-circle"></i>

                        <span>
                            Mon profil
                        </span>

                    </a>

                @endauth

            </nav>


            {{-- ==================================================
            DÉCONNEXION
        ================================================== --}}

            @auth

                <div class="sidebar-bottom">

                    <form action="{{ route('logout') }}" method="POST">

                        @csrf


                        <button type="submit" class="sidebar-link logout-link border-0 bg-transparent w-100">

                            <i class="bi bi-box-arrow-right"></i>

                            <span>
                                Déconnexion
                            </span>

                        </button>

                    </form>

                </div>

            @endauth

        </aside>


        {{-- ==================================================
        ZONE PRINCIPALE
    ================================================== --}}

        <div class="main-wrapper">


            {{-- ==================================================
            TOPBAR
        ================================================== --}}

            <header class="topbar">

                {{-- Menu mobile --}}
                <button class="sidebar-toggle" id="sidebarToggle" type="button" aria-label="Ouvrir le menu">

                    <i class="bi bi-list"></i>

                </button>


                {{-- Recherche --}}
                <form action="{{ route('search.index') }}" method="GET" class="search-box">
                    <i class="bi bi-search"></i>
                    <input
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Rechercher..."
                        aria-label="Recherche globale"
                        maxlength="100"
                    >
                </form>


                {{-- Partie droite --}}
                <div class="topbar-right">

                    {{-- Notifications --}}
                    <a
                        href="{{ route('products.index', ['low_stock' => 1]) }}"
                        class="notification-button"
                        title="Alertes de stock"
                        aria-label="Afficher les produits en stock faible"
                    >
                        <i class="bi bi-bell"></i>
                    </a>


                    {{-- Profil --}}
                    @auth

                        <div class="dropdown">

                            <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                aria-expanded="false"
                                aria-label="Menu du compte de {{ auth()->user()->name }}">

                                <i class="bi bi-person-circle me-1"></i>

                                <span class="topbar-user-name">{{ auth()->user()->name }}</span>

                            </button>


                            <ul class="dropdown-menu dropdown-menu-end">

                                {{-- Email --}}
                                <li>

                                    <span class="dropdown-item-text text-muted small">

                                        {{ auth()->user()->email }}

                                    </span>

                                </li>


                                <li>

                                    <hr class="dropdown-divider">

                                </li>


                                {{-- Profil --}}
                                <li>

                                    <a href="{{ route('profile.edit') }}" class="dropdown-item">

                                        <i class="bi bi-person me-2"></i>

                                        Mon profil

                                    </a>

                                </li>


                                {{-- Déconnexion --}}
                                <li>

                                    <hr class="dropdown-divider">

                                </li>


                                <li>

                                    <form method="POST" action="{{ route('logout') }}">

                                        @csrf


                                        <button type="submit" class="dropdown-item text-danger">

                                            <i class="bi bi-box-arrow-right me-2"></i>

                                            Déconnexion

                                        </button>

                                    </form>

                                </li>

                            </ul>

                        </div>

                    @endauth

                </div>

            </header>


            {{-- ==================================================
            CONTENU PRINCIPAL
        ================================================== --}}

            <main class="main-content">

                {{-- Message succès --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">

                        <i class="bi bi-check-circle me-2"></i>

                        {{ session('success') }}


                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                            aria-label="Fermer"></button>

                    </div>
                @endif


                {{-- Message erreur --}}
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">

                        <i class="bi bi-exclamation-triangle me-2"></i>

                        {{ session('error') }}


                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                            aria-label="Fermer"></button>

                    </div>
                @endif


                @yield('content')

            </main>

        </div>

    </div>


    {{-- ==================================================
    BOOTSTRAP JS
================================================== --}}

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


    {{-- ==================================================
    SIDEBAR MOBILE
================================================== --}}

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const sidebar =
                document.getElementById('sidebar');

            const sidebarToggle =
                document.getElementById('sidebarToggle');


            if (
                sidebar &&
                sidebarToggle
            ) {

                sidebarToggle.addEventListener(
                    'click',
                    function() {

                        sidebar.classList.toggle('show');

                    }
                );

            }

        });
    </script>


    {{-- ==================================================
    SCRIPTS SPÉCIFIQUES AUX PAGES
================================================== --}}

    @stack('scripts')

</body>

</html>
