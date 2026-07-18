@extends('layouts.app')
@section('title', 'All Products')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-grid text-kuet"></i> All Products</h4>
    <a href="{{ route('products.create') }}" class="btn btn-kuet"><i class="bi bi-plus-circle"></i> Sell an item</a>
</div>

{{-- Smart search / filter / sort --}}
<form method="GET" action="{{ route('products.index') }}" class="card mb-4">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small mb-1">Search by title</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="{{ $filters['search'] }}" class="form-control" placeholder="e.g. laptop, calculator...">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Category</label>
                <select name="category" class="form-select">
                    <option value="">All categories</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->category_name }}" @selected($filters['category'] === $c->category_name)>{{ $c->category_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Condition</label>
                <select name="condition" class="form-select">
                    <option value="">Any</option>
                    <option value="NEW" @selected($filters['condition'] === 'NEW')>New</option>
                    <option value="OLD" @selected($filters['condition'] === 'OLD')>Old</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Sort by</label>
                <select name="sort" class="form-select">
                    <option value="latest"     @selected($filters['sort'] === 'latest')>Latest</option>
                    <option value="price_low"  @selected($filters['sort'] === 'price_low')>Price: low to high</option>
                    <option value="price_high" @selected($filters['sort'] === 'price_high')>Price: high to low</option>
                    <option value="bid_high"   @selected($filters['sort'] === 'bid_high')>Highest bid</option>
                    <option value="condition"  @selected($filters['sort'] === 'condition')>Condition</option>
                </select>
            </div>
            <div class="col-md-1 d-grid">
                <button class="btn btn-kuet"><i class="bi bi-funnel"></i></button>
            </div>
        </div>
    </div>
</form>

@if ($rows->isEmpty())
    <div class="text-center text-muted py-5">
        <i class="bi bi-inbox" style="font-size:3rem;"></i>
        <p class="mt-2">No available products match your filters.</p>
    </div>
@else
    <div class="row g-3">
        @foreach ($rows as $row)
            <div class="col-sm-6 col-lg-4">
                <div class="card product-card h-100">
                    <a href="{{ route('products.show', $row->product_id) }}">
                        @if ($thumbs->get($row->product_id))
                            <img src="{{ asset('storage/' . $thumbs->get($row->product_id)) }}" class="card-img-top thumb" alt="{{ $row->title }}">
                        @else
                            <div class="placeholder-thumb"><i class="bi bi-image"></i></div>
                        @endif
                    </a>
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start">
                            <h6 class="card-title mb-1">
                                <a href="{{ route('products.show', $row->product_id) }}" class="text-decoration-none text-dark">{{ $row->title }}</a>
                            </h6>
                            <span class="badge badge-cond-{{ $row->product_condition }}">{{ $row->product_condition }}</span>
                        </div>
                        <div class="text-muted small mb-2"><i class="bi bi-tag"></i> {{ $row->category_name }}</div>
                        <div class="mb-2">
                            <div class="fw-bold text-kuet">৳ {{ number_format($row->min_proposed_price, 2) }}</div>
                            <div class="small text-muted">Highest bid: ৳ {{ number_format($row->max_current_bid, 2) }}</div>
                        </div>
                        <div class="mt-auto d-flex gap-2">
                            <a href="{{ route('products.show', $row->product_id) }}" class="btn btn-sm btn-kuet flex-grow-1">View &amp; bid</a>
                            @if (in_array($row->product_id, $wishlisted))
                                <form method="POST" action="{{ route('wishlist.destroy', $row->product_id) }}">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Remove from wishlist"><i class="bi bi-heart-fill"></i></button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('wishlist.store', $row->product_id) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-secondary" title="Add to wishlist"><i class="bi bi-heart"></i></button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $rows->links() }}
    </div>
@endif
@endsection
