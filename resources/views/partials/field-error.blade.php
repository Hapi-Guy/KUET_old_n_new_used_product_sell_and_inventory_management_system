{{-- Inline, dismissible validation message shown directly under a field.
     Usage: @include('partials.field-error', ['field' => 'email'])
     Pair it with:  class="form-control @error('email') is-invalid @enderror" --}}
@error($field)
    <div class="invalid-feedback d-block field-error mt-1">
        <div class="d-flex justify-content-between align-items-start">
            <span><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</span>
            <button type="button" class="btn-close btn-close-field ms-2" aria-label="Dismiss"></button>
        </div>
    </div>
@enderror
