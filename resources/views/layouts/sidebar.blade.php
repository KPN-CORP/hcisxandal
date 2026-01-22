{{-- Push content ke stack navbar-right di layout utama --}}
@push('navbar-right')
    @php $employeeData = Auth::user()->employee; @endphp
    <div class="d-flex align-items-center">
        <div class="flex-shrink-0">
            @if($employeeData && $employeeData->user && $employeeData->user->img_path)
                <img src="{{ Storage::url('app/'.$employeeData->user->img_path) }}" alt="User" class="rounded-circle border" style="width: 40px; height: 40px; object-fit: cover;">
            @else
                <div class="bg-danger rounded-circle text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 1.2rem;">
                    {{ substr($employeeData->fullname ?? Auth::user()->name, 0, 1) }}
                </div>
            @endif
        </div>
        <div class="flex-grow-1 ms-3 text-end">
            <span class="fw-bold d-block text-dark">{{ $employeeData->fullname ?? Auth::user()->name }}</span>
            @if($employeeData && $employeeData->employee_id)
                <span class="text-muted d-block small">{{ $employeeData->employee_id }}</span>
            @endif
        </div>
    </div>
@endpush

<aside class="sidebar">
    <div class="logo d-flex flex-column align-items-center mb-4 pb-3 border-bottom">
        <img src="{{ asset('images/kpn-logo.png') }}" alt="KPN Corp Logo" style="height: 50px;">
        <h2 class="h6 mb-0 mt-3 text-primary fw-bold">HC SYSTEM</h2>
    </div>

    <p class="text-muted small text-uppercase fw-bold mb-2 ps-3">Main Menu</p>
    <nav class="nav flex-column main-menu gap-1">
        
        <a href="{{ route('employees.list') }}" class="nav-link {{ request()->routeIs('employees.*') ? 'active bg-danger-subtle text-danger fw-bold' : 'text-dark' }} rounded">
            <i class="bi bi-people me-2"></i> Employee Comparison
        </a>

        <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active bg-danger-subtle text-danger fw-bold' : 'text-dark' }} rounded">
            <i class="bi bi-gear me-2"></i> Role Setting
        </a>
    </nav>

    <div class="mt-auto pt-3 border-top">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-danger w-100 fw-bold">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </button>
        </form>
    </div>
</aside>