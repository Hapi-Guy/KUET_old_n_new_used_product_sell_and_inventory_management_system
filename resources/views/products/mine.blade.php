@extends('layouts.app')
@section('title', 'My products')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-box-seam text-kuet"></i> My products</h4>
    <a href="{{ route('products.create') }}" class="btn btn-kuet"><i class="bi bi-plus-circle"></i> Sell an item</a>
</div>

@if ($products->isEmpty())
    <div class="text-center text-muted py-5">
        <i class="bi bi-box" style="font-size:3rem;"></i>
        <p class="mt-2">You haven't listed any products yet.</p>
    </div>
@else
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Product</th><th>Category</th><th>Condition</th>
                        <th class="text-end">Min price</th><th class="text-center">Pending bids</th>
                        <th>Status</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $p)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if ($p->primaryImage())
                                        <img src="{{ asset('storage/' . $p->primaryImage()) }}" style="height:42px;width:42px;object-fit:cover;border-radius:6px;">
                                    @else
                                        <span class="placeholder-thumb" style="height:42px;width:42px;font-size:1rem;border-radius:6px;"><i class="bi bi-image"></i></span>
                                    @endif
                                    <a href="{{ route('products.show', $p) }}" class="text-decoration-none fw-semibold">{{ $p->title }}</a>
                                </div>
                            </td>
                            <td>{{ $p->category->category_name }}</td>
                            <td><span class="badge badge-cond-{{ $p->product_condition }}">{{ $p->product_condition }}</span></td>
                            <td class="text-end">৳ {{ number_format($p->min_proposed_price, 2) }}</td>
                            <td class="text-center">
                                @if ($p->pending_bids_count > 0)
                                    <span class="badge bg-kuet" style="background:var(--kuet);">{{ $p->pending_bids_count }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td>
                                @if ($p->status === 'AVAILABLE') <span class="badge bg-success-subtle text-success">Available</span>
                                @elseif ($p->status === 'SOLD') <span class="badge bg-secondary">Sold</span>
                                @else <span class="badge bg-warning text-dark">Unavailable</span> @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('products.show', $p) }}" class="btn btn-sm btn-outline-secondary">Manage</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
