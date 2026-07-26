@extends('layouts.user')

@section('title', 'Participant Login')

@section('content')
<div class="row justify-content-center py-4">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-lg border-0 my-4">
            <div class="card-header bg-dark text-white text-center py-4">
                <div class="bg-primary text-white p-3 rounded-circle d-inline-flex align-items-center justify-content-center mb-2 shadow-sm" style="width: 54px; height: 54px;">
                    <i class="bx bx-log-in-circle fs-2"></i>
                </div>
                <h4 class="fw-bold mb-1">Participant Login</h4>
                <p class="text-white-50 small mb-0">Sign in with your Email Address or User ID</p>
            </div>
            <div class="card-body p-4 p-md-5">

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bx bx-error-circle me-1 align-middle"></i> {{ $errors->first() }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('user.login.submit') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email Address or User ID <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bx bx-user"></i></span>
                            <input type="text" name="login" class="form-control" placeholder="e.g. rajesh@apexlabs.com OR LAB_APEX_8912" value="{{ old('login') }}" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bx bx-lock-alt"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="Enter account password" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordSibling(this)">
                                <i class="bx bx-show"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                            <label class="form-check-label small text-muted" for="rememberMe">Remember Me</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                        <i class="bx bx-log-in me-1"></i> Sign In to Portal
                    </button>
                </form>

                <div class="text-center mt-4 pt-3 border-top">
                    <p class="small text-muted mb-0">Don't have a laboratory account yet?</p>
                    <a href="{{ route('user.register') }}" class="btn btn-outline-primary btn-sm mt-2 px-4 fw-semibold">
                        <i class="bx bx-user-plus me-1"></i> Register Laboratory Account
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function togglePasswordSibling(btn) {
    var input = btn.parentElement.querySelector('input');
    var icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bx bx-hide';
    } else {
        input.type = 'password';
        icon.className = 'bx bx-show';
    }
}
</script>
@endsection
