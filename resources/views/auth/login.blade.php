@extends('layouts.app')
@section('title', 'Sign in')
@section('main-class', 'auth-wrapper')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="text-center text-white mb-4">
                <h3 class="fw-bold mb-1"><i class="bi bi-bag-check-fill"></i> KUET Old &amp; New</h3>
                <p class="mb-0 small opacity-75">Used Product Sale &amp; Inventory Management System</p>
            </div>
            <div class="card">
                <div class="card-body p-4">
                    <h5 class="card-title mb-3">Sign in</h5>

                    @if ($errors->any())
                        <div class="alert alert-danger py-2">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">KUET email</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                   class="form-control" placeholder="you@stud.kuet.ac.bd" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-kuet w-100">Sign in</button>
                    </form>
                </div>
                <div class="card-footer text-center bg-white border-0 pb-3">
                    New here? <a href="{{ route('register') }}" class="text-kuet fw-semibold">Create an account</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
