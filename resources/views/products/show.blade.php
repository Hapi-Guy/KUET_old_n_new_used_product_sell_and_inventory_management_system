@extends('layouts.app')
@section('title', $product->title)

@section('content')
<div class="mb-3">
    <a href="{{ route('products.index') }}" class="text-decoration-none text-muted"><i class="bi bi-arrow-left"></i> Back to dashboard</a>
</div>

<div class="row g-4">
    {{-- Left: gallery + description --}}
    <div class="col-lg-7">
        <div class="card mb-3">
            @if ($product->images->isNotEmpty())
                <div id="gallery" class="carousel slide" data-bs-ride="false">
                    <div class="carousel-inner">
                        @foreach ($product->images as $img)
                            <div class="carousel-item @if($loop->first) active @endif">
                                <img src="{{ asset('storage/' . $img->image_path) }}" class="d-block w-100" style="max-height:420px;object-fit:contain;background:#f1f3f6;" alt="">
                            </div>
                        @endforeach
                    </div>
                    @if ($product->images->count() > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#gallery" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                        <button class="carousel-control-next" type="button" data-bs-target="#gallery" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
                    @endif
                </div>
            @else
                <div class="placeholder-thumb" style="height:320px;"><i class="bi bi-image"></i></div>
            @endif
        </div>

        {{-- Photo management: the product's seller, or any admin, can add/remove photos. --}}
        @if ($canManagePhotos)
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-images text-kuet"></i> Manage photos
                    @if (auth()->user()->isAdmin() && ! ($isSeller ?? false))
                        <span class="badge bg-dark ms-1">Admin</span>
                    @endif
                </div>
                <div class="card-body">
                    @if ($product->images->isNotEmpty())
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @foreach ($product->images as $img)
                                <div class="position-relative">
                                    <img src="{{ asset('storage/' . $img->image_path) }}"
                                         style="height:72px;width:72px;object-fit:cover;border-radius:.375rem;border:1px solid #dee2e6;" alt="">
                                    <form method="POST" action="{{ route('product-images.destroy', [$product, $img]) }}"
                                          class="position-absolute top-0 end-0"
                                          onsubmit="return confirm('Remove this photo?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm py-0 px-1" title="Remove"
                                                style="transform:translate(30%,-30%);border-radius:50%;line-height:1;">&times;</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted small mb-3">No photos yet.</p>
                    @endif

                    <form method="POST" action="{{ route('product-images.store', $product) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="input-group">
                            <input type="file" name="images[]" class="form-control @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror"
                                   accept="image/*" multiple required>
                            <button type="submit" class="btn btn-kuet"><i class="bi bi-upload"></i> Add</button>
                        </div>
                        @include('partials.field-error', ['field' => 'images'])
                        @include('partials.field-error', ['field' => 'images.0'])
                        <div class="form-text">JPG/PNG, up to 4 MB each. You can select multiple files.</div>
                    </form>
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Description</h5>
                <p class="mb-0" style="white-space:pre-line;">{{ $product->description ?: 'No description provided.' }}</p>
            </div>
        </div>
    </div>

    {{-- Right: details + actions --}}
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <h4 class="mb-1">{{ $product->title }}</h4>
                    <span class="badge badge-cond-{{ $product->product_condition }}">{{ $product->product_condition }}</span>
                </div>
                <div class="text-muted mb-2"><i class="bi bi-tag"></i> {{ $product->category->category_name }}</div>

                @if ($product->isAvailable())
                    <span class="badge bg-success-subtle text-success border border-success-subtle">Available</span>
                @elseif ($product->isSold())
                    <span class="badge bg-secondary">Sold</span>
                @else
                    <span class="badge bg-warning text-dark">Unavailable</span>
                @endif

                <hr>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Minimum proposed price</span>
                    <span class="fw-bold text-kuet fs-5">৳ {{ number_format($product->min_proposed_price, 2) }}</span>
                </div>

                <hr>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="small text-muted">Seller</div>
                        <div class="fw-semibold">{{ $product->seller->name }}</div>
                        <div class="small">
                            @include('partials.stars', ['value' => $sellerRating->avg_seller_rating ?? 0])
                            <span class="text-muted">({{ $sellerRating->total_reviews ?? 0 }} reviews)</span>
                        </div>
                    </div>
                    @if ($isSeller)
                        {{-- Owner controls for this listing. --}}
                        <div class="d-flex flex-column gap-1">
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            @unless ($product->isSold())
                                <form method="POST" action="{{ route('products.destroy', $product) }}"
                                      onsubmit="return confirm('Delete this product?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-trash"></i> Delete</button>
                                </form>
                            @endunless
                        </div>
                    @else
                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#reportModal">
                            <i class="bi bi-flag"></i> Report
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Buyer actions --}}
        @unless ($isSeller)
            <div class="card mb-3">
                <div class="card-body">
                    @if ($product->isAvailable())
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-1 mb-2">
                            <h6 class="card-title mb-0"><i class="bi bi-cash-coin text-kuet"></i> Place your bid</h6>
                            @if ($highestBid)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-graph-up-arrow"></i> Highest bid: ৳ {{ number_format($highestBid, 2) }}
                                </span>
                            @else
                                <span class="badge bg-light text-muted border">No bids yet</span>
                            @endif
                        </div>
                        @if ($myBid)
                            <div class="alert alert-info py-2 small mb-2">
                                Your current bid: <strong>৳ {{ number_format($myBid->bid_amount, 2) }}</strong>
                                ({{ ucfirst(strtolower($myBid->bid_status)) }}).
                            </div>
                        @endif
                        <form method="POST" action="{{ route('bargains.store', $product) }}">
                            @csrf
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="1" name="bid_amount" class="form-control"
                                       value="{{ old('bid_amount', $myBid->bid_amount ?? '') }}" placeholder="Your offer" required>
                                <button class="btn btn-kuet">{{ $myBid ? 'Update bid' : 'Place bid' }}</button>
                            </div>
                        </form>
                    @else
                        <p class="text-muted mb-0">Bidding is closed for this product.</p>
                    @endif

                    <hr>
                    @if ($inWishlist)
                        <form method="POST" action="{{ route('wishlist.destroy', $product) }}">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger w-100"><i class="bi bi-heart-fill"></i> Remove from wishlist</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('wishlist.store', $product) }}">
                            @csrf
                            <button class="btn btn-outline-secondary w-100"><i class="bi bi-heart"></i> Add to wishlist</button>
                        </form>
                    @endif
                </div>
            </div>
        @endunless

        {{-- Seller: manual listing-status control --}}
        @if ($isSeller)
            @php $chosenBid = $product->bargains->firstWhere('bid_status', 'ACCEPTED'); @endphp
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0"><i class="bi bi-toggles text-kuet"></i> Listing status</h6>
                        @if ($product->isAvailable())
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Available</span>
                        @elseif ($product->isSold())
                            <span class="badge bg-secondary">Sold</span>
                        @else
                            <span class="badge bg-warning text-dark">Unavailable</span>
                        @endif
                    </div>

                    @if ($product->isAvailable())
                        @if ($chosenBid)
                            <form method="POST" action="{{ route('products.status', $product) }}"
                                  onsubmit="return confirm('Mark this product as Sold to {{ $chosenBid->buyer->name }}?');">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="SOLD">
                                <button class="btn btn-success w-100 mb-2">
                                    <i class="bi bi-bag-check"></i>
                                    Mark as Sold to {{ $chosenBid->buyer->name }} (৳ {{ number_format($chosenBid->bid_amount, 2) }})
                                </button>
                            </form>
                        @else
                            <div class="alert alert-light border small mb-2 py-2">
                                <i class="bi bi-info-circle"></i> Choose the winning bid below, then mark the product as Sold.
                            </div>
                        @endif
                        <form method="POST" action="{{ route('products.status', $product) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="UNAVAILABLE">
                            <button class="btn btn-outline-secondary btn-sm w-100">Mark Unavailable</button>
                        </form>
                    @elseif ($product->isSold())
                        <p class="small text-muted mb-2">Sold — recorded as a completed transaction below.</p>
                        <form method="POST" action="{{ route('products.status', $product) }}"
                              onsubmit="return confirm('Reopen this sale? The recorded transaction will be removed.');">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="AVAILABLE">
                            <button class="btn btn-outline-warning w-100">
                                <i class="bi bi-arrow-counterclockwise"></i> Reopen (back to Available)
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('products.status', $product) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="AVAILABLE">
                            <button class="btn btn-outline-success btn-sm w-100">Mark Available</button>
                        </form>
                    @endif
                </div>
            </div>
        @endif

        {{-- Seller actions: bid list --}}
        @if ($isSeller)
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-people text-kuet"></i> Bids received ({{ $product->bargains->count() }})
                </div>
                <ul class="list-group list-group-flush">
                    @forelse ($product->bargains as $bid)
                        <li class="list-group-item d-flex justify-content-between align-items-center @if($bid->bid_status === 'ACCEPTED') bg-success-subtle @endif">
                            <div>
                                <div class="fw-semibold">
                                    {{ $bid->buyer->name }}
                                    @if ($loop->first && $bid->bid_status !== 'REJECTED')
                                        <span class="badge bg-warning text-dark ms-1" title="Highest bid">Top</span>
                                    @endif
                                </div>
                                <div class="small text-muted">৳ {{ number_format($bid->bid_amount, 2) }}</div>
                            </div>
                            <div class="text-end">
                                @if ($product->isSold())
                                    {{-- After the sale: the winner reads "Sold", everyone else "Rejected". --}}
                                    @if ($bid->bid_status === 'ACCEPTED')
                                        <span class="badge bg-success bid-status-badge">Sold</span>
                                    @else
                                        <span class="badge bg-secondary bid-status-badge">Rejected</span>
                                    @endif
                                @elseif ($bid->bid_status === 'ACCEPTED')
                                    <button type="button" class="btn btn-sm btn-success"
                                            data-bs-toggle="popover" data-bs-trigger="focus" data-bs-html="true"
                                            data-bs-title="Contact {{ $bid->buyer->name }}"
                                            data-bs-content="<div class='mb-1'><i class='bi bi-telephone'></i> {{ $bid->buyer->mobile_no ?: 'No number' }}</div><div><i class='bi bi-envelope'></i> {{ $bid->buyer->email }}</div>">
                                        <i class="bi bi-person-lines-fill"></i> Contact
                                    </button>
                                    @if ($product->isAvailable())
                                        <form method="POST" action="{{ route('bargains.reset', $bid) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-secondary">Undo</button>
                                        </form>
                                    @endif
                                @elseif ($bid->bid_status === 'REJECTED')
                                    <span class="badge bg-secondary">Rejected</span>
                                    @if ($product->isAvailable())
                                        <form method="POST" action="{{ route('bargains.reset', $bid) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-secondary">Restore</button>
                                        </form>
                                    @endif
                                @elseif ($product->isAvailable())
                                    <form method="POST" action="{{ route('bargains.accept', $bid) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success"><i class="bi bi-hand-thumbs-up"></i> Choose</button>
                                    </form>
                                    <form method="POST" action="{{ route('bargains.reject', $bid) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-secondary">Reject</button>
                                    </form>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst(strtolower($bid->bid_status)) }}</span>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No bids yet.</li>
                    @endforelse
                </ul>
            </div>
        @endif

        {{-- Sold: transaction + rating --}}
        @if ($product->transaction)
            <div class="card mb-3 border-success">
                <div class="card-body">
                    <h6 class="card-title text-success"><i class="bi bi-receipt"></i> Sale completed</h6>
                    <div class="d-flex justify-content-between"><span class="text-muted">Buyer</span><span>{{ $product->transaction->buyer->name }}</span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted">Final price</span><span class="fw-bold">৳ {{ number_format($product->transaction->final_price, 2) }}</span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted">Date</span><span>{{ $product->transaction->transaction_date }}</span></div>

                    @php
                        $me = auth()->id();
                        $isParticipant = $me === (int) $product->seller_id || $me === (int) $product->transaction->buyer_id;
                    @endphp

                    @if ($isParticipant)
                        <hr>
                        @if ($myRating)
                            {{-- Already rated: show what was submitted, no form. --}}
                            <h6 class="mb-2"><i class="bi bi-star-fill text-warning"></i> Your rating</h6>
                            <div class="alert alert-success py-2 small mb-0">
                                <i class="bi bi-check-circle"></i>
                                You rated the {{ $me === (int) $product->seller_id ? 'buyer' : 'seller' }}:
                                @include('partials.stars', ['value' => $myRating->rating_value])
                                <span class="fw-semibold">{{ number_format($myRating->rating_value, 1) }}</span>
                                @if ($myRating->review_text)
                                    <div class="mt-1 fst-italic">&ldquo;{{ $myRating->review_text }}&rdquo;</div>
                                @endif
                            </div>
                        @else
                            <h6 class="mb-2"><i class="bi bi-star text-warning"></i> Leave a rating</h6>
                            <form method="POST" action="{{ route('ratings.store', $product) }}">
                                @csrf
                                <div class="mb-2">
                                    <select name="rating_value" class="form-select form-select-sm" required>
                                        <option value="">Stars…</option>
                                        @for ($s = 5; $s >= 1; $s--)
                                            <option value="{{ $s }}">{{ $s }} star{{ $s > 1 ? 's' : '' }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <textarea name="review_text" rows="2" class="form-control form-control-sm" placeholder="Optional review…"></textarea>
                                </div>
                                <button class="btn btn-sm btn-kuet w-100">Submit rating</button>
                            </form>
                            <p class="small text-muted mt-2 mb-0">
                                {{ $me === (int) $product->seller_id ? 'You are rating the buyer.' : 'You are rating the seller.' }}
                            </p>
                        @endif
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Report modal --}}
@unless ($isSeller)
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="{{ route('reports.store', $product) }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-flag text-danger"></i> Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Who / what are you reporting?</label>
                    <select name="report_type_combo" id="reportCombo" class="form-select" required
                            onchange="document.getElementById('reportedId').value=this.selectedOptions[0].dataset.uid;document.getElementById('reportType').value=this.selectedOptions[0].dataset.type;">
                        <option value="" data-uid="" data-type="">Choose…</option>
                        <option value="seller" data-uid="{{ $product->seller_id }}" data-type="SELLER">Seller &mdash; {{ $product->seller->name }} (fake product / bad seller)</option>
                        @if ($product->transaction)
                            <option value="buyer" data-uid="{{ $product->transaction->buyer_id }}" data-type="BUYER">Buyer &mdash; {{ $product->transaction->buyer->name }} (malicious buyer)</option>
                        @endif
                    </select>
                    <input type="hidden" name="reported_id" id="reportedId">
                    <input type="hidden" name="report_type" id="reportType">
                </div>
                <div class="mb-2">
                    <label class="form-label">Reason</label>
                    <textarea name="reason" rows="3" class="form-control" required placeholder="Describe the issue…"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Submit report</button>
            </div>
        </form>
    </div>
</div>
@endunless
@endsection
