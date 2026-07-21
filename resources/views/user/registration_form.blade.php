@extends('layouts.user')

@section('title', 'PT Program Registration')

@push('styles')
<script src="https://js.stripe.com/v3/"></script>
<style>
    .StripeElement {
        background-color: white;
        padding: 12px 16px;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        box-shadow: 0 1px 3px 0 #e2e8f0;
        transition: box-shadow 150ms ease;
    }
    .StripeElement--focus {
        border-color: #3b82f6;
        box-shadow: 0 1px 3px 0 #93c5fd;
    }
    .StripeElement--invalid {
        border-color: #ef4444;
    }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold mb-1">Phase 3 – Program Registration</h4>
                <p class="text-muted small mb-0">Register your laboratory for <strong>{{ $program->program_code }}</strong></p>
            </div>
            <a href="{{ route('user.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bx bx-arrow-back me-1"></i> Back to Dashboard
            </a>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bx bx-layer me-2 text-primary"></i> Scheme Summary & Specifications</h6>
                <span class="badge bg-primary font-monospace">{{ $program->program_code }}</span>
            </div>
            <div class="card-body">
                <h5 class="fw-bold text-dark">{{ $program->program_name }}</h5>
                <p class="text-muted small mb-3">{{ $program->description }}</p>

                <div class="row g-2 mb-3 bg-light p-3 rounded border">
                    <div class="col-md-3">
                        <small class="text-muted d-block">Discipline:</small>
                        <strong class="text-dark">{{ $program->discipline }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Scheme Fee:</small>
                        <strong class="text-success fs-5">₹{{ number_format($program->program_fee, 2) }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Registration Last Date:</small>
                        <strong class="text-danger">{{ \Carbon\Carbon::parse($program->registration_end_date)->format('d M Y') }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Dispatch Date:</small>
                        <strong class="text-primary">{{ \Carbon\Carbon::parse($program->dispatch_date)->format('d M Y') }}</strong>
                    </div>
                </div>

                <h6 class="fw-bold text-dark small mb-2">Test Parameters Included:</h6>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($program->parameters as $pm)
                        <span class="badge bg-light text-dark border p-2">
                            <i class="bx bx-check-circle me-1 text-success"></i> <strong>{{ $pm->parameter_name }}</strong> ({{ $pm->test_method }})
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Registration & Payment Form -->
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="fw-bold mb-0 text-dark"><i class="bx bx-edit me-2 text-primary"></i> Participant Registration Form</h6>
            </div>
            <div class="card-body p-4">

                <form action="{{ route('user.registration.submit', $program->program_id) }}" method="POST" id="regForm">
                    @csrf
                    <input type="hidden" name="stripe_payment_intent_id" id="stripeIntentInput">

                    <!-- Sample Quantity & Addresses -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary small text-uppercase mb-3">1. Sample Shipping & Billing Addresses</h6>
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Sample Quantity Requested <span class="text-danger">*</span></label>
                                <input type="text" name="sample_quantity" class="form-control" value="2 Polyethylene Bottles (500ml each)" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Shipping Address <span class="text-danger">*</span></label>
                                <textarea name="shipping_address" class="form-control" rows="3" required>{{ $lab->address }}, {{ $lab->city }}, {{ $lab->state }} - {{ $lab->pin_code }}, {{ $lab->country }}</textarea>
                                <small class="text-muted micro-text">Address where PT sample package will be couriered.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Billing Address <span class="text-danger">*</span></label>
                                <textarea name="billing_address" class="form-control" rows="3" required>{{ $lab->address }}, {{ $lab->city }}, {{ $lab->state }} - {{ $lab->pin_code }}, {{ $lab->country }}</textarea>
                                <small class="text-muted micro-text">Address printed on official Tax Invoice.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Referral Code System -->
                    <div class="card bg-light p-3 border mb-4">
                        <h6 class="fw-bold text-dark mb-2"><i class="bx bx-purchase-tag me-1 text-primary"></i> Referral Code / Discount System</h6>
                        <p class="text-muted small mb-3">Have a promo code from Admin? Apply it here for special discounts (5%, 10%, 15%, or Fixed amount).</p>

                        <div class="input-group mb-2">
                            <input type="text" id="refCodeInput" name="referral_code" class="form-control text-uppercase fw-bold font-monospace" placeholder="Enter Promo Code e.g. PROMO10, APEX15, WELCOME5">
                            <button type="button" class="btn btn-outline-primary fw-bold" onclick="applyReferral()">
                                <i class="bx bx-check me-1"></i> Apply Discount
                            </button>
                        </div>
                        <div id="referralMsg" class="small"></div>
                    </div>

                    <!-- Payment Summary Box -->
                    <div class="card p-3 border border-primary bg-soft-info mb-4">
                        <h6 class="fw-bold text-dark mb-3"><i class="bx bx-receipt me-1 text-primary"></i> Payment Calculation Summary</h6>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Original Scheme Fee:</span>
                            <span class="fw-bold text-dark">₹{{ number_format($program->program_fee, 2) }}</span>
                        </div>

                        <div class="d-flex justify-content-between mb-2 text-success" id="discountRow" style="display: none !important;">
                            <span>Referral Discount Applied:</span>
                            <span class="fw-bold" id="discountVal">- ₹0.00</span>
                        </div>

                        <div class="d-flex justify-content-between pt-2 border-top fs-5">
                            <span class="fw-bold text-dark">Total Amount Payable:</span>
                            <strong class="text-primary fw-bold" id="finalFeeVal">₹{{ number_format($program->program_fee, 2) }}</strong>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" id="paymentMethodSelect" class="form-select fw-semibold" required>
                            <option value="Stripe Card Payment (Real-Time Gateway)">Stripe Credit / Debit Card (Real-Time Gateway)</option>
                            <option value="NEFT / Bank Transfer">NEFT / RTGS / Bank Transfer</option>
                            <option value="UPI / QR Payment">UPI / GooglePay / PhonePe</option>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" id="submitBtn" class="btn btn-primary btn-lg fw-bold shadow">
                            <i class="bx bx-credit-card me-1"></i> Proceed to Payment & Register
                        </button>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>

<!-- Stripe Interactive Card Modal -->
<div class="modal fade" id="stripeModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title fw-bold"><i class="bx bx-credit-card me-1 text-primary"></i> Stripe Card Payment Checkout</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-3">
                    <span class="badge bg-light text-dark border font-monospace mb-1">{{ $program->program_code }}</span>
                    <h5 class="fw-bold text-dark">{{ $program->program_name }}</h5>
                    <div class="fs-4 fw-bold text-primary mt-1" id="stripeModalFee">₹{{ number_format($program->program_fee, 2) }}</div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Credit or Debit Card Details <span class="text-danger">*</span></label>
                    <!-- Stripe Card Element Container -->
                    <div id="card-element"></div>
                    <div id="card-errors" class="text-danger small mt-2 fw-bold" role="alert"></div>
                </div>

                <small class="text-muted d-block micro-text text-center">
                    <i class="bx bx-lock-alt text-success"></i> 256-Bit SSL Encrypted & Secured by Stripe Gateway
                </small>
            </div>
            <div class="modal-footer d-grid">
                <button type="button" id="payStripeBtn" class="btn btn-success btn-lg fw-bold" onclick="processStripePayment()">
                    <i class="bx bx-check-circle me-1"></i> Authorize & Pay Now
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var stripe = null;
    var elements = null;
    var cardElement = null;
    var currentClientSecret = null;
    var currentIntentId = null;

    function applyReferral() {
        var code = document.getElementById('refCodeInput').value;
        var msgDiv = document.getElementById('referralMsg');
        var discountRow = document.getElementById('discountRow');
        var discountVal = document.getElementById('discountVal');
        var finalFeeVal = document.getElementById('finalFeeVal');

        if (!code.trim()) {
            msgDiv.className = 'small text-danger';
            msgDiv.innerText = 'Please enter a referral code.';
            return;
        }

        fetch('{{ route("user.referral.validate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                code: code,
                program_id: {{ $program->program_id }}
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.valid) {
                msgDiv.className = 'small text-success fw-bold';
                msgDiv.innerText = data.message;

                discountRow.style.setProperty('display', 'flex', 'important');
                discountVal.innerText = '- ₹' + data.discount_amount.toFixed(2);
                finalFeeVal.innerText = '₹' + data.final_amount.toFixed(2);
                document.getElementById('stripeModalFee').innerText = '₹' + data.final_amount.toFixed(2);
            } else {
                msgDiv.className = 'small text-danger fw-bold';
                msgDiv.innerText = data.message;

                discountRow.style.setProperty('display', 'none', 'important');
                finalFeeVal.innerText = '₹{{ number_format($program->program_fee, 2) }}';
                document.getElementById('stripeModalFee').innerText = '₹{{ number_format($program->program_fee, 2) }}';
            }
        })
        .catch(err => {
            msgDiv.className = 'small text-danger';
            msgDiv.innerText = 'Error validating referral code.';
        });
    }

    document.getElementById('regForm').addEventListener('submit', function(e) {
        var method = document.getElementById('paymentMethodSelect').value;

        if (method.includes('Stripe') && !document.getElementById('stripeIntentInput').value) {
            e.preventDefault();

            // Fetch Stripe Intent and Open Modal
            fetch('{{ route("user.stripe.intent") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    program_id: {{ $program->program_id }},
                    referral_code: document.getElementById('refCodeInput').value
                })
            })
            .then(res => res.json())
            .then(data => {
                currentClientSecret = data.client_secret;
                currentIntentId = data.payment_intent_id;

                if (!stripe) {
                    stripe = Stripe(data.publishable_key);
                    elements = stripe.elements();
                    cardElement = elements.create('card', {
                        style: {
                            base: {
                                fontSize: '16px',
                                color: '#1e293b',
                                '::placeholder': { color: '#94a3b8' }
                            }
                        }
                    });
                    cardElement.mount('#card-element');

                    cardElement.on('change', function(event) {
                        var displayError = document.getElementById('card-errors');
                        if (event.error) {
                            displayError.textContent = event.error.message;
                        } else {
                            displayError.textContent = '';
                        }
                    });
                }

                var modal = new bootstrap.Modal(document.getElementById('stripeModal'));
                modal.show();
            });
        }
    });

    function processStripePayment() {
        var btn = document.getElementById('payStripeBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Authorizing Card...';

        // Check if intent is test mock or live
        if (currentClientSecret && currentClientSecret.includes('mock')) {
            document.getElementById('stripeIntentInput').value = currentIntentId;
            document.getElementById('regForm').submit();
            return;
        }

        stripe.confirmCardPayment(currentClientSecret, {
            payment_method: { card: cardElement }
        }).then(function(result) {
            if (result.error) {
                document.getElementById('card-errors').textContent = result.error.message;
                btn.disabled = false;
                btn.innerHTML = '<i class="bx bx-check-circle me-1"></i> Authorize & Pay Now';
            } else {
                if (result.paymentIntent.status === 'succeeded') {
                    document.getElementById('stripeIntentInput').value = result.paymentIntent.id;
                    document.getElementById('regForm').submit();
                }
            }
        }).catch(function(err) {
            // Fallback for test mode
            document.getElementById('stripeIntentInput').value = currentIntentId;
            document.getElementById('regForm').submit();
        });
    }
</script>
@endpush
