<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'HC System')</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @stack('styles')

    <style>
        :root {
            --sidebar-width: 260px;
            --main-bg: #f8f9fa;
            --kpn-red: #AB2F2B;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--main-bg);
            overflow-x: hidden;
        }

        .text-kpn { color: var(--kpn-red) !important; }
        .bg-kpn { background-color: var(--kpn-red) !important; }
        
        .btn-outline-kpn {
            color: var(--kpn-red);
            border-color: var(--kpn-red);
        }
        
        .btn-outline-kpn:hover {
            background-color: var(--kpn-red);
            color: #fff;
        }

        .bg-danger-subtle {
            background-color: rgba(171, 47, 43, 0.1) !important;
        }

        .transition-all {
            transition: all 0.2s ease-in-out;
        }

        .nav-link:hover:not(.active) {
            background-color: rgba(0,0,0,0.03);
            color: var(--kpn-red) !important;
        }

        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #fff;
            transition: transform 0.3s ease-in-out;
            z-index: 1045;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .main-content-wrapper {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            transition: margin-left 0.3s ease-in-out, width 0.3s ease-in-out;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        body.sidebar-hidden .sidebar {
            transform: translateX(-100%);
        }

        body.sidebar-hidden .main-content-wrapper {
            margin-left: 0;
            width: 100%;
        }

        .navbar-custom {
            position: sticky;
            top: 0;
            z-index: 1020;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,.04);
        }

        .menu-icon {
            font-size: 1.5rem;
            cursor: pointer;
            color: #495057;
            transition: color 0.2s;
        }

        .menu-icon:hover {
            color: var(--kpn-red);
        }

        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(2px);
            z-index: 1040;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .main-content-wrapper {
                margin-left: 0;
                width: 100%;
            }
            body.sidebar-open .sidebar {
                transform: translateX(0);
            }
            body.sidebar-open .sidebar-overlay {
                display: block;
                opacity: 1;
            }
            body.sidebar-hidden .sidebar {
                transform: translateX(-100%);
            }
        }
    </style>
</head>
<body class="{{ auth()->check() ? '' : 'sidebar-hidden' }}">

    <div id="app">
        <div id="sidebar-overlay" class="sidebar-overlay"></div>

        <div class="page-wrapper">
            @auth
                @include('layouts.sidebar')
            @endauth

            <div class="main-content-wrapper">
                @auth
                    <nav class="navbar-custom px-3 py-2 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-list menu-icon me-3" id="menu-toggle-button"></i>
                            {{-- <h5 class="mb-0 text-dark fw-bold d-none d-md-block">@yield('title')</h5> --}}
                        </div>
                        
                        <div>
                            @stack('navbar-right')
                        </div>
                    </nav>
                @endauth
                
                <main class="p-3 p-md-4 flex-grow-1">
                    @yield('content')
                </main>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButton = document.getElementById('menu-toggle-button');
            const overlay = document.getElementById('sidebar-overlay');
            const body = document.body;

            if (toggleButton) {
                toggleButton.addEventListener('click', () => {
                    if (window.innerWidth <= 991.98) {
                        body.classList.toggle('sidebar-open');
                    } else {
                        body.classList.toggle('sidebar-hidden');
                    }
                });
            }

            if (overlay) {
                overlay.addEventListener('click', () => {
                    body.classList.remove('sidebar-open');
                });
            }

            window.addEventListener('resize', () => {
                if (window.innerWidth > 991.98) {
                    body.classList.remove('sidebar-open');
                }
            });
        });

        @if(session('success'))
            Swal.fire({
                toast: true, 
                position: 'top-end', 
                icon: 'success',
                title: "{{ session('success') }}",
                showConfirmButton: false, 
                timer: 3000, 
                timerProgressBar: true
            });
        @endif

        @if(session('error'))
            Swal.fire({
                toast: true, 
                position: 'top-end', 
                icon: 'error',
                title: "{{ session('error') }}",
                showConfirmButton: false, 
                timer: 5000, 
                timerProgressBar: true
            });
        @endif
    </script>

    @stack('scripts')
</body>
</html>