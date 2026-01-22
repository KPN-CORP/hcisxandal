<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'HC System')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    @stack('styles')

    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #fff;
            --main-bg: #f8f9fa;
            --bs-primary: #AB2F2B; /* Warna Merah KPN */
        }
        body {
            background-color: var(--main-bg);
            overflow-x: hidden;
        }
        
        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: var(--sidebar-bg);
            border-right: 1px solid #dee2e6;
            padding: 1.5rem 1rem;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease-in-out;
            z-index: 1030;
            overflow-y: auto;
        }

        /* Content Wrapper */
        .main-content-wrapper {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            transition: margin-left 0.3s ease-in-out, width 0.3s ease-in-out;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* --- LOGIC KUNCI: SAAT SIDEBAR DISEMBUNYIKAN (LOGIN PAGE / TOGGLE) --- */
        body.sidebar-hidden .sidebar {
            transform: translateX(calc(-1 * var(--sidebar-width)));
        }
        body.sidebar-hidden .main-content-wrapper {
            margin-left: 0;
            width: 100%;
        }

        /* Navbar Custom */
        .navbar-custom {
            position: sticky;
            top: 0;
            z-index: 1020;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,.05);
            padding: .75rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .menu-icon {
            font-size: 1.5rem;
            cursor: pointer;
            color: #495057;
        }

        /* Desktop Only Message */
        .desktop-only-message {
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            height: 100vh;
            padding: 2rem;
            background: white;
            position: fixed;
            top: 0; left: 0; width: 100%; z-index: 9999;
        }
        
        @media (max-width: 991.98px) {
            .page-wrapper { display: none !important; }
            .desktop-only-message { display: flex; }
        }
    </style>
</head>
{{-- Tambahkan class 'sidebar-hidden' jika user BELUM login --}}
<body class="{{ auth()->check() ? '' : 'sidebar-hidden' }}">

    <div id="app">
        <div class="page-wrapper">
            
            {{-- 1. Sidebar (Hanya dirender jika login) --}}
            @auth
                @include('layouts.sidebar')
            @endauth

            {{-- 2. Konten Utama --}}
            <div class="main-content-wrapper">
                
                {{-- Navbar Atas (Hanya dirender jika login) --}}
                @auth
                    <nav class="navbar-custom">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-list menu-icon me-3" id="menu-toggle-button"></i>
                            <h5 class="mb-0 text-dark fw-bold">@yield('title')</h5>
                        </div>
                        
                        {{-- Area Kanan Navbar (Profile) --}}
                        <div>
                            @stack('navbar-right')
                        </div>
                    </nav>
                @endauth
                
                {{-- Isi Halaman --}}
                <main class="p-4">
                    @yield('content')
                </main>
            </div>
        </div>

        {{-- Mobile Block Message --}}
        <div class="desktop-only-message">
            <i class="bi bi-laptop text-danger" style="font-size: 5rem;"></i>
            <h3 class="mt-3">Desktop Only</h3>
            <p>This application is designed for desktop screens.</p>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle Sidebar Logic
            const toggleButton = document.getElementById('menu-toggle-button');
            if (toggleButton) {
                toggleButton.addEventListener('click', () => {
                    document.body.classList.toggle('sidebar-hidden');
                });
            }
        });

        // Global SweetAlert Notification
        @if(session('success'))
            Swal.fire({
                toast: true, position: 'top-end', icon: 'success',
                title: "{{ session('success') }}",
                showConfirmButton: false, timer: 3000, timerProgressBar: true
            });
        @endif
        @if(session('error'))
            Swal.fire({
                toast: true, position: 'top-end', icon: 'error',
                title: "{{ session('error') }}",
                showConfirmButton: false, timer: 5000, timerProgressBar: true
            });
        @endif
    </script>

    @stack('scripts')
</body>
</html>