@extends('layouts.user')

@section('title', 'Laboratory Registration')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-lg border-0 my-3">
            <div class="card-header bg-dark text-white text-center py-4">
                <h4 class="fw-bold mb-1"><i class="bx bx-building-house me-2 text-primary"></i> Laboratory Registration Form</h4>
                <p class="text-white-50 small mb-0">Phase 1: Create a secure laboratory account to participate in ISO/IEC 17043 PT Schemes</p>
            </div>
            <div class="card-body p-4 p-md-5">

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong class="d-block mb-1"><i class="bx bx-error me-1"></i> Please correct the errors below:</strong>
                        <ul class="mb-0 ps-3 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('user.register.submit') }}" method="POST">
                    @csrf

                    <!-- Section 1: Company / Laboratory Information -->
                    <div class="border-bottom pb-3 mb-4">
                        <h6 class="fw-bold text-primary text-uppercase letter-spacing-1 mb-3">
                            <i class="bx bx-business me-1"></i> 1. Company / Laboratory Information
                        </h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Laboratory Name <span class="text-danger">*</span></label>
                                <input type="text" name="laboratory_name" id="labNameInput" class="form-control" placeholder="e.g. Apex Analytical Services" value="{{ old('laboratory_name') }}" required oninput="updateUsernamePreview()">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">NABL Certificate Number <small class="text-muted">(Optional)</small></label>
                                <input type="text" name="nabl_certificate_number" class="form-control" placeholder="e.g. TC-8912" value="{{ old('nabl_certificate_number') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Laboratory Type <span class="text-danger">*</span></label>
                                <select name="laboratory_type" class="form-select" required>
                                    <option value="">-- Select Laboratory Type --</option>
                                    <option value="Commercial Laboratory" {{ old('laboratory_type') == 'Commercial Laboratory' ? 'selected' : '' }}>Commercial Laboratory</option>
                                    <option value="Research Institute" {{ old('laboratory_type') == 'Research Institute' ? 'selected' : '' }}>Research Institute</option>
                                    <option value="Industrial Testing" {{ old('laboratory_type') == 'Industrial Testing' ? 'selected' : '' }}>Industrial Testing</option>
                                    <option value="Biotech & Pharma" {{ old('laboratory_type') == 'Biotech & Pharma' ? 'selected' : '' }}>Biotech & Pharma</option>
                                    <option value="Environmental Testing" {{ old('laboratory_type') == 'Environmental Testing' ? 'selected' : '' }}>Environmental Testing</option>
                                    <option value="Government Laboratory" {{ old('laboratory_type') == 'Government Laboratory' ? 'selected' : '' }}>Government Laboratory</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">GST Number <span class="text-danger">*</span></label>
                                <input type="text" name="gst_number" class="form-control text-uppercase" placeholder="e.g. 27AAACA1234A1Z5" value="{{ old('gst_number') }}" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold">Office / Lab Address <span class="text-danger">*</span></label>
                                <textarea name="address" class="form-control" rows="2" placeholder="Full street address..." required>{{ old('address') }}</textarea>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">City <span class="text-danger">*</span></label>
                                <input type="text" name="city" class="form-control" placeholder="e.g. Mumbai" value="{{ old('city') }}" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">State <span class="text-danger">*</span></label>
                                <input type="text" name="state" class="form-control" placeholder="e.g. Maharashtra" value="{{ old('state') }}" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Country <span class="text-danger">*</span></label>
                                <input type="text" name="country" class="form-control" value="{{ old('country', 'India') }}" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">PIN Code <span class="text-danger">*</span></label>
                                <input type="text" name="pin_code" class="form-control" placeholder="e.g. 400001" value="{{ old('pin_code') }}" required>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Contact Details -->
                    <div class="border-bottom pb-3 mb-4">
                        <h6 class="fw-bold text-primary text-uppercase letter-spacing-1 mb-3">
                            <i class="bx bx-user me-1"></i> 2. Contact Person Details
                        </h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Contact Person Name <span class="text-danger">*</span></label>
                                <input type="text" name="contact_person" class="form-control" placeholder="e.g. Dr. Rajesh Sharma" value="{{ old('contact_person') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Designation <span class="text-danger">*</span></label>
                                <input type="text" name="designation" class="form-control" placeholder="e.g. Quality Assurance Manager" value="{{ old('designation') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" placeholder="e.g. rajesh@apexlabs.com" value="{{ old('email') }}" required>
                                <small class="text-muted micro-text">Credentials and dispatch alerts will be sent to this email.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Mobile / Phone Number <span class="text-danger">*</span></label>
                                <input type="text" name="mobile_number" class="form-control" placeholder="e.g. +91 9876543210" value="{{ old('mobile_number') }}" required>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Account & Credentials -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary text-uppercase letter-spacing-1 mb-3">
                            <i class="bx bx-key me-1"></i> 3. Account Credentials
                        </h6>

                        <div class="card bg-light p-3 border mb-3">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <label class="form-label small fw-semibold mb-0">System User ID (Auto-Generated)</label>
                                    <small class="text-muted d-block">Your unique participant User ID generated automatically by the system.</small>
                                </div>
                                <div>
                                    <span class="badge bg-dark fs-6 font-monospace py-2 px-3" id="usernameBadge">
                                        <i class="bx bx-id-card me-1 text-warning"></i> LAB_XXXX_1234
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Account Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" placeholder="Minimum 8 characters" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="Re-enter password" required>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4 pt-3 border-top">
                        <a href="{{ route('user.login') }}" class="btn btn-outline-secondary px-4">Already registered? Log In</a>
                        <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm">
                            <i class="bx bx-check-circle me-1"></i> Register Laboratory Account
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function updateUsernamePreview() {
        var input = document.getElementById('labNameInput').value;
        var badge = document.getElementById('usernameBadge');
        if (input.trim().length > 0) {
            var slug = input.trim().substring(0, 8).replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
            badge.innerHTML = '<i class="bx bx-id-card me-1 text-warning"></i> LAB_' + (slug || 'XXXX') + '_XXXX';
        } else {
            badge.innerHTML = '<i class="bx bx-id-card me-1 text-warning"></i> LAB_XXXX_1234';
        }
    }
</script>
@endpush
