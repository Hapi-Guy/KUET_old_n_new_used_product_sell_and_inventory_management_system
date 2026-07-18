@extends('layouts.app')
@section('title', 'Edit ' . $product->title)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <h4 class="mb-3"><i class="bi bi-pencil text-kuet"></i> Edit product</h4>
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" value="{{ old('title', $product->title) }}" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select" required>
                                @foreach ($categories as $c)
                                    <option value="{{ $c->id }}" @selected(old('category_id', $product->category_id) == $c->id)>{{ $c->category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Condition</label>
                            <select name="product_condition" class="form-select" required>
                                <option value="NEW" @selected(old('product_condition', $product->product_condition) === 'NEW')>New</option>
                                <option value="OLD" @selected(old('product_condition', $product->product_condition) === 'OLD')>Old</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Min. price (৳)</label>
                            <input type="number" step="0.01" min="0" name="min_proposed_price" value="{{ old('min_proposed_price', $product->min_proposed_price) }}" class="form-control" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required @disabled($product->isSold())>
                                <option value="AVAILABLE" @selected($product->status === 'AVAILABLE')>Available</option>
                                <option value="UNAVAILABLE" @selected($product->status === 'UNAVAILABLE')>Unavailable</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="4" class="form-control">{{ old('description', $product->description) }}</textarea>
                    </div>

                    @if ($product->images->isNotEmpty())
                        <div class="mb-3">
                            <label class="form-label d-block">Current images</label>
                            <div class="d-flex gap-2 flex-wrap">
                                @foreach ($product->images as $img)
                                    <img src="{{ asset('storage/' . $img->image_path) }}" style="height:70px;width:70px;object-fit:cover;border-radius:6px;">
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Add more images</label>
                        <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-kuet"><i class="bi bi-check-lg"></i> Save changes</button>
                        <a href="{{ route('products.show', $product) }}" class="btn btn-light">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
