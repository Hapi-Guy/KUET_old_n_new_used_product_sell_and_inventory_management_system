@extends('layouts.app')
@section('title', 'Sell an item')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <h4 class="mb-3"><i class="bi bi-plus-circle text-kuet"></i> List a product for sale</h4>
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" value="{{ old('title') }}" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Choose…</option>
                                @foreach ($categories as $c)
                                    <option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Condition</label>
                            <select name="product_condition" class="form-select" required>
                                <option value="NEW" @selected(old('product_condition') === 'NEW')>New</option>
                                <option value="OLD" @selected(old('product_condition', 'OLD') === 'OLD')>Old</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Min. price (৳)</label>
                            <input type="number" step="0.01" min="0" name="min_proposed_price" value="{{ old('min_proposed_price') }}" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="4" class="form-control" placeholder="Condition details, age, reason for selling…">{{ old('description') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Images <span class="text-muted">(you can select multiple)</span></label>
                        <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-kuet"><i class="bi bi-check-lg"></i> Publish listing</button>
                        <a href="{{ route('products.index') }}" class="btn btn-light">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
