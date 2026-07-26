@extends('layouts.user')

@section('title', 'Laboratory Account Registration')

@section('content')
<div class="row justify-content-center py-3">
    <div class="col-lg-8">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-dark text-white text-center py-4">
                <h4 class="fw-bold mb-1"><i class="bx bx-shield-quarter me-2 text-primary"></i>Laboratory Email Registration</h4>
                <p class="text-white-50 small mb-0">Step 1 of 2 — Verify your email to create a secure PT portal account.</p>
            </div>
            <div class="card-body p-4 p-md-5">
                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif
                <form action="{{ route('user.register.submit') }}" method="POST" class="row g-3">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Laboratory Name <span class="text-danger">*</span></label>
                        <input name="laboratory_name" class="form-control" value="{{ old('laboratory_name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Center Name</label>
                        <input name="center_name" class="form-control" value="{{ old('center_name') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Contact Person Name <span class="text-danger">*</span></label>
                        <input name="contact_person" class="form-control" value="{{ old('contact_person') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Address <span class="text-danger">*</span></label>
                        <textarea name="address" class="form-control" rows="2" required>{{ old('address') }}</textarea>
                    </div>
                    <div class="col-md-3"><label class="form-label small fw-semibold">City <span class="text-danger">*</span></label><input name="city" class="form-control" value="{{ old('city') }}" required></div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">State <span class="text-danger">*</span></label>
                        <select name="state" class="form-select" required>
                            <option value="" disabled {{ !old('state') ? 'selected' : '' }}>Select State</option>
                            @foreach([
                                'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh', 'Goa', 'Gujarat', 
                                'Haryana', 'Himachal Pradesh', 'Jharkhand', 'Karnataka', 'Kerala', 'Madhya Pradesh', 
                                'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab', 
                                'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura', 'Uttar Pradesh', 
                                'Uttarakhand', 'West Bengal', 'Andaman and Nicobar Islands', 'Chandigarh', 
                                'Dadra and Nagar Haveli and Daman and Diu', 'Delhi', 'Jammu and Kashmir', 'Ladakh', 
                                'Lakshadweep', 'Puducherry'
                            ] as $st)
                                <option value="{{ $st }}" {{ old('state') === $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3"><label class="form-label small fw-semibold">Country <span class="text-danger">*</span></label><input name="country" class="form-control" value="{{ old('country', 'India') }}" required></div>
                    <div class="col-md-3"><label class="form-label small fw-semibold">PIN Code <span class="text-danger">*</span></label><input name="pin_code" class="form-control" value="{{ old('pin_code') }}" required></div>
                    <div class="col-md-6"><label class="form-label small fw-semibold">Contact Number <span class="text-danger">*</span></label><input name="mobile_number" class="form-control" value="{{ old('mobile_number') }}" required></div>
                    <div class="col-12 d-flex justify-content-between align-items-center border-top pt-4 mt-3">
                        <a href="{{ route('user.login') }}" class="btn btn-outline-secondary">Already registered? Log in</a>
                        <button class="btn btn-primary px-4"><i class="bx bx-envelope me-1"></i>Send Verification OTP</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
