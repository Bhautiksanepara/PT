@extends('layouts.user')

@section('title', 'Complete Laboratory Profile')

@section('content')
<div class="row justify-content-center py-3"><div class="col-lg-8"><div class="card shadow border-0">
    <div class="card-header bg-warning-subtle py-4"><h4 class="fw-bold mb-1"><i class="bx bx-building me-2"></i>Complete Required Laboratory Information</h4><p class="mb-0 small text-muted">Portal functions will unlock after this master profile is saved.</p></div>
    <div class="card-body p-4 p-md-5">
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        <form action="{{ route('user.profile.complete.update') }}" method="POST" class="row g-3">@csrf @method('PUT')
            <div class="col-md-6"><label class="form-label small fw-semibold">Center Name <span class="text-danger">*</span></label><input name="center_name" class="form-control" value="{{ old('center_name', $lab->center_name) }}" required></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Laboratory Name <span class="text-danger">*</span></label><input name="laboratory_name" class="form-control" value="{{ old('laboratory_name', $lab->laboratory_name) }}" required></div>
            <div class="col-12"><label class="form-label small fw-semibold">Full Address <span class="text-danger">*</span></label><textarea name="address" class="form-control" rows="3" required>{{ old('address', $lab->address) }}</textarea></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">GST Number <span class="text-danger">*</span></label><input name="gst_number" class="form-control text-uppercase" value="{{ old('gst_number', $lab->gst_number) }}" required></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Mobile Number <span class="text-danger">*</span></label><input name="mobile_number" class="form-control" value="{{ old('mobile_number', $lab->mobile_number) }}" required></div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Laboratory Type <span class="text-danger">*</span></label>
                <select name="laboratory_type" class="form-select" required>
                    <option value="" disabled {{ !old('laboratory_type', $lab->laboratory_type) ? 'selected' : '' }}>Select Type</option>
                    @foreach([
                        'Inhouse Testing Laboratory', 
                        'Hallmarking', 
                        'Third Party Testing Laboratory', 
                        'Research & Development Laboratory', 
                        'Government Laboratory', 
                        'Calibration Laboratory'
                    ] as $type)
                        <option value="{{ $type }}" {{ old('laboratory_type', $lab->laboratory_type) === $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6"><label class="form-label small fw-semibold">BIS / NABL Certificate Number <span class="text-danger">*</span></label><input name="nabl_certificate_number" class="form-control" value="{{ old('nabl_certificate_number', $lab->nabl_certificate_number) }}" required></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Designation <span class="text-danger">*</span></label><input name="designation" class="form-control" value="{{ old('designation', $lab->designation) }}" required placeholder="e.g. Quality Manager / Director"></div>
            <div class="col-12 d-flex justify-content-between border-top pt-4 mt-3"><a href="{{ route('user.password.edit') }}" class="btn btn-outline-secondary">Change Password</a><button class="btn btn-primary px-4">Save & Unlock Portal</button></div>
        </form>
    </div>
</div></div></div>
@endsection
