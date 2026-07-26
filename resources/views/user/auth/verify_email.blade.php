@extends('layouts.user')

@section('title', 'Verify Email')

@section('content')
<div class="row justify-content-center py-5"><div class="col-md-6 col-lg-5"><div class="card shadow-lg border-0">
    <div class="card-header bg-dark text-white text-center py-4"><h4 class="fw-bold mb-1">Verify Your Email</h4><p class="text-white-50 small mb-0">Enter the six-digit code sent to your email.</p></div>
    <div class="card-body p-4">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        <form action="{{ route('user.register.verify.submit') }}" method="POST">@csrf
            <input type="hidden" name="email" value="{{ old('email', $email) }}">
            <label class="form-label small fw-semibold">Verification Code</label>
            <input name="otp" inputmode="numeric" maxlength="6" class="form-control text-center fs-4 fw-bold letter-spacing-2" required autofocus>
            <button class="btn btn-primary w-100 mt-3">Verify Email & Create Account</button>
        </form>
        <form action="{{ route('user.register.resend-otp') }}" method="POST" class="text-center mt-3">@csrf<input type="hidden" name="email" value="{{ old('email', $email) }}"><button class="btn btn-link btn-sm">Resend code</button></form>
    </div>
</div></div></div>
@endsection
