@extends('layouts.app')
@section('title', 'My wishlist')

@section('content')
<h4 class="mb-3"><i class="bi bi-heart text-danger"></i> My wishlist</h4>

@if ($items->isEmpty())
    <div class="text-center text-muted py-5">
        <i class="bi bi-heart" style="font-size:3rem;"></i>
        <p class="mt-2">Your wishlist is empty. Items you bid on but don't win are added here automatically.</p>
        <a href="{{ route('products.index') }}" class="btn btn-kuet">Browse products</a>
    </div>
@else
    <div class="row g-3">
        @foreach ($items as $item)
            @php($p = $item->product)
            <div class="col-sm-6 col-lg-4">
                <div class="card product-card h-100">
                    <a href="{{ route('products.show', $p) }}">
                        @if ($p->primaryImage())
                            <img src="{{ asset('storage/' . $p->primaryImage()) }}" class="card-img-top thumb" alt="">
                        @else
                            <div class="placeholder-thumb"><i class="bi bi-image"></i></div>
                        @endif
                    </a>
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between">
                            <h6 class="mb-1"><a href="{{ route('products.show', $p) }}" class="text-decoration-none text-dark">{{ $p->title }}</a></h6>
                            @if ($p->status === 'AVAILABLE') <span class="badge bg-success-subtle text-success">Available</span>
                            @elseif ($p->status === 'SOLD') <span class="badge bg-secondary">Sold</span>
                            @else <span class="badge bg-warning text-dark">N/A</span> @endif
                        </div>
                        <div class="small text-muted mb-2"><i class="bi bi-tag"></i> {{ $p->category->category_name }}</div>
                        <div class="fw-bold text-kuet mb-2">৳ {{ number_format($p->min_proposed_price, 2) }}</div>
                        <div class="mt-auto d-flex gap-2">
                            <a href="{{ route('products.show', $p) }}" class="btn btn-sm btn-kuet flex-grow-1">View</a>
                            <form method="POST" action="{{ route('wishlist.destroy', $p) }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
