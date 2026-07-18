@extends('layouts.app')
@section('title', 'Admin dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0"><i class="bi bi-speedometer2 text-kuet"></i> Admin dashboard</h4>
        <small class="text-muted">Site overview &mdash; read only</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.sql') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-database"></i> Raw SQL demo
        </a>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to marketplace
        </a>
    </div>
</div>

{{-- Stat tiles --}}
<div class="row g-3 mb-4">
    @php
        $tiles = [
            ['label' => 'Users',        'value' => $stats['users'],        'icon' => 'people',        'sub' => $stats['admins'].' admin'],
            ['label' => 'Products',     'value' => $stats['products'],     'icon' => 'box-seam',      'sub' => $stats['available'].' available'],
            ['label' => 'Sold',         'value' => $stats['sold'],         'icon' => 'bag-check',     'sub' => $stats['transactions'].' deals'],
            ['label' => 'Revenue',      'value' => '৳'.number_format($stats['revenue'], 0), 'icon' => 'cash-stack', 'sub' => 'gross'],
            ['label' => 'Reports',      'value' => $stats['reports'],      'icon' => 'flag',          'sub' => 'total filed'],
        ];
    @endphp
    @foreach ($tiles as $t)
        <div class="col-6 col-md-4 col-xl">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center text-muted small mb-1">
                        <i class="bi bi-{{ $t['icon'] }} me-1"></i> {{ $t['label'] }}
                    </div>
                    <div class="fs-3 fw-bold text-kuet">{{ $t['value'] }}</div>
                    <div class="small text-muted">{{ $t['sub'] }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-4">
    {{-- Reports --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-flag text-danger"></i> Latest reports</div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Type</th><th>Reporter</th><th>Reported</th><th>Product</th><th>Reason</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $r)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $r->report_type }}</span></td>
                                <td>{{ $r->reporter->name ?? '—' }}</td>
                                <td>{{ $r->reported->name ?? '—' }}</td>
                                <td class="text-truncate" style="max-width: 140px;">{{ $r->product->title ?? '—' }}</td>
                                <td class="text-truncate" style="max-width: 160px;" title="{{ $r->reason }}">{{ $r->reason }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">No reports filed.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Recent users --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-people text-kuet"></i> Recent users</div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Name</th><th>Email</th><th>Mobile</th><th>Role</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($recentUsers as $u)
                            <tr>
                                <td>{{ $u->name }}</td>
                                <td class="small">{{ $u->email }}</td>
                                <td class="small">{{ $u->mobile_no ?? '—' }}</td>
                                <td>
                                    @if ($u->isAdmin())
                                        <span class="badge bg-dark">Admin</span>
                                    @else
                                        <span class="badge bg-light text-dark border">User</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Recent products --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-box-seam text-kuet"></i> Recent products</span>
                <a href="{{ route('admin.products') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-images"></i> Manage all products
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>#</th><th>Title</th><th>Seller</th><th>Category</th><th>Condition</th><th>Min price</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($recentProducts as $p)
                            <tr>
                                <td class="text-muted">{{ $p->id }}</td>
                                <td>{{ $p->title }}</td>
                                <td>{{ $p->seller->name ?? '—' }}</td>
                                <td>{{ $p->category->category_name ?? '—' }}</td>
                                <td><span class="badge badge-cond-{{ $p->product_condition }}">{{ $p->product_condition }}</span></td>
                                <td>৳{{ number_format((float) $p->min_proposed_price, 2) }}</td>
                                <td>
                                    @if ($p->isSold())
                                        <span class="badge bg-secondary">SOLD</span>
                                    @elseif ($p->isAvailable())
                                        <span class="badge bg-success">AVAILABLE</span>
                                    @else
                                        <span class="badge bg-warning text-dark">{{ $p->status }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
