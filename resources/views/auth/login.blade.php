@extends('layouts.app')

@section('title', 'Login - HC System')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    body, html {
        height: 100%;
        background-color: #f4f5f7;
    }
    .main-content {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        padding: 1rem;
    }
    .login-card {
        display: flex;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        border: none;
        min-height: 500px;
        background: white;
    }
    
    /* Bagian Kiri (Gambar/Art) - Merah Tema */
    .login-art {
        background-color: #AB2F2B; /* Warna Merah KPN */
        width: 40%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    .login-art-text {
        writing-mode: vertical-rl;
        transform: rotate(180deg);
        font-size: 3rem;
        font-weight: 700;
        letter-spacing: 0.5rem;
        color: rgba(255,255,255,0.1);
        white-space: nowrap;
        user-select: none;
    }
    
    /* Bagian Kanan (Form) */
    .login-form-section {
        width: 60%;
        padding: 3rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    /* Input Custom */
    .form-control-custom {
        border: none;
        border-bottom: 2px solid #e9ecef;
        border-radius: 0;
        padding-left: 0;
        padding-bottom: 10px;
        transition: border-color 0.3s;
    }
    .form-control-custom:focus {
        box-shadow: none;
        border-color: #AB2F2B;
    }
    .input-group-text-custom {
        background: transparent;
        border: none;
        border-bottom: 2px solid #e9ecef;
        border-radius: 0;
        color: #adb5bd;
        padding-bottom: 10px;
    }
    
    /* Tombol Login */
    .btn-login {
        background-color: #AB2F2B;
        border-color: #AB2F2B;
        padding: 0.8rem;
        font-weight: 600;
        letter-spacing: 1px;
        color: white;
    }
    .btn-login:hover {
        background-color: #8f2623;
        border-color: #8f2623;
        color: white;
    }

    /* Password Toggle */
    .btn-toggle-password {
        border: none;
        border-bottom: 2px solid #e9ecef;
        background: transparent;
        color: #6c757d;
        padding-bottom: 10px;
    }
    .btn-toggle-password:focus {
        box-shadow: none;
        border-color: #AB2F2B;
    }

    @media (max-width: 768px) {
        .login-card { flex-direction: column; }
        .login-art { width: 100%; height: 100px; }
        .login-art-text { writing-mode: horizontal-tb; transform: none; font-size: 2rem; }
        .login-form-section { width: 100%; padding: 2rem; }
    }
</style>
@endpush

@section('content')
<div class="main-content">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-9 col-lg-10">
                <div class="login-card">
                    {{-- Left side --}}
                    <div class="login-art">
                        <h2 class="login-art-text">HC SYSTEM</h2>
                    </div>

                    {{-- Right side --}}
                    <div class="login-form-section">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $errors->first() }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="text-center mb-5">
                            {{-- Ganti src dengan path logo yang benar --}}
                            <img src="{{ asset('images/kpn-logo.png') }}" alt="KPN Corp Logo" style="height: 60px; width: auto;">
                            <h5 class="mt-3 text-muted fw-light">Please login to continue</h5>
                        </div>

                        <form method="POST" action="{{ route('login.submit') }}">
                            @csrf
                            
                            {{-- Employee ID --}}
                            <div class="mb-4">
                                <label for="employee_id" class="form-label text-muted small fw-bold">EMPLOYEE ID</label>
                                <div class="input-group">
                                    <span class="input-group-text input-group-text-custom">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input id="employee_id" type="text" class="form-control form-control-custom" name="employee_id" value="{{ old('employee_id') }}" required autofocus placeholder="Enter your ID">
                                </div>
                            </div>

                            {{-- Password --}}
                            <div class="mb-5">
                                <label for="password" class="form-label text-muted small fw-bold">PASSWORD</label>
                                <div class="input-group">
                                    <span class="input-group-text input-group-text-custom">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input id="password" type="password" class="form-control form-control-custom" name="password" required placeholder="Enter your password">
                                    <button class="btn btn-toggle-password" type="button" id="togglePassword">
                                        <i class="bi bi-eye-slash"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-login shadow-sm">LOG IN</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="text-center mt-3 text-muted small">
                    &copy; {{ date('Y') }} KPN Corp. All Rights Reserved.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');
    const icon = togglePassword.querySelector('i');

    togglePassword.addEventListener('click', function (e) {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        
        if (type === 'password') {
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });
});
</script>
@endpush