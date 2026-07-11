<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') &middot; {{ config('app.name') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --kuet: #0d4f8b; --kuet-dark: #0a3c69; }
        body { background: #f4f6f9; }
        .navbar-kuet { background: linear-gradient(90deg, var(--kuet-dark), var(--kuet)); }
        .navbar-kuet .navbar-brand, .navbar-kuet .nav-link { color: #fff !important; }
        .navbar-kuet .nav-link:hover { color: #cfe2ff !important; }
        .text-kuet { color: var(--kuet) !important; }
        .btn-kuet { background: var(--kuet); border-color: var(--kuet); color: #fff; }
        .btn-kuet:hover { background: var(--kuet-dark); border-color: var(--kuet-dark); color: #fff; }
        .product-card img.thumb { height: 190px; object-fit: cover; }
        .product-card .placeholder-thumb {
            height: 190px; display: flex; align-items: center; justify-content: center;
            background: #e9eef5; color: #9aa7b5; font-size: 2.4rem;
        }
        .card { border: none; box-shadow: 0 1px 3px rgba(16,24,40,.08); }
        .badge-cond-NEW { background: #198754; }
        .badge-cond-OLD { background: #fd7e14; }
        footer { color: #6c757d; }
        .auth-wrapper { min-height: 100vh; display: flex; align-items: center;
            background: linear-gradient(135deg, var(--kuet-dark), var(--kuet)); }
    </style>
    @stack('styles')
</head>
<body>
@auth
    <nav class="navbar navbar-expand-lg navbar-kuet shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('products.index') }}">
                <i class="bi bi-bag-check-fill"></i> KUET Old &amp; New
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="nav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('products.index') }}"><i class="bi bi-grid"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('products.create') }}"><i class="bi bi-plus-circle"></i> Sell an item</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('products.mine') }}"><i class="bi bi-box-seam"></i> My products</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('wishlist.index') }}"><i class="bi bi-heart"></i> Wishlist</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('transactions.index') }}"><i class="bi bi-receipt"></i> My deals</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> {{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text small text-muted">{{ auth()->user()->email }}</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right"></i> Sign out</button>
                                </form>
                            </li>

                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
@endauth

    <main class="@yield('main-class', 'container py-4')">
        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    @auth
        <footer class="container py-4 text-center small">
            &copy; {{ date('Y') }} {{ config('app.name') }}
        </footer>
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
