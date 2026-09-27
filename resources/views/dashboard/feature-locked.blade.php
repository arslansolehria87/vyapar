@extends('layouts.app')

@section('title', 'Feature Upgrade Required — CodiceSync')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center" style="background: #ffffff; border: 1px solid #D4C2DF !important;">
                
                <div class="mx-auto my-3 rounded-circle d-flex align-items-center justify-center" style="width: 70px; height: 70px; background: #F3F3FF; color: #5813BC; font-size: 28px;">
                    <i class="fa-solid fa-lock"></i>
                </div>

                <span class="badge rounded-pill px-3 py-2 mx-auto mb-2 text-uppercase" style="background: #F3F3FF; color: #5813BC; font-size: 11px; letter-spacing: 1px; font-weight: 700;">
                    Feature Not Included
                </span>

                <h3 class="fw-bold text-dark mt-2 mb-1">{{ $featureInfo['name'] ?? 'Premium Feature' }}</h3>
                <p class="text-muted small px-3">
                    {{ $featureInfo['description'] ?? 'This specialized module is not included in your current subscription package.' }}
                </p>

                <div class="p-3 my-3 rounded-3 text-start" style="background: #F3F3FF; border: 1px dashed #D4C2DF;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-circle-info text-primary" style="color: #5813BC !important;"></i>
                        <span class="small fw-semibold text-dark">How to enable this module?</span>
                    </div>
                    <p class="small text-muted mb-0 mt-1">
                        Contact CodiceSync administration to add this module to your business workspace. Your administrator will instantly activate it for your account.
                    </p>
                </div>

                <div class="d-flex flex-column gap-2 mt-2">
                    <a href="https://wa.me/923214530103?text={{ urlencode('Hello, I want to upgrade my CodiceSync plan to include: ' . ($featureInfo['name'] ?? 'Feature') . ' for business: ' . ($company->name ?? '')) }}"
                       target="_blank"
                       class="btn text-white fw-bold py-2.5 rounded-3"
                       style="background: linear-gradient(135deg, #5813BC 0%, #AC22CB 100%);">
                        <i class="fa-brands fa-whatsapp me-1"></i> Contact Administrator to Upgrade (0321-4530103)
                    </a>

                    <div class="text-muted small py-1">
                        Support: <strong>0371-0045282</strong> / <strong>0321-4530103</strong> | 
                        <a href="mailto:codicesync@gmail.com" class="text-decoration-none" style="color: #5813BC;">codicesync@gmail.com</a>
                    </div>

                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary py-2 rounded-3 small">
                        Back to Dashboard
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
