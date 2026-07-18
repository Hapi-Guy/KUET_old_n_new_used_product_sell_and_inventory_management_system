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
        /* Inline per-field validation message (with its own dismiss button). */
        .field-error { font-size: .85rem; }
        .field-error .btn-close-field { font-size: .6rem; padding: .25rem; flex: 0 0 auto; }
        /* After-sale bid statuses (Sold / Rejected) rendered at a uniform width. */
        .bid-status-badge { display: inline-block; min-width: 84px; padding-top: .45rem; padding-bottom: .45rem; }
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
                    @if (auth()->user()->isAdmin())
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i> Admin</a></li>
                    @endif
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
        {{-- Global banners are only for the authenticated app shell. Guest/auth
             pages (login, register) show validation inline under each field, so
             nothing floats beside the centered card. --}}
        @auth
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
        @endauth

        @yield('content')
    </main>

    @auth
        <footer class="container py-4 text-center small">
            &copy; {{ date('Y') }} {{ config('app.name') }}
        </footer>
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Dismiss an inline field error: hide the message and clear the red
        // border on the input it belongs to.
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.field-error .btn-close-field');
            if (! btn) return;
            var group = btn.closest('.mb-3, [class*="col-"]') || btn.parentElement;
            var input = group ? group.querySelector('.is-invalid') : null;
            if (input) { input.classList.remove('is-invalid'); }
            var box = btn.closest('.field-error');
            if (box) { box.remove(); }
        });

        // Enable Bootstrap popovers (used by the "Contact" button on bids).
        document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function (el) {
            new bootstrap.Popover(el);
        });
    </script>
    @stack('scripts')
</body>
</html>
