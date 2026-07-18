@extends('layouts.app')
@section('title', 'My deals')

@section('content')
<h4 class="mb-3"><i class="bi bi-receipt text-kuet"></i> My deals</h4>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-bag-check text-success"></i> Purchases ({{ $purchases->count() }})</div>
            <ul class="list-group list-group-flush">
                @forelse ($purchases as $t)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('products.show', $t->product) }}" class="fw-semibold text-decoration-none">{{ $t->product->title }}</a>
                            <span class="fw-bold text-kuet">৳ {{ number_format($t->final_price, 2) }}</span>
                        </div>
                        <div class="small text-muted">
                            Sold by {{ $t->product->seller->name }} &middot; {{ $t->transaction_date }}
                        </div>
                    </li>
                @empty
                    <li class="list-group-item text-muted">No purchases yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-cash-stack text-kuet"></i> Sales ({{ $sales->count() }})</div>
            <ul class="list-group list-group-flush">
                @forelse ($sales as $t)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('products.show', $t->product) }}" class="fw-semibold text-decoration-none">{{ $t->product->title }}</a>
                            <span class="fw-bold text-success">৳ {{ number_format($t->final_price, 2) }}</span>
                        </div>
                        <div class="small text-muted">
                            Bought by {{ $t->buyer->name }} &middot; {{ $t->transaction_date }}
                        </div>
                    </li>
                @empty
                    <li class="list-group-item text-muted">No sales yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
