@extends('layouts.app')
@section('title', 'All products (admin)')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0"><i class="bi bi-box-seam text-kuet"></i> All products</h4>
        <small class="text-muted">Every listing &mdash; open one to manage its photos</small>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Dashboard
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th><th>Title</th><th>Seller</th><th>Category</th>
                    <th>Condition</th><th>Status</th><th class="text-center">Photos</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $p)
                    <tr>
                        <td class="text-muted">{{ $p->id }}</td>
                        <td>{{ $p->title }}</td>
                        <td>{{ $p->seller->name ?? '—' }}</td>
                        <td>{{ $p->category->category_name ?? '—' }}</td>
                        <td><span class="badge badge-cond-{{ $p->product_condition }}">{{ $p->product_condition }}</span></td>
                        <td>
                            @if ($p->isSold())
                                <span class="badge bg-secondary">SOLD</span>
                            @elseif ($p->isAvailable())
                                <span class="badge bg-success">AVAILABLE</span>
                            @else
                                <span class="badge bg-warning text-dark">{{ $p->status }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border"><i class="bi bi-image"></i> {{ $p->images_count }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('products.show', $p) }}" class="btn btn-sm btn-kuet">
                                <i class="bi bi-images"></i> Manage photos
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No products yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $products->links() }}
</div>
@endsection
