@extends('layouts.app')
@section('title', 'Create account')
@section('main-class', 'auth-wrapper')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="text-center text-white mb-4">
                <h3 class="fw-bold mb-1"><i class="bi bi-bag-check-fill"></i> KUET Old &amp; New</h3>
                <p class="mb-0 small opacity-75">Sign up is restricted to KUET students</p>
            </div>
            <div class="card">
                <div class="card-body p-4">
                    <h5 class="card-title mb-3">Create your account</h5>

                    @if ($errors->any())
                        <div class="alert alert-danger py-2">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Full name</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">KUET email</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                   class="form-control" placeholder="you@stud.kuet.ac.bd" required>
                            <div class="form-text">Must end with <code>@stud.kuet.ac.bd</code></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mobile number <span class="text-muted">(optional)</span></label>
                            <input type="text" name="mobile_no" value="{{ old('mobile_no') }}" class="form-control">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm password</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-kuet w-100">Create account</button>
                    </form>
                </div>
                <div class="card-footer text-center bg-white border-0 pb-3">
                    Already have an account? <a href="{{ route('login') }}" class="text-kuet fw-semibold">Sign in</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
