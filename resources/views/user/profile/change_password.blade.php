@extends('layouts.user')

@section('title', 'Change Password')

@section('content')
<div class="row justify-content-center py-5">
    <div class="col-md-6">
        <div class="card shadow border-0">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Change Password</h5>
            </div>
            <div class="card-body p-4">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif 
                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif
                <form action="{{ route('user.password.update') }}" method="POST">
                    @csrf 
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <div class="input-group">
                            <input type="password" name="current_password" class="form-control" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordSibling(this)">
                                <i class="bx bx-show"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <div class="input-group">
                            <input type="password" name="password" class="form-control" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordSibling(this)">
                                <i class="bx bx-show"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" class="form-control" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordSibling(this)">
                                <i class="bx bx-show"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ Auth::guard('lab')->user()->profile_completed_at ? route('user.dashboard') : route('user.profile.complete') }}" class="btn btn-light">Back</a>
                        <button class="btn btn-primary">Update Password</button>
                    </div>
                </form>
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
